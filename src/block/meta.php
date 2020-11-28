<?php
declare(strict_types=1);

namespace block\meta;

use app;
use entity;
use str;

function render(array $block): string
{
    $app = app\data('app');
    $desc = $app['page']['meta_description'] ?? null;
    $title = app\cfg('app', 'title');
    $menutitle = function () use ($app, $title): string {
        $crit = [['id', $app['page']['path']], ['level', 0, APP['op']['>']]];

        foreach (entity\all('page', $crit, select: ['name'], order: ['level' => 'asc']) as $item) {
            $title = $item['name'] . ($title ? ' - ' . $title : '');
        }

        return $title;
    };
    $title = match (true) {
        !empty($app['page']['meta_title']) => $app['page']['meta_title'],
        !!$app['page'] => $menutitle(),
        !!$app['entity'] => $app['entity']['name'] . ($title ? ' - ' . $title : ''),
        default => $title,
    };

    return app\tpl($block['tpl'], ['description' => str\enc($desc), 'title' => str\enc($title)]);
}
