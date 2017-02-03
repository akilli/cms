<?php
declare(strict_types=1);

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
    $toc = $path . '/' . data('import', 'toc');

    try {
        unzip($file, $path);
    } catch (Exception $e) {
        message($e->getMessage());
        return false;
    }

    if (!is_file($toc)) {
        message(_('File %s not found', data('import', 'toc')));
        return false;
    }

    // Copy media files
    file_copy($path . '/media', project_path('media'));

    $trans = db_trans(
        function () use ($path, $toc) {
            $import = csv_unserialize(file_get_contents($toc), ['keys' => ['pos', 'name', 'file']]);

            // Delete old menu, nodes and pages + create new menu
            $menu = [-1 => ['uid' => 'page', 'name' => 'Page']];

            if (!delete('page') || !delete('menu', ['uid' => 'page']) || !save('menu', $menu)) {
                throw new RuntimeException(_('Import error'));
            }

            // Create new contents
            $levels = [0];
            $oids = [];

            foreach ($import as $item) {
                $oid = pathinfo($item['file'], PATHINFO_FILENAME);

                if (empty($oids[$oid])) {
                    $pages = [];
                    $pages[-1]['name'] = $item['name'];
                    $pages[-1]['active'] = true;
                    $pages[-1]['content'] = $item['file'] ? import_content($path . '/' . $item['file']) : null;

                    if (!save('page', $pages)) {
                        throw new RuntimeException(_('Import error'));
                    }

                    $oids[$oid] = $pages[-1]['id'];
                }

                $level = substr_count($item['pos'], '.');
                $basis = !empty($levels[$level - 1]) ? $levels[$level - 1] : 0;

                $nodes = [];
                $nodes[-1]['name'] = $item['name'];
                $nodes[-1]['target'] = '/page/view/' . $oids[$oid];
                $nodes[-1]['mode'] = 'child';
                $nodes[-1]['pos'] = $menu[-1]['id'] . ':' . $basis;

                if (!save('node', $nodes)) {
                    throw new RuntimeException(_('Import error'));
                }

                $levels[$level] = $nodes[-1]['lft'];
            }

            if (!in_array('index', $oids) && ($p = glob($path . '/index.{html,odt}', GLOB_BRACE)) && !import_page($p[0])) {
                throw new RuntimeException(_('Import error'));
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
    $pages = [];
    $pages[-1]['name'] = pathinfo($file, PATHINFO_FILENAME);
    $pages[-1]['active'] = true;
    $pages[-1]['content'] = import_content($file);

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

    if (preg_match('#<body(.*)>(.*)</body>#isU', $html, $match)) {
        $html = $match[2];
    }

    $from = ['#="index\.html"#Ui', '#="media/([^"]+)"#Ui', '#="(([^"]+)\.html)"#Ui'];
    $to = ['="/"', '="' . url_media() . '/$1"', '="/$1"'];

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
    if (!file_exists($file) || !$html = file_get_contents($file)) {
        return '';
    }

    $pattern = sprintf('#%s(.*)%s#isU', data('import', 'start'), data('import', 'end'));

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
    $xslFile = path('data', 'odt.xsl');

    if (!unzip($file, $path) || !is_file($contentXML)) {
        return $html;
    }

    // Load stylesheet
    $xsl = new DOMDocument();

    if (!$xsl->load($xslFile)) {
        throw new RuntimeException(_('Could not load %s', $xslFile));
    }

    // Load XSLT processor
    $xslt = new XSLTProcessor();
    $xslt->registerPHPFunctions();
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
        file_copy($mediaPath, project_path('media'));
    }

    file_delete($path);

    return $html;
}

/**
 * Fix link path
 *
 * @param string $url
 *
 * @return string
 */
function import_link(string $url): string
{
    $parts = explode('/', $url);
    $base = array_pop($parts);
    $dir = $parts ? array_pop($parts) : '';

    return in_array($dir, ['Pictures', 'media']) ? url_media($base) : url($base);
}
