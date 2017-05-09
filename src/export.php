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

    $toc = '';

    foreach (all('tree', [['project_id', $id]], ['order' => ['pos' => 'asc']]) as $item) {
        $toc .= $item['structure'] . IMPORT['del'] . str_replace(IMPORT['del'], '', $item['name']) . IMPORT['del'] . basename($item['url']) . "\n";
    }

    $zip->addFromString(IMPORT['toc'], $toc);

    return $file;
}
