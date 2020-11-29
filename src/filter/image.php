<?php
declare(strict_types=1);

namespace filter\image;

use app;
use arr;
use entity;

/**
 * Makes img-elements somehow responsive
 */
function filter(string $html, array $cfg = []): string
{
    $pattern = '#(?P<figure><figure(?:[^>]*)>)\s*(?P<a><a(?:[^>]*)>)?\s*(?P<img>(?P<pre><img(?:[^>]*) src="(?P<url>'
        . app\file() . '(?P<name>(?:[a-z0-9_\-]+)\.(?:' . implode('|', APP['image.ext']) . ')))")(?P<post>(?:[^>]*)>))#';

    if (!($cfg = arr\replace(APP['image'], $cfg)) || !$cfg['srcset'] || !preg_match_all($pattern, $html, $match)) {
        return $html;
    }

    $data = entity\all('file', [['url', array_unique($match['url'])]], select: ['id', 'url', 'thumb'], index: 'url');
    $cache = function (string $file): int {
        $width = &app\registry('filter')['image'][$file];
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