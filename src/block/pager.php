<?php
declare(strict_types=1);

namespace block;

use app;

/**
 * Pager
 */
function pager(array $block): string
{
    if ($block['cfg']['cur'] < 1 || $block['cfg']['limit'] < 0 || $block['cfg']['size'] <= 0) {
        return '';
    }

    $total = $block['cfg']['limit'] && ($c = (int) ceil($block['cfg']['size'] / $block['cfg']['limit'])) ? $c : 1;
    $block['cfg']['cur'] = min(max($block['cfg']['cur'], 1), $total);
    $offset = ($block['cfg']['cur'] - 1) * $block['cfg']['limit'];
    $up = $block['cfg']['limit'] ? min($offset + $block['cfg']['limit'], $block['cfg']['size']) : $block['cfg']['size'];
    $info = app\i18n('%s to %s of %s', (string) ($offset + 1), (string) $up, (string) $block['cfg']['size']);
    $min = max(1, min($block['cfg']['cur'] - intdiv($block['cfg']['pages'], 2), $total - $block['cfg']['pages'] + 1));
    $max = min($min + $block['cfg']['pages'] - 1, $total);
    $limits = [];
    $links = [];

    foreach ($block['cfg']['limits'] as $k => $l) {
        if (is_int($l) && $l >= 0) {
            $limits[] = [
                'name' => $l ?: app\i18n('All'),
                'url' => app\query(['cur' => null, 'limit' => $k === 0 ? null : $l], true),
                'active' => $l === $block['cfg']['limit'],
            ];
        }
    }

    if ($block['cfg']['cur'] >= 2) {
        $p = ['cur' => $block['cfg']['cur'] === 2 ? null : $block['cfg']['cur'] - 1];
        $links[] = [
            'name' => app\i18n('Previous'),
            'url' => app\query($p, true),
            'class' => 'prev',
        ];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $p = ['cur' => $i === 1 ? null : $i];
        $links[] = [
            'name' => $i,
            'url' => app\query($p, true),
            'active' => $i === $block['cfg']['cur'],
            'class' => null,
        ];
    }

    if ($block['cfg']['cur'] < $total) {
        $links[] = [
            'name' => app\i18n('Next'),
            'url' => app\query(['cur' => $block['cfg']['cur'] + 1], true),
            'class' => 'next',
        ];
    }

    return app\tpl($block['tpl'], ['info' => $info, 'limits' => count($limits) > 1 ? $limits : [], 'links' => $links]);
}
