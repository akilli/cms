<?php
declare(strict_types=1);

namespace block;

use app;
use arr;
use attr;
use entity;
use html;
use layout;
use menu;
use response;
use session;
use str;

function add(array $block): string
{
    if (!($entityId = $block['cfg']['entity_id']) || !app\allowed(app\id($entityId, 'add'))) {
        return '';
    }

    $a = html\element('a', ['href' => app\actionurl($entityId, 'add')], app\i18n('Add Item'));

    return html\element('div', ['class' => 'block-add'], $a);
}

function block(array $block): string
{
    return layout\render_init(['type' => 'view'] + $block);
}

function breadcrumb(array $block): string
{
    $url = app\data('request', 'url');

    if ($url === '/') {
        return '';
    }

    $id = $block['cfg']['id'];
    $call = fn(array $item): array => arr\replace(APP['cfg']['menu'], $item);
    $data = $id ? app\cfg('menu', $id) : array_map($call, entity\all('menu', order: ['position' => 'asc']));

    if (!($data = menu\filter($data)) || !$cur = current(arr\filter($data, 'url', $url))) {
        return '';
    }

    $home = entity\one('page', crit: [['url', '/']], select: ['name', 'url']);
    $html = html\element('a', ['href' => $home['url']], $home['name']);

    foreach ($cur['path'] as $pid) {
        $a = match (true) {
            !$data[$pid]['url'] => [],
            $data[$pid]['url'] === $url => ['href' => $data[$pid]['url'], 'aria-current' => 'page'],
            default => ['href' => $data[$pid]['url']],
        };
        $html .= ' ' . html\element('a', $a, $data[$pid]['name']);
    }

    return html\element('nav', ['id' => $block['id'], 'aria-label' => 'breadcrumb'], $html);
}

function container(array $block): string
{
    if (($html = layout\render_children($block['id'])) && $block['tag']) {
        return html\element($block['tag'], $block['cfg']['id'] ? ['id' => $block['id']] : [], $html);
    }

    return $html;
}

function filter(array $block): string
{
    if (!$block['cfg']['attr'] && !$block['cfg']['searchable']) {
        return '';
    }

    return app\tpl($block['tpl'], [
        'attr' => $block['cfg']['attr'],
        'data' => $block['cfg']['data'],
        'q' => $block['cfg']['q'],
        'searchable' => $block['cfg']['searchable'],
    ]);
}

function form(array $block): string
{
    $app = app\data('app');
    $entity = $app['entity'];
    $crit = [['autoedit', true], ['auto', false]];
    $call = fn(array $src, array $k): array => $k ? arr\extract($src, $k) : arr\all($src, $crit);

    if (!$entity || !($attrs = $call($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    if ($data = app\data('request', 'post')) {
        if ($app['item_id']) {
            $data = ['id' => $app['item_id']] + $data;
        }

        if (entity\save($entity['id'], $data)) {
            $id = $data['id'] ?? $app['item_id'] ?? null;
            response\redirect(app\actionurl($entity['id'], 'edit', $id));
        }
    }

    $args = $app['item'] ? [$app['item'], $data] : [$data];
    $data = arr\replace(entity\item($entity['id']), ...$args);

    return app\tpl(
        $block['tpl'],
        ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]
    );
}

function html(): string
{
    $app = app\data('app');
    $a = [
        'lang' => APP['lang'],
        'data-url' => app\data('request', 'url'),
        'data-entity' => $app['entity_id'],
        'data-action' => $app['action'],
        ...($app['item_id'] ? ['data-id' => $app['item_id']] : []),
        ...($app['parent_id'] ? ['data-parent' => $app['parent_id']] : []),
    ];

    return "<!doctype html>\n" . html\element('html', $a, layout\render_id('head') . layout\render_id('body'));
}

function index(array $block): string
{
    $app = app\data('app');
    $entity = $block['cfg']['entity_id'] ? app\cfg('entity', $block['cfg']['entity_id']) : $app['entity'];
    $call = fn(bool $flag, array $k, string $f): array => match (true) {
        !$flag => [],
        !!$k => arr\extract($entity['attr'], $k),
        default => arr\all($entity['attr'], [[$f, true], ['nullable', false]]),
    };

    if (!$entity || !($attrs = $call(true, $block['cfg']['attr_id'], 'autoindex'))) {
        return '';
    }

    $crit = $block['cfg']['crit'];
    $order = $block['cfg']['order'];
    $limit = $block['cfg']['limit'];
    $get = arr\replace(['cur' => null, 'filter' => [], 'q' => null, 'sort' => null], app\data('request', 'get'));
    $add = null;
    $filter = null;
    $sort = null;
    $pager = null;
    $offset = 0;

    if ($block['cfg']['sortable']
        && $get['sort']
        && preg_match('#^(-)?([a-z][\w]*)$#', $get['sort'], $match)
        && !empty($attrs[$match[2]])
    ) {
        $order = [$match[2] => $match[1] ? 'desc' : 'asc'];
        $sort = $get['sort'];
    }

    if ($block['cfg']['filterable'] || $block['cfg']['searchable']) {
        $sa = array_keys($call($block['cfg']['searchable'], $block['cfg']['search'], 'autosearch'));
        $fa = $call($block['cfg']['filterable'], $block['cfg']['filter'], 'autofilter');
        $get['filter'] = $get['filter'] && is_array($get['filter']) ? array_intersect_key($get['filter'], $fa) : [];

        foreach (array_keys($get['filter']) as $attrId) {
            $op = match ($fa[$attrId]['backend']) {
                'json', 'text', 'varchar' => $fa[$attrId]['opt'] ? APP['op']['='] : APP['op']['~'],
                'multiint', 'multitext' => APP['op']['~'],
                'datetime' => $get['filter'][$attrId] ? APP['op']['^'] : APP['op']['='],
                default => APP['op']['='],
            };
            $crit[] = [$attrId, $get['filter'][$attrId], $op];
        }

        if ($sa && $get['q'] && ($q = array_filter(explode(' ', (string)$get['q'])))) {
            foreach ($q as $v) {
                $crit[] = array_map(fn(string $attrId): array => [$attrId, $v, APP['op']['~']], $sa);
            }
        }

        $filter = layout\render_init([
            'type' => 'filter',
            'parent_id' => $block['id'],
            'cfg' => [
                'attr' => $fa,
                'data' => arr\replace(entity\item($entity['id']), $get['filter']),
                'q' => $get['q'],
                'searchable' => $block['cfg']['searchable'],
            ],
        ]);
    }

    if ($block['cfg']['add']) {
        $add = layout\render_init([
            'type' => 'add',
            'parent_id' => $block['id'],
            'cfg' => ['entity_id' => $entity['id']],
        ]);
    }

    if ($block['cfg']['pager']) {
        $size = entity\size($entity['id'], crit: $crit);
        $total = $limit > 0 && ($p = (int)ceil($size / $limit)) ? $p : 1;
        $get['cur'] = min(max((int)$get['cur'], 1), $total);
        $offset = ($get['cur'] - 1) * $limit;
        $pager = layout\render_init([
            'type' => 'pager',
            'parent_id' => $block['id'],
            'cfg' => ['cur' => $get['cur'], 'limit' => $limit, 'size' => $size],
        ]);
    }

    return app\tpl($block['tpl'], [
        'action' => $block['cfg']['action'],
        'add' => $add,
        'attr' => $attrs,
        'data' => entity\all($entity['id'], crit: $crit, order: $order, limit: $limit, offset: $offset),
        'filter' => $filter,
        'pager' => $pager,
        'sort' => $sort,
        'sortable' => $block['cfg']['sortable'],
        'table' => $block['cfg']['table'],
        'title' => $block['cfg']['title'] ? str\enc(app\i18n($block['cfg']['title'])) : null,
    ]);
}

function login(array $block): string
{
    if ($data = app\data('request', 'post')) {
        if (!empty($data['username'])
            && !empty($data['password'])
            && ($account = app\login($data['username'], $data['password']))
        ) {
            session\regenerate();
            session\save('account', $account['id']);
            response\redirect(app\actionurl('account', 'dashboard'));
        }

        app\msg(app\i18n('Invalid name and password combination'));
    }

    $entity = app\cfg('entity', 'account');
    $a = [
        'username' => ['unique' => false, 'min' => null, 'max' => null],
        'password' => ['min' => null, 'max' => null, 'autocomplete' => null],
    ];
    $attrs = arr\extend(arr\extract($entity['attr'], ['username', 'password']), $a);

    return app\tpl($block['tpl'], ['attr' => $attrs, 'data' => [], 'multipart' => false]);
}

function menu(array $block): string
{
    $menuId = $block['cfg']['id'];
    $call = fn(array $item): array => arr\replace(APP['cfg']['menu'], $item);
    $data = $menuId ? app\cfg('menu', $menuId) : array_map($call, entity\all('menu', order: ['position' => 'asc']));

    if (!$data = menu\filter($data)) {
        return '';
    }

    $lastId = array_key_last($data);
    $url = app\data('request', 'url');
    $cur = current(arr\filter($data, 'url', $url)) ?? null;
    $level = 0;
    $html = layout\render_children($block['id']);

    foreach ($data as $id => $item) {
        $c = $item['url'] === $url;
        $a = $item['url'] ? ['href' => $item['url']] : [];
        $a += $c ? ['aria-current' => 'page'] : [];
        $a += $item['children'] ? ['aria-haspopup' => 'true'] : [];
        $a += !$c && $cur && in_array($id, $cur['path']) ? ['class' => 'current-path'] : [];
        $html .= match ($item['level'] <=> $level) {
            1 => '<ul><li>',
            -1 => '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li>',
            default => '</li><li>',
        };
        $html .= html\element('a', $a, $item['name']);
        $html .= $id === $lastId ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return html\element('nav', ['id' => $block['id']], $html);
}

function meta(array $block): string
{
    $app = app\data('app');
    $desc = $app['item']['meta_description'] ?? null;
    $title = app\cfg('app', 'title') ?? '';
    $title = match (true) {
        $app['action'] !== 'view' => trim(($app['entity']['name'] ?? '') . ' - ' . $title, '- '),
        !empty($app['item']['meta_title']) => $app['item']['meta_title'],
        !empty($app['item']['name']) => $app['item']['name'] . ' - ' . $title,
        default => $title,
    };

    return app\tpl($block['tpl'], ['description' => str\enc($desc), 'title' => str\enc($title)]);
}

function pager(array $block): string
{
    if ($block['cfg']['cur'] < 1 || $block['cfg']['limit'] < 0 || $block['cfg']['size'] <= 0) {
        return '';
    }

    $total = $block['cfg']['limit'] && ($c = (int)ceil($block['cfg']['size'] / $block['cfg']['limit'])) ? $c : 1;
    $block['cfg']['cur'] = min(max($block['cfg']['cur'], 1), $total);
    $offset = ($block['cfg']['cur'] - 1) * $block['cfg']['limit'];
    $up = $block['cfg']['limit'] ? min($offset + $block['cfg']['limit'], $block['cfg']['size']) : $block['cfg']['size'];
    $info = app\i18n('%s to %s of %s', $offset + 1, $up, $block['cfg']['size']);
    $min = max(1, min($block['cfg']['cur'] - intdiv($block['cfg']['pages'], 2), $total - $block['cfg']['pages'] + 1));
    $max = min($min + $block['cfg']['pages'] - 1, $total);
    $links = [];
    $base = APP['pager'];

    if ($block['cfg']['cur'] >= 2) {
        $p = ['cur' => $block['cfg']['cur'] === 2 ? null : $block['cfg']['cur'] - 1];
        $url = app\urlquery($p, true);
        $links[] = arr\replace($base, ['name' => app\i18n('Previous'), 'url' => $url, 'class' => 'prev']);
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $p = ['cur' => $i === 1 ? null : $i];
        $url = app\urlquery($p, true);
        $links[] = arr\replace($base, ['name' => $i, 'url' => $url, 'current' => $i === $block['cfg']['cur']]);
    }

    if ($block['cfg']['cur'] < $total) {
        $url = app\urlquery(['cur' => $block['cfg']['cur'] + 1], true);
        $links[] = arr\replace($base, ['name' => app\i18n('Next'), 'url' => $url, 'class' => 'next']);
    }

    return app\tpl($block['tpl'], ['info' => $info, 'links' => $links]);
}

function profile(array $block): string
{
    $account = app\data('account');

    if (!$account['id']) {
        return '';
    }

    $pId = 'password';
    $cId = 'password-confirmation';

    // Add password-confirmation to form if password is among the configured attributes
    if (($pKey = array_search($pId, $block['cfg']['attr_id'], true)) !== false) {
        $account['_old'][$cId] = $account['_old'][$pId];
        $account['_entity']['attr'][$cId] = array_replace(
            $account['_entity']['attr'][$pId],
            ['id' => $cId, 'name' => app\i18n('Password Confirmation')]
        );
        array_splice($block['cfg']['attr_id'], ++$pKey, 0, $cId);
    }

    if (!$attrs = arr\extract($account['_entity']['attr'], $block['cfg']['attr_id'])) {
        return '';
    }

    $request = app\data('request');

    if ($data = $request['post']) {
        if (!empty($data[$pId]) && (empty($data[$cId]) || $data[$pId] !== $data[$cId])) {
            $message = app\i18n('Password and password confirmation must be identical');
            $data['_error'][$pId][] = $message;
            $data['_error'][$cId][] = $message;
        } else {
            unset($data[$cId]);
            $data = ['id' => $account['id']] + $data;

            if (entity\save('account', $data)) {
                response\redirect($request['url']);
            }
        }
    }

    $data = $data ? arr\replace($account, $data) : $account;

    return app\tpl(
        $block['tpl'],
        ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]
    );
}

function tag(array $block): string
{
    return $block['tag'] ? html\element($block['tag'], $block['cfg']['attr'], $block['cfg']['val']) : '';
}

function title(array $block): string
{
    $app = app\data('app');
    $text = match (true) {
        !!$block['cfg']['text'] => app\i18n($block['cfg']['text']),
        $app['action'] !== 'view' => $app['entity']['name'] ?? '',
        default => $app['item']['title'] ?? $app['item']['name'] ?? '',
    };

    return $text ? html\element('h1', [], str\enc($text)) : '';
}

function tpl(array $block): string
{
    return app\tpl($block['tpl']);
}

function view(array $block): string
{
    $entityId = $block['cfg']['entity_id'];
    $id = $block['cfg']['id'];
    $data = match (true) {
        !!$block['cfg']['data'] => $block['cfg']['data'],
        $entityId && $id => entity\one($entityId, crit: [['id', $id]]),
        !$entityId && !$id => app\data('app', 'item'),
        default => null,
    };
    $entity = $data['_entity'] ?? null;
    $crit = [['autoview', true], ['id', 'name', APP['op']['!=']], ['id', 'title', APP['op']['!=']]];
    $call = fn(array $src, array $k): array => $k ? arr\extract($src, $k) : arr\all($src, $crit);

    if (!$entity || !($attrs = $call($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    $html = '';

    foreach ($attrs as $attr) {
        $html .= attr\viewer($data, $attr, wrap: true);
    }

    return $html && $block['tag'] ? html\element($block['tag'], ['data-entity' => $entity['id']], $html) : $html;
}
