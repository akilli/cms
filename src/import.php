<?php
namespace qnd;

use DOMDocument;
use Exception;
use RuntimeException;
use XSLTProcessor;

/**
 * Import content
 *
 * @param string $file
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function import_zip($file): bool
{
    $path = path('tmp', 'import_' . project('id'));

    if (file_exists($path)) {
        file_delete($path);
    }

    try {
        unzip($file, $path);
    } catch (Exception $e) {
        message($e->getMessage());
        return false;
    }

    $toc = file_one($path, ['name' => 'toc.csv', 'recursive' => true]);
    $import = csv_unserialize(file_get_contents($toc['path']), ['keys' => ['pos', 'name', 'file']]);

    // Menu
    if (!one('menu', ['uid' => 'page'])) {
        $menu = skeleton('menu', 1);
        $menu[-1]['uid'] = 'page';
        $menu[-1]['name'] = 'Page';

        if (!save('menu', $menu)) {
            return false;
        }
    }

    // Menu Nodes + Pages
    $pages = skeleton('page', count($import));

    foreach ($import as $key => $item) {
        $i = -$key - 1;
        $pages[$i]['name'] = $item['name'];
        $pages[$i]['content'] = $item['file'] ? import_content($toc['dir'] . '/' . $item['file']) : '';
    }

    return save('page', $pages);
}

/**
 * Import content
 *
 * @param string $file
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function import_content($file)
{
    if (!file_exists($file)) {
        throw new RuntimeException(_('Invalid file %s', $file));
    }

    $html = '';
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    if ($ext === 'html') {
        if (!$html = file_get_contents($file)) {
            return '';
        }

        if (preg_match('#' . config('import.start') . '(.*)' . config('import.start') .'#isU', $html, $match)) {
            $html = $match[1];
        }
    } elseif (in_array($ext, ['odt', 'ott'])) {
        $html = import_odf($file);
    }

    // We just need the content
    if (preg_match('#<body(.*)>(.*)</body>#isU', $html, $match)) {
        $html = $match[2];
    }

    return $html;
}

/**
 * Import open document
 *
 * @param string $file
 *
 * @return string
 */
function import_odf($file)
{
    $html = '';
    $path = path('tmp', uniqid(basename($file), true));
    $mediaPath = $path . '/Pictures';
    $contentXML = $path . '/content.xml';
    $xslFile = path('xml', 'odt.xsl');

    if (!unzip($file, $path) || !is_file($contentXML)) {
        return $html;
    }

    // Load stylesheet
    $xsl = new DOMDocument;

    if ($xsl->load($xslFile) !== true) {
        return $html;
    }

    // Load XSLT processor
    $xslt = new XSLTProcessor;
    $xslt->importStylesheet($xsl);

    // Load odf content
    $dom = new DOMDocument;
    $dom->recover = true;
    $dom->strictErrorChecking = false;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->load($contentXML);
    $html = $xslt->transformToXml($dom);

    // Copy images and fix url
    if (is_dir($mediaPath)) {
        file_copy($mediaPath, path('media'));
    }

    file_delete($path);

    return $html;
}
