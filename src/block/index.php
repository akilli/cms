<?php
declare(strict_types=1);

namespace block;

use app;
use arr;
use attr;
use entity;
use layout;
use str;

/**
 * Index
 */
function index(array $block): string
{
    $app = app\data('app');
    $request = app\data('request');
    $entity = $block['cfg']['entity_id'] ? app\cfg('entity', $block['cfg']['entity_id']) : $app['entity'];
    $block['cfg']['limits'] = array_filter($block['cfg']['limits'], fn(mixed $v): bool => is_int($v) && $v >= 0);

    if (!$entity || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id'])) || !$block['cfg']['limits']) {
        return '';
    }

    $crit = $block['cfg']['crit'];
    $order = $block['cfg']['order'];
    $get = arr\replace(['cur' => null, 'filter' => [], 'limit' => null, 'q' => null, 'sort' => null], $request['get']);
    $filter = null;
    $sort = $block['cfg']['sort'] ? null : false;
    $pager = null;
    $limit = $block['cfg']['limits'][0];
    $offset = 0;

    if (is_int($get['limit']) && $get['limit'] >= 0 && in_array($get['limit'], $block['cfg']['limits'])) {
        $limit = $get['limit'];
    }

    if (in_array('page', [$entity['id'], $entity['parent_id']])) {
        if ($app['action'] !== 'admin') {
            $crit[] = ['disabled', false];
        }

        if ($block['cfg']['parent_id']) {
            $crit[] = ['parent_id', $block['cfg']['parent_id'] === -1 ? $app['id'] : $block['cfg']['parent_id']];
        }
    }

    if ($block['cfg']['sort']
        && $get['sort']
        && preg_match('#^(-)?([a-z0-9_\-]+)$#', $get['sort'], $match)
        && !empty($attrs[$match[2]])
    ) {
        $order = [$match[2] => $match[1] ? 'desc' : 'asc'];
        $sort = $get['sort'];
    }

    if ($block['cfg']['filter'] || $block['cfg']['search']) {
        $fa = $block['cfg']['filter'] ? arr\extract($entity['attr'], $block['cfg']['filter']) : [];
        $get['filter'] = $get['filter'] && is_array($get['filter']) ? array_intersect_key($get['filter'], $fa) : [];

        foreach (array_keys($get['filter']) as $attrId) {
            $attr = $fa[$attrId];
            $op = match ($attr['backend']) {
                'json', 'text', 'varchar' => $attr['opt'] ? APP['op']['='] : APP['op']['~'],
                'int[]', 'text[]' => APP['op']['~'],
                'datetime' => $get['filter'][$attrId] ? APP['op']['^'] : APP['op']['='],
                default => APP['op']['='],
            };

            if ($get['filter'][$attrId] && in_array($attr['backend'], ['datetime', 'date', 'time'])) {
                $get['filter'][$attrId] = match ($attr['backend']) {
                    'datetime' => attr\datetime($get['filter'][$attrId], APP['datetime.frontend'], APP['datetime.backend']),
                    'date' => attr\datetime($get['filter'][$attrId], APP['date.frontend'], APP['date.backend']),
                    'time' => attr\datetime($get['filter'][$attrId], APP['time.frontend'], APP['time.backend']),
                };
            }

            $crit[] = [$attrId, $get['filter'][$attrId], $op];
        }

        if ($block['cfg']['search'] && $get['q'] && ($q = array_filter(explode(' ', (string) $get['q'])))) {
            foreach ($q as $v) {
                $call = fn(string $attrId): array => [$attrId, $v, APP['op']['~']];
                $crit[] = array_map($call, $block['cfg']['search']);
            }
        }

        $filter = layout\render(layout\cfg([
            'type' => 'filter',
            'parent_id' => $block['id'],
            'cfg' => [
                'attr' => $fa,
                'data' => arr\replace(entity\item($entity['id']), $get['filter']),
                'q' => $get['q'],
                'search' => !!$block['cfg']['search'],
            ],
        ]));
    }

    if ($block['cfg']['pager']) {
        $size = entity\size($entity['id'], $crit);
        $total = $limit > 0 && ($c = (int) ceil($size / $limit)) ? $c : 1;
        $get['cur'] = min(max((int) $get['cur'], 1), $total);
        $offset = ($get['cur'] - 1) * $limit;
        $pager = layout\render(layout\cfg([
            'type' => 'pager',
            'parent_id' => $block['id'],
            'cfg' => [
                'cur' => $get['cur'],
                'limit' => $limit,
                'limits' => $block['cfg']['limits'],
                'size' => $size,
            ],
        ]));
    }

    return app\tpl($block['tpl'], [
        'attr' => $attrs,
        'data' => entity\all($entity['id'], $crit, order: $order, limit: $limit, offset: $offset),
        'filter' => $filter,
        'link' => $block['cfg']['link'],
        'pager-bottom' => in_array($block['cfg']['pager'], ['both', 'bottom']) ? $pager : null,
        'pager-top' => in_array($block['cfg']['pager'], ['both', 'top']) ? $pager : null,
        'sort' => $sort,
        'title' => $block['cfg']['title'] ? str\enc(app\i18n($block['cfg']['title'])) : null,
    ]);
}
