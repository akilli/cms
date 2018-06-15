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

    // Theme
    $theme = path('theme');

    foreach (array_diff(scandir($theme), ['.', '..']) as $themeId) {
        if (($themeFile = $theme . '/' . $themeId) && is_file($themeFile)) {
            $zip->addFile($themeFile, 'theme/' . $themeId);
        }
    }

    // Media
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
    $attrs = entity_attr(data('entity', 'page'), 'view');

    foreach (all('page', [], ['order' => ['pos' => 'asc']]) as $data) {
        $base = basename($data['url']);
        $name = str_replace(IMPORT['del'], '', $data['name']);
        $toc .= viewer($data['_entity']['attr']['pos'], $data) . IMPORT['del'] . $name . IMPORT['del'] . $base . "\n";
        $main = ['id' => 'content', 'template' => 'entity/view.phtml', 'vars' => ['data' => $data, 'attr' => $attrs]];
        $nav = ['id' => 'nav', 'vars' => ['mode' => 'top', 'current' => $data['id']]];
        $subnav = ['id' => 'subnav', 'vars' => ['mode' => 'sub', 'current' => $data['id']]];
        $§ = [
            'id' => 'root',
            'template' => 'layout/export.phtml',
            'vars' => [
                'charset' => $charset,
                'title' => $data['name'],
                'name' => $project['name'],
                'main' => IMPORT['start'] . section_template($main) . IMPORT['end'],
                'nav' => section_nav($nav),
                'subnav' => section_nav($subnav),
            ],
        ];
        $zip->addFromString($base, preg_replace($from, $to, section_template($§)));
    }

    // Homepage
    $attrs = entity_attr($project['_entity'], 'home');
    $main = ['id' => 'content', 'template' => 'entity/view.phtml', 'vars' => ['data' => $project, 'attr' => $attrs]];
    $nav = ['id' => 'nav', 'vars' => ['mode' => 'top']];
    $subnav = ['id' => 'subnav', 'vars' => ['mode' => 'sub']];
    $§ = [
        'id' => 'root',
        'template' => 'layout/export.phtml',
        'vars' => [
            'charset' => $charset,
            'title' => $project['name'],
            'name' => $project['name'],
            'main' => IMPORT['start'] . section_template($main) . IMPORT['end'],
            'nav' => section_nav($nav),
            'subnav' => section_nav($subnav),
        ],
    ];
    $zip->addFromString('index.html', preg_replace($from, $to, section_template($§)));

    // TOC
    $zip->addFromString(IMPORT['toc'], $toc);

    return $file;
}
