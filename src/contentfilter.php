<?php
declare(strict_types = 1);

namespace contentfilter;

use app;
use arr;
use entity;
use layout;
use str;

/**
 * Replaces all DB placeholder tags, i.e. `<block id="{entity_id}-{id}" />`, with actual blocks
 */
function block(string $html): string
{
    $pattern = '#<block id="%s"(?:[^>]*)>#s';

    if (preg_match_all(sprintf($pattern, '([a-z_]+)-(\d+)'), $html, $match)) {
        $data = [];

        foreach ($match[1] as $key => $entityId) {
            $data[$entityId][] = $match[2][$key];
        }

        foreach ($data as $entityId => $ids) {
            foreach (entity\all($entityId, [['id', $ids]]) as $item) {
                $html = preg_replace(sprintf($pattern, $entityId . '-' . $item['id']), layout\render(layout\db($item)), $html);
            }
        }
    }

    return preg_replace('#<block(?:[^>]*)>#s', '', $html);
}

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
    $pattern = '#(?P<figure><figure class="(?P<class>[^"]+)">)\s*(?P<a><a(?:[^>]*)>)?\s*(?P<img>(?P<pre><img(?:[^>]*) src="'
        . app\file() . '(?P<name>(?P<id>\d+)\.(?P<ext>' . implode('|', APP['image.ext']) . '))")(?P<post>(?:[^>]*)>))#';

    if (!($cfg = arr\replace(APP['image'], $cfg)) || !$cfg['srcset'] || !preg_match_all($pattern, $html, $match)) {
        return $html;
    }

    $data = entity\all('file', [['id', array_unique($match['id'])]], ['select' => ['id']]);
    $call = function (array $m) use ($cfg, $data): string {
        $item = $data[$m['id']] ?? null;

        if (strpos($m['img'], 'srcset="') !== false || !$item || !($file = app\filepath($m['name'])) || !is_file($file)) {
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

        foreach ($cfg['srcset'] as $s) {
            if ($s >= $w[$file]) {
                break;
            }

            $set .= ($set ? ', ' : '') . app\file('resize-' . $s . '/' . $m['name']) . ' ' . $s . 'w';
        }

        if ($set) {
            $set .= ', ' . app\file($m['name']) . ' ' . $w[$file] . 'w';
            $img = $m['pre'] . ' srcset="' . $set . '"' . $sizes . $m['post'];
        }

        return $m['figure'] . $m['a'] . $img;
    };

    return preg_replace_callback($pattern, $call, $html);
}

/**
 * Replaces message placeholder, i.e. `<msg />`, with actual message block
 */
function msg(string $html): string
{
    $msg = '';

    foreach (app\msg() as $item) {
        $msg .= app\html('p', [], $item);
    }

    $msg = $msg ? app\html('section', ['class' => 'msg'], $msg) : '';

    return str_replace(app\html('msg'), $msg, $html);
}
