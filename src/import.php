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
 */
function import_zip(string $file): bool
{
    $path = path('tmp', uniqid('import', true));

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

    // Copy media files
    file_copy($toc['dir'] . '/media', project_path('media'));

    $trans = trans(
        function () use ($toc) {
            $import = csv_unserialize(file_get_contents($toc['path']), ['keys' => ['pos', 'name', 'file']]);

            // Delete old menu, nodes and pages + create new menu
            $menu = [-1 => ['uid' => 'page', 'name' => 'Page']];

            if (!delete('page') || !delete('menu', ['uid' => 'page']) || !save('menu', $menu)) {
                throw new RuntimeException(_('Import error'));
            }

            // Create new contents
            $levels = [0];

            foreach ($import as $item) {
                $pages = [];
                $pages[-1]['name'] = $item['name'];
                $pages[-1]['active'] = true;
                $pages[-1]['content'] = $item['file'] ? import_content($toc['dir'] . '/' . $item['file']) : null;
                $pages[-1]['oid'] = $item['file'] ? pathinfo($item['file'], PATHINFO_FILENAME): null;

                if (!save('page', $pages)) {
                    throw new RuntimeException(_('Import error'));
                }

                $level = substr_count($item['pos'], '.');
                $basis = !empty($levels[$level - 1]) ? $levels[$level - 1] : 0;

                $nodes = [];
                $nodes[-1]['name'] = $item['name'];
                $nodes[-1]['target'] = 'page/view/id/' . $pages[-1]['id'];
                $nodes[-1]['mode'] = 'child';
                $nodes[-1]['position'] = $menu[-1]['id'] . ':' . $basis;

                if (!save('node', $nodes)) {
                    throw new RuntimeException(_('Import error'));
                }

                $levels[$level] = $nodes[-1]['lft'];
            }
        }
    );
    file_delete($path);

    return $trans;
}

/**
 * Import page
 *
 * @param string $file
 *
 * @return bool
 */
function import_page(string $file): bool
{
    $oid = pathinfo($file, PATHINFO_FILENAME);
    $content = import_content($file);

    if (!$pages = all('page', ['oid' => $oid])) {
        $pages = [];
        $pages[-1]['name'] = $oid;
        $pages[-1]['active'] = true;
        $pages[-1]['oid'] = $oid;
    }

    foreach ($pages as $id => $page) {
        $pages[$id]['content'] = $content;
    }

    return save('page', $pages);
}

/**
 * Import content
 *
 * @param string $file
 *
 * @return string
 */
function import_content(string $file): string
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
function import_html(string $file): string
{
    if (!file_exists($file) || !$html = file_get_contents($file)) {
        return '';
    }

    $pattern = sprintf('#%s(.*)%s#isU', config('import.start'), config('import.end'));

    return preg_match($pattern, $html, $match) ? $match[1] : $html;
}

/**
 * Import ODT
 *
 * @param string $file
 *
 * @return string
 *
 * @throws RuntimeException
 */
function import_odt(string $file): string
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

    if (!$xsl->load($xslFile)) {
        throw new RuntimeException(_('Could not load %s', $xslFile));
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

    // Copy media files
    if (is_dir($mediaPath)) {
        file_copy($mediaPath, project_path('media'));
    }

    file_delete($path);

    return $html;
}
