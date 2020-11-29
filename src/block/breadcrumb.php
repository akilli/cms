<?php
declare(strict_types=1);

namespace block\breadcrumb;

use app;
use entity;

function render(array $block): string
{
    if (!$page = app\data('app', 'page')) {
        return '';
    }

    $html = '';
    $all = entity\all('page', [['id', $page['path']]], select: ['id', 'name', 'url', 'disabled'], order: ['level' => 'asc']);

    foreach ($all as $item) {
        $a = $item['disabled'] || $item['id'] === $page['id'] ? [] : ['href' => $item['url']];
        $html .= ($html ? ' ' : '') . app\html('a', $a, $item['name']);
    }

    return app\html('nav', ['id' => $block['id']], $html);
}
