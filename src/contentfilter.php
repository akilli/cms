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
                $html = preg_replace(sprintf($pattern, $entityId . '-' . $item['id']), layout\render(layout\db($item)), $html);
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
    return preg_replace_callback('#(?:mailto:)?[\w.-]+@[\w.-]+\.[a-z]{2,6}#im', fn(array $m): string => str\hex($m[0]), $html);
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

    $data = entity\all('file', [['url', array_unique($match['url'])]], ['index' => 'url', 'select' => ['id', 'url', 'thumb']]);
    $call = function (array $m) use ($cfg, $data): string {
        $item = $data[$m['url']] ?? null;

        if (str_contains($m['img'], 'srcset="') || !$item || !($file = app\filepath($m['name'])) || !is_file($file)) {
            return $m[0];
        }

        $w = &app\registry('contentfilter:image');
        $w[$file] = $w[$file] ?? getimagesize($file)[0] ?? 0;

        if (!$w[$file]) {
            return $m[0];
        }

        $img = $m['img'];
        $set = '';

        foreach ($cfg['srcset'] as $s) {
            if ($s >= $w[$file]) {
                break;
            }

            $set .= ($set ? ', ' : '') . app\file('resize-' . $s . '/' . $m['name']) . ' ' . $s . 'w';
        }

        if ($set) {
            $set .= ', ' . app\file($m['name']) . ' ' . $w[$file] . 'w';
            $sizes = $cfg['sizes'] && $cfg['sizes'] !== '100vw' ? $cfg['sizes'] : '(max-width: ' . $w[$file] . 'px) 100vw, ' . $w[$file] . 'px';
            $img = $m['pre'] . ' srcset="' . $set . '" sizes="' . $sizes . '"' . $m['post'];
        }

        if ($cfg['thumb'] > 0 && $item['thumb'] && ($tf = app\filepath($item['thumb'])) && is_file($tf)) {
            $tn = basename($item['thumb']);
            $w[$tf] = $w[$tf] ?? getimagesize($tf)[0] ?? 0;
            $max = min($cfg['thumb'], $w[$tf], $w[$file] - APP['image.threshold']);
            $tset = '';

            foreach ($cfg['srcset'] as $s) {
                if ($s >= $w[$tf]) {
                    break;
                }

                $tset .= ($tset ? ', ' : '') . app\file('resize-' . $s . '/' . $tn) . ' ' . $s . 'w';
            }

            $tset .= ($tset ? ', ' : '') . app\file($tn) . ' ' . $w[$tf] . 'w';
            $source = app\html('source', ['media' => '(max-width: ' . $max . 'px)', 'srcset' => $tset]);
            $img = app\html('picture', [], $source . $img);
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
