<?php
declare(strict_types = 1);

namespace qnd;

use DOMDocument;
use Exception;
use RuntimeException;
use XSLTProcessor;

/**
 * Import project
 *
 * @todo Import homepage?!
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
        unzip($file, $path);
    } catch (Exception $e) {
        message($e->getMessage());
        return false;
    }

    if (!is_file($toc)) {
        message(_('File %s not found', basename($toc)));
        return false;
    }

    $trans = db_trans(
        function () use ($name, $path, $toc) {
            $csv = csv_unserialize(file_get_contents($toc), ['keys' => ['pos', 'name', 'file']]);
            $project = ['uid' => $name, 'name' => $name, 'active' => true];

            if (!save('project', $project)) {
                throw new RuntimeException(_('Import error'));
            }

            $asset = path('asset', (string) $project['id']);
            file_copy($path . '/media', $asset . '/media');
            $log = [null];
            $prev = 0;

            foreach ($csv as $data) {
                if (($cur = substr_count(trim($data['pos'], '.'), '.')) > $prev + 1) {
                    $cur = $prev + 1;
                }

                $page = [
                    'name' => $data['name'],
                    'active' => true,
                    'parent_id' => $log[$cur],
                    'content' => $data['file'] ? import_content($path . '/' . $data['file'], $project['id']) : '',
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

    $from = ['#="/?index\.html"#Ui', '#="/?(Pictures|media)/([0-9/]*)([^"/]+)"#Ui', '#="/?(([^"]+)\.html)"#Ui'];
    $to = ['="/"', '="/asset/' . $pId . '/media/$3"', '="/$1"'];

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

    return preg_match(IMPORT['html'], $html, $match) ? $match[1] : $html;
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
        file_copy($mediaPath, path('asset', $pId . '/media'));
    }

    file_delete($path);

    return $html;
}
