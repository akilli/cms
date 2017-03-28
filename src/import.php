<?php
declare(strict_types = 1);

namespace qnd;

use DOMDocument;
use Exception;
use RuntimeException;
use XSLTProcessor;

/**
 * Import project from ZIP file
 *
 * @todo Import homepage?!
 *
 * @param string $name
 * @param string $file
 *
 * @return bool
 */
function import_zip(string $name, string $file): bool
{
    $path = path('tmp', uniqid('import', true));
    $toc = $path . '/' . data('app', 'import.toc');

    try {
        unzip($file, $path);
    } catch (Exception $e) {
        message($e->getMessage());
        return false;
    }

    if (!is_file($toc)) {
        message(_('File %s not found', basename($toc)));
        return false;
    }

    // Copy media files
    file_copy($path . '/media', project_path('media'));

    $trans = db_trans(
        function () use ($name, $path, $toc) {
            $csv = csv_unserialize(file_get_contents($toc), ['keys' => ['pos', 'name', 'file']]);

            if (!$project = import_project($name)) {
                throw new RuntimeException(_('Import error'));
            }

            // Create new page
            foreach ($csv as $data) {
                $file = $data['file'] ? $path . '/' . $data['file'] : null;

                if (!import_page('page', $project['id'], $data['name'], $file)) {
                    throw new RuntimeException(_('Import error'));
                }
            }
        }
    );
    file_delete($path);

    return $trans;
}

/**
 * Import project
 *
 * @param string $name
 *
 * @return array|null
 */
function import_project(string $name): ?array
{
    $data = ['uid' => $name, 'name' => $name, 'active' => true];

    return save('project', $data) ? $data : null;
}

/**
 * Import page
 *
 * @param string $eId
 * @param int $pId
 * @param string $name
 * @param string $file
 *
 * @return array|null
 */
function import_page(string $eId, int $pId, string $name, string $file = null): ?array
{
    $content = $file ? import_content($file) : '';
    $data = ['name' => $name, 'active' => true, 'content' => $content, 'project_id' => $pId];

    return save($eId, $data) ? $data : null;
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

    $pattern = sprintf('#%s(.*)%s#isU', data('app', 'import.start'), data('app', 'import.end'));

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
