<?php
declare(strict_types = 1);

namespace qnd;

use RuntimeException;
use ZipArchive;

/**
 * Export project
 *
 * @param int $id
 *
 * @return string
 *
 * @throws RuntimeException
 */
function export(int $id): string
{
    if (!$project = one('project', [['id', $id]])) {
        throw new RuntimeException(_('Invalid project ID %d', (string) $id));
    }

    $file = path('tmp', $project['uid'] . '.zip');
    $zip = new ZipArchive();

    if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException(_('Export error'));
    }

    // Theme files
    $theme = path('theme');

    foreach (array_diff(scandir($theme), ['.', '..']) as $themeId) {
        if (($themeFile = $theme . '/' . $themeId) && is_file($themeFile)) {
            $zip->addFile($themeFile, 'theme/' . $themeId);
        }
    }

    // Media files
    foreach (all('media', [['project_id', $project['id']]]) as $item) {
        $zip->addFile($item['file'], 'media/' . $item['id']);
    }

    // Pages
    $toc = '';

    foreach (all('tree', [['project_id', $id]], ['order' => ['pos' => 'asc']]) as $item) {
        $base = basename($item['url']);
        $name = str_replace(IMPORT['del'], '', $item['name']);
        $toc .= $item['structure'] . IMPORT['del'] . $name . IMPORT['del'] . $base . "\n";
        $zip->addFromString($base, IMPORT['start'] . $item['content'] . IMPORT['end']);
    }

    $zip->addFromString(IMPORT['toc'], $toc);
    $project['exported'] = date(DATE['b']);

    if (!save('project', $project)) {
        throw new RuntimeException(_('Export error'));
    }

    return $file;
}
