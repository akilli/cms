<?php
declare(strict_types = 1);

namespace qnd;

use RuntimeException;
use ZipArchive;

/**
 * Export project
 *
 * @return string
 *
 * @throws RuntimeException
 */
function export(): string
{
    $project = project();
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
    foreach (all('media') as $data) {
        $zip->addFile($data['file'], 'media/' . $data['id']);
    }

    // Pages
    $charset = data('app', 'charset');
    $from = [
        '#="/"#Ui',
        '#="' . URL['media'] . '([^/]+)"#Ui',
        '#="' . URL['theme'] . '([^/]+)"#Ui',
        '#="/(([^/]+)' . URL['page'] . ')"#Ui'
    ];
    $to = ['="index.html"', '="media/$1"', '="theme/$1"', '="$1"'];
    $toc = '';

    foreach (all('page', [], ['order' => ['pos' => 'asc']]) as $data) {
        $base = basename($data['url']);
        $name = str_replace(IMPORT['del'], '', $data['name']);
        $attr = array_replace($data['_entity']['attr']['pos'], ['context' => 'view', 'actions' => ['view']]);
        $toc .= viewer($attr, $data) . IMPORT['del'] . $name . IMPORT['del'] . $base . "\n";
        $main = ['id' => 'content', 'template' => 'entity/view.phtml', 'vars' => ['data' => $data, 'context' => 'view']];
        $nav = ['id' => 'nav', 'vars' => ['depth' => 2, 'sub' => true, 'current' => $data['id']]];
        $§ = [
            'id' => 'root',
            'template' => 'layout/export.phtml',
            'vars' => [
                'charset' => $charset,
                'title' => $data['name'],
                'main' => IMPORT['start'] . section_template($main) . IMPORT['end'],
                'nav' => section_nav($nav),
            ],
        ];
        $zip->addFromString($base, preg_replace($from, $to, section_template($§)));
    }

    $zip->addFromString(IMPORT['toc'], $toc);
    $project = ['exported' => date(DATE['b'])] + $project;

    if (!save('project', $project)) {
        throw new RuntimeException(_('Export error'));
    }

    return $file;
}
