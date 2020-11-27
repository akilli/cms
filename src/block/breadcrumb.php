<?php
declare(strict_types=1);

namespace block;

use app;
use entity;

/**
 * Breadcrumb Navigation
 */
function breadcrumb(array $block): string
{
    if (!$page = app\data('app', 'page')) {
        return '';
    }

    $html = '';
    $crit = [['entity_id', 'page_content'], ['id', $page['path']]];
    $all = entity\all('page', $crit, select: ['id', 'name', 'url', 'disabled'], order: ['level' => 'asc']);

    foreach ($all as $item) {
        $a = $item['disabled'] || $item['id'] === $page['id'] ? [] : ['href' => $item['url']];
        $html .= ($html ? ' ' : '') . app\html('a', $a, $item['name']);
    }

    return app\html('nav', ['id' => $block['id']], $html);
}
