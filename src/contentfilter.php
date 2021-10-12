<?php
declare(strict_types=1);

namespace contentfilter;

use app;
use arr;
use entity;
use html;
use layout;
use str;

/**
 * Minimal cache busting
 */
function asset(string $html): string
{
    $cache = function (string $type, string $id): int {
        if (($mtime = &app\registry('contentfilter')['asset'][$type . ':' . $id]) === null) {
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
 * Replaces all block placeholder tags, i.e. `<app-block id="{entity_id}-{id}"></app-block>`
 */
function block(string $html): string
{
    if (!$data = html\placeholder('app-block', $html)) {
        return $html;
    }

    $pattern = '#<app-block id="%s-%s">(?:[^<]*)</app-block>#s';

    foreach ($data as $entityId => $ids) {
        foreach (entity\all($entityId, crit: [['id', $ids]]) as $item) {
            $html = preg_replace(
                sprintf($pattern, $item['entity_id'], $item['id']),
                layout\render_entity($entityId, $item['id'], $item),
                $html
            );
        }
    }

    return $html;
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
    if (!$data = html\placeholder('app-entity', $html)) {
        return $html;
    }

    $pattern = '#<app-entity id="%s-%s">(?:[^<]*)</app-entity>#s';

    foreach ($data as $entityId => $ids) {
        $entity = app\cfg('entity', $entityId);
        $select = array_keys(arr\extract($entity['attr'], ['id', 'name', 'entity_id', 'url']));

        foreach (entity\all($entityId, crit: [['id', $ids]], select: $select) as $id => $item) {
            $allowed = app\allowed(app\id($item['entity_id'] ?? $entity['id'], 'view'));
            $name = $item['name'] ?? (string)$id;
            $url = $item['url'] ?? app\actionurl($item['entity_id'] ?? $entity['id'], 'view', $id);
            $html = preg_replace(
                sprintf($pattern, $entityId, $item['id']),
                $allowed ? html\element('a', ['href' => $url], $name) : $name,
                $html
            );
        }
    }

    return $html;
}

function file(string $html): string
{
    $pattern = '#<app-file id="%s-%s">(?:[^<]*)</app-file>#s';
    $select = ['name', 'entity_id', 'mime', 'info'];

    if (!preg_match_all(sprintf($pattern, '([a-z][a-z_\.]*)', '(\d+)'), $html, $match)) {
        return $html;
    }

    foreach (entity\all('file', crit: [['id', $match[2]]], select: $select) as $id => $item) {
        $type = strstr($item['mime'], '/', true);
        $attrs = ['src' => $item['name']];
        $replace = match (true) {
            $type === 'image' => html\element('img', $attrs + ['alt' => str\enc($item['info'])]),
            $type === 'video' => html\element('video', $attrs + ['controls' => true]),
            $type === 'audio' => html\element('audio', $attrs + ['controls' => true]),
            $item['mime'] === 'text/html' => html\element('iframe', $attrs + ['allowfullscreen' => true]),
            default => html\element('a', ['href' => $item['name']], $item['name']),
        };
        $html = preg_replace(sprintf($pattern, '(file|' . $item['entity_id'] . ')', $id), $replace, $html);
    }

    return $html;
}

/**
 * Makes img-elements somehow responsive
 */
function image(string $html, array $cfg = []): string
{
    $pattern = sprintf(
        '#(<img(?:[^>]*) src="%s/((?:.+)\.(?:%s))")((?:[^>]*)>)#',
        APP['url']['asset'],
        implode('|', APP['image.ext'])
    );
    $cfg = arr\replace(APP['image'], $cfg);

    if (!$cfg['srcset'] || !preg_match_all($pattern, $html)) {
        return $html;
    }

    $cache = function (string $file): int {
        $width = &app\registry('contentfilter')['image'][$file];
        $width ??= getimagesize($file)[0] ?? 0;

        return $width;
    };
    $srcset = function (string $name, int $width, bool $force = false) use ($cache, $cfg): string {
        $set = [];

        foreach ($cfg['srcset'] as $breakpoint) {
            if ($breakpoint >= $width) {
                break;
            }

            $set[] = app\resizeurl(app\asseturl($name), $breakpoint) . ' ' . $breakpoint . 'w';
        }

        if ($set || $force) {
            $set[] = app\asseturl($name) . ' ' . $width . 'w';
        }

        return implode(', ', $set);
    };
    $call = function (array $match) use ($cache, $cfg, $srcset): string {
        $file = app\assetpath($match[2]);

        if (str_contains($match[0], 'srcset="')
            || !is_file($file)
            || !($width = $cache($file))
            || !($set = $srcset($match[2], $width))
        ) {
            return $match[0];
        }

        if ($cfg['sizes'] && $cfg['sizes'] !== '100vw') {
            $sizes = $cfg['sizes'];
        } else {
            $sizes = '(max-width: ' . $width . 'px) 100vw, ' . $width . 'px';
        }

        return $match[1] . ' srcset="' . $set . '" sizes="' . $sizes . '"' . $match[3];
    };

    return preg_replace_callback($pattern, $call, $html);
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
