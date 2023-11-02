<?php
declare(strict_types=1);

namespace contentfilter;

use app;
use arr;
use entity;
use html;
use parser;
use str;

/**
 * Minimal cache busting
 */
function asset(string $html): string
{
    $cache = function (string $type, string $id): int {
        if (($mtime = &app\registry('contentfilter.asset')[$type . ':' . $id]) === null) {
            $file = match ($type) {
                'asset' => app\assetpath($id),
                'gui' => app\guipath($id),
                'ext' => app\extpath($id),
                default => null,
            };
            $mtime = filemtime($file) ?: 0;
        }

        return $mtime;
    };
    $pattern = '#(/(?:resize|crop)-(?:[^/]+))?/(asset|gui|ext)/((?:[^",\s]+)\.(?:[a-z0-9]+))#';
    $call = function (array $match) use ($cache): string {
        $mtime = $cache($match[2], $match[3]);

        return '/' . $mtime . $match[1] . '/' . $match[2] . '/' . $match[3];
    };

    return preg_replace_callback($pattern, $call, $html) ?? $html;
}

/**
 * Converts email addresses to HTML entity hex format
 */
function email(string $html): string
{
    return preg_replace_callback(
        '#(?:mailto:)?[\w\.\-]+@[\w\.\-]+\.[a-z]{2,6}#im',
        fn(array $m): string => str\hex($m[0]),
        $html
    );
}

/**
 * Replaces all entity placeholder tags, i.e. `<app-entity id="{entity_id}-{id}"></app-entity>`
 */
function entity(string $html): string
{
    if (!$data = parser\tag($html, 'app-entity')) {
        return $html;
    }

    $pattern = '#<app-entity id="%s-%s">(?:[^<]*)</app-entity>#s';

    foreach ($data as $entityId => $ids) {
        $entity = app\cfg('entity', $entityId);
        $select = array_keys(arr\extract($entity['attr'], ['id', 'name', 'url']));

        foreach (entity\all($entityId, crit: [['id', $ids]], select: $select) as $id => $item) {
            $allowed = app\allowed(app\id($entity['id'], 'view'));
            $name = $item['name'] ?? (string)$id;
            $url = $item['url'] ?? app\actionurl($entity['id'], 'view', $id);
            $html = preg_replace(
                sprintf($pattern, $entityId, $item['id']),
                $allowed ? html\element('a', ['href' => $url], $name) : $name,
                $html
            );
        }
    }

    return preg_replace('#<app-entity(?:[^>]*)>(?:[^<]*)</app-entity>#s', '', $html);
}

/**
 * Replaces all file entity placeholder tags, i.e. `<app-file id="{id}"></app-file>`
 */
function file(string $html): string
{
    $pattern = '#<app-file(?:[^>]*)>(?:[^<]*)</app-file>#s';

    if (!preg_match_all($pattern, $html, $match)) {
        return $html;
    }

    foreach (entity\all('file', crit: [['id', $match[1]]], select: ['name', 'mime', 'info']) as $id => $item) {
        $type = strstr($item['mime'], '/', true);
        $attrs = ['src' => $item['name']];
        $replace = match (true) {
            $type === 'image' => html\element('img', $attrs + ['alt' => str\enc($item['info'])]),
            $type === 'video' => html\element('video', $attrs + ['controls' => true]),
            $type === 'audio' => html\element('audio', $attrs + ['controls' => true]),
            default => html\element('a', ['href' => $item['name']], $item['name']),
        };
        $html = preg_replace(sprintf('#<app-file id="%s">(?:[^<]*)</app-file>#s', $id), $replace, $html);
    }

    return preg_replace($pattern, '', $html);
}

/**
 * Makes img-elements somehow responsive
 */
function image(string $html, array $cfg = []): string
{
    $ext = implode('|', APP['image.ext']);
    $pattern = sprintf('#<img(?:[^>]*) src="%s/((?:[^"]+)\.(?:%s))"(?:[^>]*)>#', APP['url']['asset'], $ext);
    $cfg = arr\replace(APP['image.responsive'], $cfg);
    $srcset = function (string $name, int $width) use ($cfg): string {
        $set = [];

        foreach ($cfg['srcset'] as $breakpoint) {
            if ($breakpoint >= $width) {
                break;
            }

            $set[] = app\resizeurl(app\asseturl($name), $breakpoint) . ' ' . $breakpoint . 'w';
        }

        if ($set) {
            $set[] = app\asseturl($name) . ' ' . $width . 'w';
        }

        return implode(', ', $set);
    };
    $cache = function (string $file): array {
        if (($info = &app\registry('contentfilter.image')[$file]) === null) {
            $info = getimagesize($file) ?? [];
        }

        return $info;
    };
    $call = function (array $match) use ($cfg, $srcset, $cache): string {
        $attrs = parser\attr($match[0]);
        $file = app\assetpath($attrs['src']);

        if (!empty($attrs['srcset'])
            || !is_file($file)
            || !($width = $cache($file)[0] ?? null)
            || !($set = $srcset($match[1], $width))
        ) {
            return $match[0];
        }

        $attrs['width'] ??= $width;
        $attrs['srcset'] = $set;
        $attrs['sizes'] = match ($cfg['sizes']) {
            null, '100vw' => '(min-width: ' . $width . 'px) ' . $width . 'px, 100vw',
            default => $cfg['sizes'],
        };

        return html\element('img', $attrs);
    };

    return $cfg['srcset'] ? preg_replace_callback($pattern, $call, $html) : $html;
}

/**
 * Replaces message placeholder, i.e. `<app-msg></app-msg>`, with actual message block
 */
function msg(string $html): string
{
    $msg = '';

    foreach (app\msg() as $item => $count) {
        $msg .= html\element('p', [], $count > 1 ? $item . ' (' . $count . ')' : $item);
    }

    return str_replace(html\element('app-msg'), $msg ? html\element('section', ['class' => 'msg'], $msg) : '', $html);
}

/**
 * Converts telephone numbers to HTML entity hex format
 */
function tel(string $html): string
{
    return preg_replace_callback('#(?:tel:)\+\d+#i', fn(array $m): string => str\hex($m[0]), $html);
}
