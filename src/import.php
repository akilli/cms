<?php
declare(strict_types = 1);

namespace qnd;

use DOMDocument;
use Exception;
use RuntimeException;
use XSLTProcessor;
use ZipArchive;

/**
 * Import project
 *
 * @param string $name
 * @param string $file
 *
 * @return bool
 */
function import_project(string $name, string $file): bool
{
    $path = path('tmp', uniqid('import', true));
    $toc = $path . '/' . IMPORT['toc'];

    try {
        import_zip($file, $path);
    } catch (Exception $e) {
        message($e->getMessage());
        return false;
    }

    if (!is_file($toc)) {
        message(_('File %s not found', basename($toc)));
        return false;
    }

    $trans = db_trans(
        function () use ($name, $path, $toc): void {
            // Project
            $project = ['uid' => $name, 'name' => $name, 'active' => true];

            if (!save('project', $project)) {
                throw new RuntimeException(_('Import error'));
            }

            // Media
            $asset = path('asset', (string) $project['id']);
            file_copy($path . '/media', $asset);

            // Homepage
            foreach (['index.html', 'index.odt'] as $hp) {
                if (!file_exists($path . '/' . $hp)) {
                    continue;
                }

                $project['content'] = import_content($path . '/' . $hp, $project['id']);

                if (!save('project', $project)) {
                    throw new RuntimeException(_('Import error'));
                }
            }

            // Pages
            $log = [null];
            $prev = 0;

            foreach (file($toc, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES) as $item) {
                $item = array_slice(str_getcsv($item, IMPORT['del']), 0, 3);

                if (count($item) !== 3) {
                    throw new RuntimeException(_('Import error'));
                }

                if (($cur = substr_count(trim($item[0], '.'), '.')) > $prev + 1) {
                    $cur = $prev + 1;
                }

                $page = [
                    'name' => rtrim($item[1], '>'),
                    'active' => true,
                    'parent_id' => $log[$cur],
                    'content' => $item[2] ? import_content($path . '/' . $item[2], $project['id']) : '',
                    'project_id' => $project['id']
                ];

                if (!save('page', $page)) {
                    file_delete($asset);
                    throw new RuntimeException(_('Import error'));
                }

                $log[$cur + 1] = $page['id'];
                $prev = $cur;
            }
        }
    );
    file_delete($path);

    return $trans;
}

/**
 * Import content
 *
 * @param string $file
 * @param int $pId
 *
 * @return string
 */
function import_content(string $file, int $pId ): string
{
    if (!file_exists($file)) {
        return '';
    }

    $html = '';
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    if ($ext === 'html') {
        $html = import_html($file);
    } elseif ($ext === 'odt') {
        $html = import_odt($file, $pId);
    }

    if (preg_match('#<body(.*)>(.*)</body>#isU', $html, $match)) {
        $html = $match[2];
    }

    $from = [
        '#="/?index\.html"#Ui',
        '#="/?(Pictures|media)/([0-9/]*)([^"/]+)"#Ui',
        '#="/?(([^"]+)\.html)"#Ui',
        '#(<[^>]+) align=".*?"#i',
        '#(<[^>]+) border=".*?"#i',
        '#(<[^>]+) cellpadding=".*?"#i',
        '#(<[^>]+) cellspacing=".*?"#i',
        '#(<[^>]+) style=".*?"#i',
    ];
    $to = [
        '="/"',
        '="' . URL['media'] . '$3"',
        '="/$1"',
        '$1',
        '$1',
        '$1',
        '$1',
        '$1',
    ];

    return preg_replace($from, $to, $html);
}

/**
 * Import HTML document
 *
 * @param string $file
 *
 * @return string
 */
function import_html(string $file): string
{
    if (!file_exists($file) || !($html = file_get_contents($file))) {
        return '';
    }

    return preg_match('#' . IMPORT['start'] . '(.*)' . IMPORT['end'] . '#isU', $html, $match) ? $match[1] : $html;
}

/**
 * Import ODT
 *
 * @param string $file
 * @param int $pId
 *
 * @return string
 *
 * @throws RuntimeException
 */
function import_odt(string $file, int $pId): string
{
    $html = '';
    $path = path('tmp', uniqid(basename($file), true));
    $mediaPath = $path . '/Pictures';
    $contentXML = $path . '/content.xml';
    $xslFile = path('data', 'odt.xsl');

    if (!import_zip($file, $path) || !is_file($contentXML)) {
        return $html;
    }

    // Load stylesheet
    $xsl = new DOMDocument();

    if (!$xsl->load($xslFile)) {
        throw new RuntimeException(_('Could not load %s', $xslFile));
    }

    // Load XSLT processor
    $xslt = new XSLTProcessor();
    $xslt->importStylesheet($xsl);

    // Load odf content
    $dom = new DOMDocument();
    $dom->recover = true;
    $dom->strictErrorChecking = false;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->load($contentXML);
    $html = $xslt->transformToXml($dom);

    // Copy media files
    if (is_dir($mediaPath)) {
        file_copy($mediaPath, path('asset', (string) $pId));
    }

    file_delete($path);

    return $html;
}

/**
 * Import ZIP
 *
 * @param string $file
 * @param string $path
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function import_zip(string $file, string $path): bool
{
    if (!file_writable($path)) {
        throw new RuntimeException(_('Invalid path %s', $path));
    }

    $zip = new ZipArchive();

    if (!$zip->open($file)) {
        throw new RuntimeException(_('Could not open ZIP file %s', $file));
    }

    if (!$zip->extractTo($path)) {
        throw new RuntimeException(_('Could not extract ZIP file to %s', $path));
    }

    return $zip->close();
}
