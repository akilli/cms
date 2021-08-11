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
 * Replaces all DB placeholder tags, i.e. `<app-block id="{entity_id}-{id}"></app-block>`, with actual blocks
 */
function block(string $html): string
{
    $pattern = '#<app-block id="%s-%s"(?:[^>]*)>\s*</app-block>#s';

    if (preg_match_all(sprintf($pattern, '([a-z_\.]+)', '(\d+)'), $html, $match)) {
        $data = [];

        foreach ($match[1] as $key => $entityId) {
            if (!in_array($match[2][$key], $data[$entityId] ?? [])) {
                $data[$entityId][] = $match[2][$key];
            }
        }

        foreach ($data as $entityId => $ids) {
            foreach (entity\all($entityId, crit: [['id', $ids]]) as $item) {
                $html = preg_replace(
                    sprintf($pattern, $entityId, $item['id']),
                    layout\render_entity($entityId, $item['id'], $item),
                    $html
                );
            }
        }
    }

    return preg_replace('#<app-block(?:[^>]*)>\s*</app-block>#s', '', $html);
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
            || !($item = $data[$match['url']] ?? null)
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

        if ($cfg['thumb'] > 0 && $item['thumb'] && ($tfile = app\assetpath($item['thumb'])) && is_file($tfile)) {
            $twidth = $cache($tfile);
            $tname = preg_replace('#^' . APP['url']['asset'] . '/#', '', $item['thumb']);
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
        $msg .= html\element('p', [], $count > 1 ? sprintf('%s (%d)', $item, $count) : $item);
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
