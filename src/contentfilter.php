<?php
declare(strict_types=1);

namespace contentfilter;

use app;
use arr;
use entity;
use layout;
use str;

/**
 * Replaces all DB placeholder tags, i.e. `<editor-block id="{entity_id}-{id}"></editor-block>`, with actual blocks
 */
function block(string $html): string
{
    $pattern = '#<editor-block id="%s"(?:[^>]*)>\s*</editor-block>#s';

    if (preg_match_all(sprintf($pattern, '([a-z_]+)-(\d+)'), $html, $match)) {
        $data = [];

        foreach ($match[1] as $key => $entityId) {
            $data[$entityId][] = $match[2][$key];
        }

        foreach ($data as $entityId => $ids) {
            foreach (entity\all($entityId, [['id', $ids]]) as $item) {
                $html = preg_replace(
                    sprintf($pattern, $entityId . '-' . $item['id']),
                    layout\render(layout\db($item)),
                    $html
                );
            }
        }
    }

    return preg_replace('#<editor-block(?:[^>]*)>\s*</editor-block>#s', '', $html);
}

/**
 * Converts email addresses to HTML entity hex format
 */
function email(string $html): string
{
    return preg_replace_callback(
        '#(?:mailto:)?[\w.-]+@[\w.-]+\.[a-z]{2,6}#im',
        fn(array $m): string => str\hex($m[0]),
        $html
    );
}

/**
 * Converts telephone numbers to HTML entity hex format
 */
function tel(string $html): string
{
    return preg_replace_callback('#(?:tel:)\+\d+#i', fn(array $m): string => str\hex($m[0]), $html);
}

/**
 * Makes img-elements somehow responsive
 */
function image(string $html, array $cfg = []): string
{
    $pattern = '#(?P<figure><figure(?:[^>]*)>)\s*(?P<a><a(?:[^>]*)>)?\s*(?P<img>(?P<pre><img(?:[^>]*) src="(?P<url>'
        . app\file() . '(?P<name>(?:[a-z0-9_\-]+)\.(?:' . implode('|', APP['image.ext']) . ')))")(?P<post>(?:[^>]*)>))#';

    if (!($cfg = arr\replace(APP['image'], $cfg)) || !$cfg['srcset'] || !preg_match_all($pattern, $html, $match)) {
        return $html;
    }

    $data = entity\all('file', [['url', array_unique($match['url'])]], select: ['id', 'url', 'thumb'], index: 'url');
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

            $set[] = app\file('resize-' . $breakpoint . '/' . $name) . ' ' . $breakpoint . 'w';
        }

        if ($set || $force) {
            $set[] = app\file($name) . ' ' . $width . 'w';
        }

        return implode(', ', $set);
    };
    $call = function (array $match) use ($cache, $cfg, $data, $srcset): string {
        if (str_contains($match['img'], 'srcset="')
            || !($item = $data[$match['url']] ?? null)
            || !($file = app\filepath($match['name']))
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

        if ($cfg['thumb'] > 0 && $item['thumb'] && ($tfile = app\filepath($item['thumb'])) && is_file($tfile)) {
            $twidth = $cache($tfile);
            $tset = $srcset(basename($item['thumb']), $twidth, true);
            $tmax = min($cfg['thumb'], $twidth);
            $source = app\html('source', ['media' => '(max-width: ' . $tmax . 'px)', 'srcset' => $tset]);
            $img = app\html('picture', [], $source . $img);
        }

        return $match['figure'] . $match['a'] . $img;
    };

    return preg_replace_callback($pattern, $call, $html);
}

/**
 * Replaces message placeholder, i.e. `<msg/>`, with actual message block
 */
function msg(string $html): string
{
    $msg = '';

    foreach (app\msg() as $item) {
        $msg .= app\html('p', [], $item);
    }

    return str_replace(
        app\html('msg'),
        $msg ? app\html('section', ['class' => 'msg'], $msg) : '',
        $html
    );
}
