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
    $file = path('tmp', project('uid') . '.zip');
    $zip = new ZipArchive();

    if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException(_('Export error'));
    }

    export_theme($zip);
    export_media($zip);
    export_page($zip);
    export_homepage($zip);

    return $file;
}

/**
 * Export theme files
 *
 * @param ZipArchive $zip
 *
 * @return void
 */
function export_theme(ZipArchive $zip): void
{
    $theme = path('theme');

    foreach (array_diff(scandir($theme), ['.', '..']) as $themeId) {
        if (($themeFile = $theme . '/' . $themeId) && is_file($themeFile)) {
            $zip->addFile($themeFile, 'theme/' . $themeId);
        }
    }
}

/**
 * Export media files
 *
 * @param ZipArchive $zip
 *
 * @return void
 */
function export_media(ZipArchive $zip): void
{
    foreach (all('media') as $data) {
        $zip->addFile($data['file'], 'media/' . $data['id']);
    }
}

/**
 * Export pages and generate TOC file
 *
 * @param ZipArchive $zip
 *
 * @return void
 */
function export_page(ZipArchive $zip): void
{
    $toc = '';
    $attrs = entity_attr(data('entity', 'page'), 'view');

    foreach (all('page', [], ['order' => ['pos' => 'asc']]) as $page) {
        $base = basename($page['url']);
        $name = str_replace(IMPORT['del'], '', $page['name']);
        $toc .= viewer($page['_entity']['attr']['pos'], $page) . IMPORT['del'] . $name . IMPORT['del'] . $base . "\n";
        $zip->addFromString($base, export_content($page, $attrs));
    }

    $zip->addFromString(IMPORT['toc'], $toc);
}

/**
 * Export homepage
 *
 * @param ZipArchive $zip
 *
 * @return void
 */
function export_homepage(ZipArchive $zip): void
{
    $project = project();
    $zip->addFromString('index.html', export_content($project, entity_attr($project['_entity'], 'home')));
}

/**
 * Export page content
 *
 * @param array $data
 * @param array $attrs
 *
 * @return string
 */
function export_content(array $data, array $attrs): string
{
    $main = ['id' => 'content', 'template' => 'entity/view.phtml', 'vars' => ['data' => $data, 'attr' => $attrs]];
    $nav = ['id' => 'nav', 'vars' => ['mode' => 'top']];
    $subnav = ['id' => 'subnav', 'vars' => ['mode' => 'sub']];

    if ($data['_entity']['id'] === 'page') {
        $nav['vars']['current'] = $data['id'];
        $subnav['vars']['current'] = $data['id'];
    }

    $§ = [
        'id' => 'root',
        'template' => 'layout/export.phtml',
        'vars' => [
            'charset' => data('app', 'charset'),
            'title' => $data['name'],
            'name' => project('name'),
            'main' => IMPORT['start'] . section_template($main) . IMPORT['end'],
            'nav' => section_nav($nav),
            'subnav' => section_nav($subnav),
        ],
    ];
    $from = [
        '#="/"#Ui',
        '#="' . URL['media'] . '([^/]+)"#Ui',
        '#="' . URL['theme'] . '([^/]+)"#Ui',
        '#="/(([^/]+)' . URL['page'] . ')"#Ui'
    ];
    $to = ['="index.html"', '="media/$1"', '="theme/$1"', '="$1"'];

    return preg_replace($from, $to, section_template($§));
}
