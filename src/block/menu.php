<?php
declare(strict_types=1);

namespace block\menu;

use app;
use entity;
use layout;

function login(array $block): string
{
    if ($block['cfg']['url']) {
        $page = entity\one('page', [['entity_id', 'page_content'], ['url', $block['cfg']['url']]]);
    } else {
        $page = app\data('app', 'page');
    }

    if ($block['cfg']['submenu'] && empty($page['path'][1])) {
        return '';
    }

    $rootCrit = [['entity_id', 'page_content']];
    $rootCrit[] = $block['cfg']['submenu'] ? ['id', $page['path'][1]] : ['url', '/'];
    $select = ['id', 'name', 'url', 'disabled', 'pos', 'level'];

    if (!$root = entity\one('page', $rootCrit, select: $select)) {
        return '';
    }

    $crit = [['entity_id', 'page_content'], ['pos', $root['pos'] . '.', APP['op']['^']]];

    if ($block['cfg']['submenu']) {
        $parent = $page['path'];
        unset($parent[0]);
        $crit[] = [['id', $page['path']], ['parent_id', $parent]];
    } else {
        $crit[] = ['menu', true];
    }

    $block['cfg']['data'] = entity\all('page', $crit, select: $select, order: ['pos' => 'asc']);
    $block['cfg']['title'] = null;

    if ($block['cfg']['root'] && $block['cfg']['submenu']) {
        $block['cfg']['title'] = $root['name'];
    } elseif ($block['cfg']['root']) {
        $root['level']++;
        $block['cfg']['data'] = [$root['id'] => $root] + $block['cfg']['data'];
    }

    unset($block['cfg']['root'], $block['cfg']['submenu']);
    $block['type'] = 'nav';

    return layout\render(layout\cfg($block));
}
