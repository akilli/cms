<?php
declare(strict_types=1);

namespace block;

use app;
use arr;
use attr;
use entity;
use layout;
use request;
use session;
use str;
use DomainException;

/**
 * Container
 */
function container(array $block): string
{
    if (($html = layout\children($block['id'])) && $block['cfg']['tag']) {
        return app\html($block['cfg']['tag'], $block['cfg']['id'] ? ['id' => $block['id']] : [], $html);
    }

    return $html;
}

/**
 * HTML
 */
function html(): string
{
    $app = app\data('app');
    $a = [
        'lang' => APP['lang'],
        'data-parent' => $app['parent_id'],
        'data-entity' => $app['entity_id'],
        'data-action' => $app['action'],
        'data-url' => app\data('request', 'url')
    ];

    return "<!doctype html>\n" . app\html('html', $a, layout\block('head') . layout\block('body'));
}

/**
 * Message
 */
function msg(): string
{
    return app\html('msg');
}

/**
 * Title
 */
function title(array $block): string
{
    $app = app\data('app');
    $text = match (true) {
        !!$block['cfg']['text'] => app\i18n($block['cfg']['text']),
        $app['area'] === '_public_' => $app['page']['title'] ?? $app['page']['name'] ?? '',
        default => $app['entity']['name'] ?? '',
    };

    return $text ? app\html('h1', [], str\enc($text)) : '';
}

/**
 * Template
 */
function tpl(array $block): string
{
    return $block['tpl'] ? app\tpl($block['tpl']) : '';
}

/**
 * Meta
 */
function meta(array $block): string
{
    $app = app\data('app');
    $desc = null;
    $title = app\cfg('app', 'title');

    if ($app['page']) {
        $desc = $app['page']['meta_description'];

        if ($app['page']['meta_title']) {
            $title = $app['page']['meta_title'];
        } else {
            $crit = [['id', $app['page']['path']], ['level', 0, APP['op']['>']]];
            $all = entity\all('page', $crit, select: ['name'], order: ['level' => 'asc']);

            foreach ($all as $item) {
                $title = $item['name'] . ($title ? ' - ' . $title : '');
            }
        }
    } elseif ($app['entity']) {
        $title = $app['entity']['name'] . ($title ? ' - ' . $title : '');
    }

    return app\tpl($block['tpl'], ['description' => str\enc($desc), 'title' => str\enc($title)]);
}

/**
 * View
 */
function view(array $block): string
{
    if (!$block['cfg']['attr_id'] || ($data = $block['cfg']['data']) && empty($data['_entity'])) {
        return '';
    }

    if (!$data && $block['cfg']['entity_id'] && $block['cfg']['id']) {
        $data = entity\one($block['cfg']['entity_id'], [['id', $block['cfg']['id']]]);
    } elseif (!$data && ($app = app\data('app')) && $app['entity_id'] && $app['id']) {
        $data = entity\one($app['entity_id'], [['id', $app['id']]]);
    }

    if (!($entity = $data['_entity'] ?? null) || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    if (in_array('page', [$entity['id'], $entity['parent_id']]) && empty($attrs['title']) && $data['title']) {
        $data['name'] = $data['title'];
    }

    $html = '';

    foreach ($attrs as $attrId => $attr) {
        $html .= attr\viewer($data, $attr, ['wrap' => true]);
    }

    return $html;
}

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

/**
 * Filter
 */
function filter(array $block): string
{
    return $block['cfg']['attr'] || $block['cfg']['search'] ? app\tpl($block['tpl'], $block['cfg']) : '';
}

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

/**
 * Database
 */
function db(array $block): string
{
    if ($block['cfg']['entity_id']
        && $block['cfg']['id']
        && ($data = entity\one($block['cfg']['entity_id'], [['id', $block['cfg']['id']]]))
    ) {
        return layout\render(layout\db($data, ['id' => $block['id']]));
    }

    return '';
}

/**
 * Database Block
 */
function dblock(array $block): string
{
    $data = $block['cfg']['data'];

    if (!$data || !($attrs = arr\extract($data['_entity']['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    $html = '';

    foreach ($attrs as $attr) {
        $cfg = ['wrap' => true] + (in_array($attr['id'], ['file', 'title']) ? ['link' => $data['link']] : []);
        $html .= attr\viewer($data, $attr, $cfg);
    }

    if ($html) {
        return app\html('section', ['class' => str_replace('_', '-', $block['cfg']['data']['entity_id'])], $html);
    }

    return '';
}

/**
 * Edit Form
 */
function edit(array $block): string
{
    $app = app\data('app');
    $old = null;
    $p = [];

    if (!($entity = $app['entity']) || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    if (($id = $app['id']) && !($old = entity\one($entity['id'], [['id', $id]]))) {
        app\msg('Nothing to edit');
        request\redirect(app\url($entity['id'] . '/admin'));
        return '';
    }

    if ($data = app\data('request', 'post')) {
        if ($id) {
            $data = ['id' => $id] + $data;
        }

        if (entity\save($entity['id'], $data)) {
            request\redirect(app\url($entity['id'] . '/edit/' . $data['id']));
            return '';
        }
    }

    if ($id) {
        $p = [$old];
    }

    $p[] = $data;
    $data = arr\replace(entity\item($entity['id']), ...$p);

    return app\tpl(
        $block['tpl'],
        ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]
    );
}

/**
 * Profile Form
 */
function profile(array $block): string
{
    $account = app\data('account');

    if (!$account || !($attrs = arr\extract($account['_entity']['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    $request = app\data('request');

    if ($data = $request['post']) {
        if (!empty($data['password']) && (empty($data['confirmation']) || $data['password'] !== $data['confirmation'])) {
            $data['_error']['password'][] = app\i18n('Password and password confirmation must be identical');
            $data['_error']['confirmation'][] = app\i18n('Password and password confirmation must be identical');
        } else {
            unset($data['confirmation']);
            $data = ['id' => $account['id']] + $data;

            if (entity\save('account', $data)) {
                request\redirect($request['url']);
                return '';
            }
        }
    }

    $data = $data ? arr\replace($account, $data) : $account;

    return app\tpl(
        $block['tpl'],
        ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]
    );
}

/**
 * Login Form
 */
function login(array $block): string
{
    if ($data = app\data('request', 'post')) {
        if (!empty($data['username'])
            && !empty($data['password'])
            && ($account = app\login($data['username'], $data['password']))
        ) {
            session\regenerate();
            session\save('account', $account['id']);
            request\redirect();
            return '';
        }

        app\msg('Invalid name and password combination');
    }

    $entity = app\cfg('entity', 'account');
    $a = [
        'username' => ['unique' => false, 'min' => 0, 'max' => 0],
        'password' => ['min' => 0, 'max' => 0, 'autocomplete' => null],
    ];
    $attrs = arr\extend(arr\extract($entity['attr'], ['username', 'password']), $a);

    return app\tpl($block['tpl'], ['attr' => $attrs, 'data' => [], 'multipart' => false]);
}

/**
 * Navigation
 *
 * @throws DomainException
 */
function nav(array $block): string
{
    if (!$block['cfg']['data']) {
        return '';
    }

    $count = count($block['cfg']['data']);
    $start = current($block['cfg']['data'])['level'] ?? 1;
    $base = ['name' => null, 'url' => null, 'disabled' => false, 'level' => $start];
    $level = 0;
    $i = 0;
    $attrs = ['id' => $block['id']];
    $call = function (array $it): ?string {
        $url = app\data('request', 'url');
        return match (true) {
            $it['url'] === $url => 'active',
            $it['url'] && str_starts_with($url, preg_replace('#\.html#', '', $it['url'])) => 'path',
            default => null,
        };
    };
    $html = $block['cfg']['title'] ? app\html('h2', [], app\i18n($block['cfg']['title'])) : '';
    $html .= layout\children($block['id']);

    if ($block['cfg']['toggle']) {
        $html .= app\html('a', ['data-action' => 'toggle', 'data-target' => $block['id']]);
        $attrs['data-toggle'] = '';
    }

    foreach ($block['cfg']['data'] as $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $item = arr\replace($base, $item);
        $item['level'] = $item['level'] - $start + 1;
        $a = $item['url'] && !$item['disabled'] ? ['href' => $item['url']] : [];
        $c = (array) $call($item);
        $class = '';
        $toggle = '';

        if ($next = next($block['cfg']['data'])) {
            $next = arr\replace($base, $next);
            $next['level'] = $next['level'] - $start + 1;
        }

        if ($next && $item['level'] < $next['level']) {
            if (!$c && $call($next)) {
                $c = ['path'];
            }

            $c[] = 'parent';

            if ($block['cfg']['toggle']) {
                $ta = ['data-action' => 'toggle'];

                if (array_intersect(['active', 'path'], $c)) {
                    $ta['data-toggle'] = '';
                }

                $toggle = app\html('a', $ta);
            }
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
        $html .= $toggle . app\html('a', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return $block['cfg']['tag'] ? app\html($block['cfg']['tag'], $attrs, $html) : $html;
}

/**
 * Menu Navigation
 */
function menu(array $block): string
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

/**
 * Toolbar Navigation
 */
function toolbar(array $block): string
{
    $data = app\cfg('toolbar');
    $empty = [];

    foreach ($data as $id => $item) {
        if (!$item['active'] || $item['priv'] && !app\allowed($item['priv'])) {
            unset($data[$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent_id']) {
            unset($empty[$item['parent_id']]);
        }
    }

    $block['type'] = 'nav';
    $block['cfg'] = ['data' => array_diff_key($data, $empty), 'toggle' => true];

    return layout\render(layout\cfg($block));
}

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
