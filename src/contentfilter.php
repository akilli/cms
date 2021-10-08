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
    if (!$data = html\placeholder('app-file', $html)) {
        return $html;
    }

    $pattern = '#<app-file id="%s-%s">(?:[^<]*)</app-file>#s';

    foreach ($data as $entityId => $ids) {
        $entity = app\cfg('entity', $entityId);
        $select = array_keys(arr\extract($entity['attr'], ['name', 'mime', 'thumb', 'info']));

        foreach (entity\all($entityId, crit: [['id', $ids]], select: $select) as $item) {
            $type = strstr($item['mime'], '/', true);

            if ($item['mime'] === 'text/html') {
                $a = ['src' => $item['name'], 'allowfullscreen' => 'allowfullscreen'];
                $a += $item['thumb'] ? ['data-thumb' => $item['thumb']] : [];
                $replace = html\element('iframe', $a);
            } elseif ($type === 'image') {
                $replace = html\element('img', ['src' => $item['name'], 'alt' => str\enc($item['info'])]);
            } elseif ($type === 'audio' && !$item['thumb']) {
                $replace = html\element('audio', ['src' => $item['name'], 'controls' => true]);
            } elseif (in_array($type, ['audio', 'video'])) {
                $a = $item['thumb'] ? ['poster' => $item['thumb']] : [];
                $replace = html\element('video', ['src' => $item['name'], 'controls' => true] + $a);
            } elseif ($item['thumb']) {
                $v = html\element('img', ['src' => $item['thumb'], 'alt' => str\enc($item['info'])]);
                $replace = html\element('a', ['href' => $item['name']], $v);
            } else {
                $replace = html\element('a', ['href' => $item['name']], $item['name']);
            }

            $html = preg_replace(sprintf($pattern, $entityId, $item['id']), $replace, $html);
        }
    }

    return $html;
}

/**
 * Makes img-elements somehow responsive
 */
function image(string $html, array $cfg = []): string
{
    $pattern = sprintf(
        '#(?P<img>(?P<pre><img(?:[^>]*) src="(?P<url>%s/(?P<name>(?:.+)\.(?:%s)))")(?P<post>(?:[^>]*)>))#',
        APP['url']['asset'],
        implode('|', APP['image.ext'])
    );

    if (!($cfg = arr\replace(APP['image'], $cfg)) || !$cfg['srcset'] || !preg_match_all($pattern, $html, $match)) {
        return $html;
    }

    $data = entity\all(
        'file',
        crit: [['name', array_unique($match['url'])]],
        select: ['id', 'name', 'thumb'],
        index: 'name'
    );
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
    $call = function (array $match) use ($cache, $cfg, $data, $srcset): string {
        if (str_contains($match['img'], 'srcset="')
            || !($file = app\assetpath($match['name']))
            || !is_file($file)
            || !($width = $cache($file))
        ) {
            return $match[0];
        }

        $img = $match['img'];

        if ($set = $srcset($match['name'], $width)) {
            if ($cfg['sizes'] && $cfg['sizes'] !== '100vw') {
                $sizes = $cfg['sizes'];
            } else {
                $sizes = '(max-width: ' . $width . 'px) 100vw, ' . $width . 'px';
            }

            $img = $match['pre'] . ' srcset="' . $set . '" sizes="' . $sizes . '"' . $match['post'];
        }

        $thumb = $data[$match['url']]['thumb'] ?? null;

        if ($cfg['thumb'] > 0 && $thumb && ($tfile = app\assetpath($thumb)) && is_file($tfile)) {
            $twidth = $cache($tfile);
            $tname = preg_replace('#^' . APP['url']['asset'] . '/#', '', $thumb);
            $tset = $srcset($tname, $twidth, true);
            $tmax = min($cfg['thumb'], $twidth);
            $source = html\element('source', ['media' => '(max-width: ' . $tmax . 'px)', 'srcset' => $tset]);
            $img = html\element('picture', [], $source . $img);
        }

        return $img;
    };

    return preg_replace_callback($pattern, $call, $html);
}

/**
 * Replaces message placeholder, i.e. `<msg/>`, with actual message block
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
