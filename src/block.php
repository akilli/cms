<?php
declare(strict_types=1);

namespace block;

use app;
use arr;
use attr;
use DomainException;
use entity;
use html;
use layout;
use response;
use session;
use str;

function block(array $block): string
{
    return layout\render(layout\block(['type' => 'view'] + $block));
}

function breadcrumb(array $block): string
{
    $url = app\data('request', 'url');

    if ($url === '/' || !($cur = entity\one('menu', crit: [['url', $url]], select: ['path']))) {
        return '';
    }

    $home = entity\one('page', crit: [['url', '/']], select: ['name', 'url']);
    $html = html\element('a', ['href' => $home['url']], $home['name']);
    $all = entity\all('menu', crit: [['id', $cur['path']]], select: ['name', 'url'], order: ['level' => 'asc']);

    foreach ($all as $item) {
        $a = match (true) {
            !$item['url'] => [],
            $item['url'] === $url => ['href' => $item['url'], 'aria-current' => 'page'],
            default => ['href' => $item['url']],
        };
        $html .= ' ' . html\element('a', $a, $item['name']);
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

function edit(array $block): string
{
    $app = app\data('app');
    $entity = $app['entity'];

    if (!$entity || !$app['action'] || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    if ($data = app\data('request', 'post')) {
        if ($app['id']) {
            $data = ['id' => $app['id']] + $data;
        }

        if (entity\save($entity['id'], $data)) {
            response\redirect(app\actionurl($entity['id'], $app['action'], $data['id']));
        }
    }

    $args = $app['id'] ? [entity\one($entity['id'], crit: [['id', $app['id']]]), $data] : [$data];
    $data = arr\replace(entity\item($entity['id']), ...$args);

    return app\tpl(
        $block['tpl'],
        ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]
    );
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

function html(): string
{
    $app = app\data('app');
    $a = [
        'lang' => APP['lang'],
        'data-area' => $app['area'],
        'data-parent' => $app['parent_id'],
        'data-entity' => $app['entity_id'],
        'data-action' => $app['action'],
        'data-url' => app\data('request', 'url'),
    ];

    return "<!doctype html>\n" . html\element('html', $a, layout\render_id('head') . layout\render_id('body'));
}

function index(array $block): string
{
    $app = app\data('app');
    $entity = $block['cfg']['entity_id'] ? app\cfg('entity', $block['cfg']['entity_id']) : $app['entity'];

    if (!$entity || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    $crit = $block['cfg']['crit'];
    $order = $block['cfg']['order'];
    $limit = $block['cfg']['limit'];
    $get = arr\replace(['cur' => null, 'filter' => [], 'q' => null, 'sort' => null], app\data('request', 'get'));
    $filter = null;
    $sort = null;
    $pager = null;
    $offset = 0;

    if ($block['cfg']['sortable']
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
                'multiint', 'multitext' => APP['op']['~'],
                'datetime' => $get['filter'][$attrId] ? APP['op']['^'] : APP['op']['='],
                default => APP['op']['='],
            };
            $crit[] = [$attrId, $get['filter'][$attrId], $op];
        }

        if ($block['cfg']['search'] && $get['q'] && ($q = array_filter(explode(' ', (string)$get['q'])))) {
            foreach ($q as $v) {
                $call = fn(string $attrId): array => [$attrId, $v, APP['op']['~']];
                $crit[] = array_map($call, $block['cfg']['search']);
            }
        }

        $filter = layout\render(layout\block([
            'type' => 'filter',
            'parent_id' => $block['id'],
            'cfg' => [
                'attr' => $fa,
                'data' => arr\replace(entity\item($entity['id']), $get['filter']),
                'q' => $get['q'],
                'searchable' => !!$block['cfg']['search'],
            ],
        ]));
    }

    if ($block['cfg']['pager']) {
        $size = entity\size($entity['id'], crit: $crit);
        $total = $limit > 0 && ($c = (int)ceil($size / $limit)) ? $c : 1;
        $get['cur'] = min(max((int)$get['cur'], 1), $total);
        $offset = ($get['cur'] - 1) * $limit;
        $pager = layout\render(layout\block([
            'type' => 'pager',
            'parent_id' => $block['id'],
            'cfg' => ['cur' => $get['cur'], 'limit' => $limit, 'size' => $size],
        ]));
    }

    return app\tpl($block['tpl'], [
        'action' => $block['cfg']['action'],
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
            response\redirect();
        }

        app\msg(app\i18n('Invalid name and password combination'));
    }

    $entity = app\cfg('entity', 'account');
    $a = [
        'username' => ['unique' => false, 'min' => 0, 'max' => 0],
        'password' => ['min' => 0, 'max' => 0, 'autocomplete' => null],
    ];
    $attrs = arr\extend(arr\extract($entity['attr'], ['username', 'password']), $a);

    return app\tpl($block['tpl'], ['attr' => $attrs, 'data' => [], 'multipart' => false]);
}

function menu(array $block): string
{
    $select = ['id', 'name', 'url', 'position', 'level'];
    $order = ['position' => 'asc'];
    $block['cfg'] = ['data' => entity\all('menu', select: $select, order: $order)];
    $block['type'] = 'nav';

    return layout\render(layout\block($block));
}

function meta(array $block): string
{
    $app = app\data('app');
    $desc = $app['item']['meta_description'] ?? null;
    $title = app\cfg('app', 'title') ?? '';
    $title = match (true) {
        $app['area'] === '_admin_' => trim(($app['entity']['name'] ?? '') . ' - ' . $title, '- '),
        !empty($app['item']['meta_title']) => $app['item']['meta_title'],
        !empty($app['item']['name']) => $app['item']['name'] . ' - ' . $title,
        default => $title,
    };

    return app\tpl($block['tpl'], ['description' => str\enc($desc), 'title' => str\enc($title)]);
}

/**
 * @throws DomainException
 */
function nav(array $block): string
{
    if (!$block['cfg']['data']) {
        return '';
    }

    $count = count($block['cfg']['data']);
    $start = current($block['cfg']['data'])['level'] ?? 1;
    $base = ['name' => null, 'url' => null, 'level' => $start];
    $level = 0;
    $i = 0;
    $url = app\data('request', 'url');
    $call = fn(array $it): bool => $it['url'] === $url;
    $html = $block['cfg']['title'] ? html\element('h2', [], app\i18n($block['cfg']['title'])) : '';
    $html .= layout\render_children($block['id']);

    foreach ($block['cfg']['data'] as $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $item = arr\replace($base, $item);
        $item['level'] = $item['level'] - $start + 1;
        $c = [];
        $class = '';
        $a = [];

        if ($item['url']) {
            $a = ['href' => $item['url']] + ($call($item) ? ['aria-current' => 'page'] : []);
        }

        if ($next = next($block['cfg']['data'])) {
            $next = arr\replace($base, $next);
            $next['level'] = $next['level'] - $start + 1;
        }

        if ($next && $item['level'] < $next['level']) {
            if ($call($next)) {
                $c[] = 'path';
            }

            $c[] = 'parent';
        }

        if ($c) {
            $a['class'] = implode(' ', $c);
            $class = ' class="' . $a['class'] . '"';
        }

        $html .= match ($item['level'] <=> $level) {
            1 => '<ul><li' . $class . '>',
            -1 => '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>',
            default => '</li><li' . $class . '>',
        };
        $html .= html\element('a', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return html\element('nav', ['id' => $block['id']], $html);
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
    $info = app\i18n('%s to %s of %s', (string)($offset + 1), (string)$up, (string)$block['cfg']['size']);
    $min = max(1, min($block['cfg']['cur'] - intdiv($block['cfg']['pages'], 2), $total - $block['cfg']['pages'] + 1));
    $max = min($min + $block['cfg']['pages'] - 1, $total);
    $links = [];
    $base = ['name' => null, 'url' => null, 'current' => false, 'class' => null];

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
    if (!$account = app\data('account')) {
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
        $app['area'] === '_admin_' => $app['entity']['name'] ?? '',
        default => $app['item']['title'] ?? $app['item']['name'] ?? '',
    };

    return $text ? html\element('h1', [], str\enc($text)) : '';
}

function toolbar(array $block): string
{
    $data = app\cfg('toolbar');
    $empty = [];

    foreach ($data as $id => $item) {
        if (!$item['active'] || $item['privilege'] && !app\allowed($item['privilege'])) {
            unset($data[$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent_id']) {
            unset($empty[$item['parent_id']]);
        }
    }

    $block['type'] = 'nav';
    $block['cfg'] = ['data' => array_diff_key($data, $empty)];

    return layout\render(layout\block($block));
}

function tpl(array $block): string
{
    return app\tpl($block['tpl']);
}

function view(array $block): string
{
    if (!$block['cfg']['attr_id'] || ($data = $block['cfg']['data']) && empty($data['_entity'])) {
        return '';
    }

    if (!$data && $block['cfg']['entity_id'] && $block['cfg']['id']) {
        $data = entity\one($block['cfg']['entity_id'], crit: [['id', $block['cfg']['id']]]);
    } elseif (!$data && ($app = app\data('app')) && $app['entity_id'] && $app['id']) {
        $data = entity\one($app['entity_id'], crit: [['id', $app['id']]]);
    }

    if (!($entity = $data['_entity'] ?? null) || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    $html = '';

    foreach ($attrs as $attr) {
        $html .= attr\viewer($data, $attr, ['wrap' => true]);
    }

    return $html && $block['tag'] ? html\element($block['tag'], ['data-entity' => $entity['id']], $html) : $html;
}
