<?php
namespace qnd;

use DOMDocument;
use Exception;
use XSLTProcessor;

/**
 * Import content
 *
 * @param string $file
 *
 * @return bool
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

    if (!$toc = file_one($path, ['name' => config('import.toc'), 'recursive' => true])) {
        message(_('File %s not found', config('import.toc')));
        return false;
    }

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
        $pages[$i]['active'] = true;
        $pages[$i]['content'] = $item['file'] ? import_content($toc['dir'] . '/' . $item['file']) : null;
        $pages[$i]['oid'] = $item['file'] ?: null;
    }

    return save('page', $pages);
}

/**
 * Import content
 *
 * @param string $file
 *
 * @return bool
 */
function import_content($file)
{
    if (!file_exists($file)) {
        return '';
    }

    $html = '';
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    if ($ext === 'html') {
        $html = import_html($file);
    } elseif ($ext === 'odt') {
        $html = import_odt($file);
    }

    return preg_match('#<body(.*)>(.*)</body>#isU', $html, $match) ? $match[2] : $html;
}

/**
 * Import HTML document
 *
 * @param string $file
 *
 * @return string
 */
function import_html($file)
{
    if (!file_exists($file) || !$html = file_get_contents($file)) {
        return '';
    }

    $pattern = sprintf('#%s(.*)%s#isU', config('import.start'), config('import.start'));

    return preg_match($pattern, $html, $match) ? $match[1] : $html;
}

/**
 * Import ODT
 *
 * @param string $file
 *
 * @return string
 */
function import_odt($file)
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
