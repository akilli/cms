<?php
declare(strict_types = 1);

namespace contentfilter;

use app;
use arr;
use entity;
use str;

/**
 * Converts email addresses to HTML entity hex format
 */
function email(string $html): string
{
    $call = function (array $m): string {
        return str\hex($m[0]);
    };

    return preg_replace_callback('#(?:mailto:)?[\w.-]+@[\w.-]+\.[a-z]{2,6}#im', $call, $html);
}

/**
 * Makes img-elements somehow responsive
 */
function image(string $html, array $cfg = []): string
{
    $pattern = '#(?P<figure><figure class="(?P<class>[^"]+)">)\s*(?P<a><a(?:[^>]*)>)?\s*(?P<img>(?P<pre><img(?:[^>]*) src="' . APP['url.file'] . '(?P<name>(?P<id>\d+)(?P<thumb>\.thumb)?\.(?P<ext>' . implode('|', APP['image.ext']) . '))")(?P<post>(?:[^>]*)>))#';

    if (!($cfg = arr\replace(APP['image'], $cfg)) || !$cfg['srcset'] || !preg_match_all($pattern, $html, $match)) {
        return $html;
    }

    $data = entity\all('file', [['id', array_unique($match['id'])]], ['select' => ['id', 'thumb_url', 'thumb_ext']]);
    $call = function (array $m) use ($cfg, $data): string {
        $item = $data[$m['id']] ?? null;

        if (strpos($m['img'], 'srcset="') !== false || !$item || !($file = app\path('file', $m['name'])) || !is_file($file)) {
            return $m[0];
        }

        $w = & app\registry('contentfilter.image');
        $w[$file] = $w[$file] ?? getimagesize($file)[0] ?? 0;

        if (!$w[$file]) {
            return $m[0];
        }

        $img = $m['img'];
        $sizes = $cfg['sizes'] && $cfg['sizes'] !== '100vw' ? ' sizes="' . $cfg['sizes'] . '"' : '';
        $set = '';
        $tn = $item['thumb_url'] && in_array($item['thumb_ext'], APP['image.ext']) ? basename($item['thumb_url']) : null;

        foreach ($cfg['srcset'] as $s) {
            if ($s >= $w[$file]) {
                break;
            }

            $set .= ($set ? ', ' : '') . APP['url.file'] . 'resize-' . $s . '/' . $m['name'] . ' ' . $s . 'w';
        }

        if ($set) {
            $set .= ', ' . APP['url.file'] . $m['name'] . ' ' . $w[$file] . 'w';
            $img = $m['pre'] . ' srcset="' . $set . '"' . $sizes . $m['post'];
        }

        if ($cfg['thumb'] > 0 && !$m['thumb'] && $tn && ($tf = app\path('file', $tn)) && is_file($tf)) {
            $w[$tf] = $w[$tf] ?? getimagesize($tf)[0] ?? 0;
            $max = min($cfg['thumb'], $w[$tf], $w[$file] - APP['image.max']);
            $tset = '';

            foreach ($cfg['srcset'] as $s) {
                if ($s >= $w[$tf]) {
                    break;
                }

                $tset .= ($tset ? ', ' : '') . APP['url.file'] . 'resize-' . $s . '/' . $tn . ' ' . $s . 'w';
            }

            $tset .= ($tset ? ', ' : '') . APP['url.file'] . $tn . ' ' . $w[$tf] . 'w';
            $source = '<source media="(max-width: ' . $max . 'px)" srcset="' . $tset . '"' . '/>';
            $img = app\html('picture', [], $source . $img);
        }

        return $m['figure'] . $m['a'] . $img;
    };

    return preg_replace_callback($pattern, $call, $html);
}
