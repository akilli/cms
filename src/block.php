<?php
declare(strict_types = 1);

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
    $cfg = arr\replace(app\cfg('block', 'container')['cfg'], $block['cfg']);
    $html = layout\children($block['id']);
    $attrs = $block['parent_id'] === 'root' ? [] : ['id' => $block['id']];

    return $html && $cfg['tag'] ? app\html($cfg['tag'], $attrs, $html) : $html;
}

/**
 * Root Container
 */
function root(): string
{
    $app = app\data('app');
    $attr = [
        'lang' => APP['lang'],
        'data-action' => $app['action'],
        'data-entity' => $app['entity_id'],
        'data-parent' => $app['parent_id'],
        'data-url' => app\data('request', 'url'),
    ];

    return "<!doctype html>\n" . app\html('html', $attr, layout\block('head') . layout\block('body'));
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
    $cfg = arr\replace(app\cfg('block', 'title')['cfg'], $block['cfg']);
    $app = app\data('app');

    if ($cfg['text']) {
        $text = app\i18n($cfg['text']);
    } elseif ($app['public']) {
        $text = $app['page']['title'] ?? $app['page']['name'] ?? '';
    } else {
        $text = $app['entity']['name'] ?? '';
    }

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
    $block['tpl'] = $block['tpl'] ?: app\cfg('block', 'meta')['tpl'];
    $app = app\data('app');
    $desc = null;
    $title = app\cfg('app', 'title');

    if ($app['page']) {
        $desc = $app['page']['meta_description'];

        if ($app['page']['meta_title']) {
            $title = $app['page']['meta_title'];
        } else {
            $all = entity\all('page', [['id', $app['page']['path']], ['level', 0, APP['op']['>']]], ['select' => ['name'], 'order' => ['level' => 'asc']]);

            foreach ($all as $item) {
                $title = $item['name'] . ($title ? ' - ' . $title : '');
            }
        }
    } elseif ($app['entity']) {
        $title = $app['entity']['name'] . ($title ? ' - ' . $title : '');
    }

    $var = ['description' => str\enc($desc), 'title' => str\enc($title)];

    return app\tpl($block['tpl'], $var);
}

/**
 * View
 */
function view(array $block): string
{
    $type = app\cfg('block', 'view');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);
    $app = app\data('app');

    if (!($entity = $app['entity']) || !($id = $app['id'])) {
        return '';
    }

    $attrs = arr\extract($entity['attr'], $cfg['attr_id']);
    $data = $app['page'] ?: entity\one($entity['id'], [['id', $id]]);
    $data['name'] = empty($attrs['title']) && $data['title'] ? $data['title'] : $data['name'];
    $var = ['attr' => $attrs, 'data' => $data];

    return $var['attr'] && $var['data'] ? app\tpl($block['tpl'], $var) : '';
}

/**
 * Page Banner
 */
function banner(array $block): string
{
    $block['tpl'] = $block['tpl'] ?: app\cfg('block', 'banner')['tpl'];

    if (($page = app\data('app', 'page')) && $page['entity_id'] !== 'page_content') {
        $page = entity\one('page', [['id', $page['path']], ['entity_id', 'page_content']], ['select' => ['image'], 'order' => ['level' => 'desc']]);
    }

    if ($page && ($img = attr\viewer($page, $page['_entity']['attr']['image']))) {
        return app\tpl($block['tpl'], ['img' => $img]);
    }

    return '';
}

/**
 * Index
 */
function index(array $block): string
{
    $type = app\cfg('block', 'index');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);
    $app = app\data('app');
    $request = app\data('request');
    $cfg['entity_id'] = $cfg['entity_id'] ?: $app['entity_id'];
    $entity = app\cfg('entity', $cfg['entity_id']);
    $call = function ($v): bool {
        return is_int($v) && $v >= 0;
    };
    $cfg['limit'] = array_filter(is_array($cfg['limit']) ? $cfg['limit'] : [$cfg['limit']], $call);

    if (!$entity || !($attrs = arr\extract($entity['attr'], $cfg['attr_id'])) || !$cfg['limit']) {
        return '';
    }

    $crit = $cfg['crit'];
    $opt = ['order' => $cfg['order']];
    $get = arr\replace(['cur' => null, 'filter' => [], 'limit' => null, 'q' => null, 'sort' => null], $request['get']);
    $filter = '';
    $sort = $cfg['sort'] ? null : false;
    $pager = null;
    $limit = is_int($get['limit']) && $get['limit'] >= 0 && in_array($get['limit'], $cfg['limit']) ? $get['limit'] : $cfg['limit'][0];

    if ($limit > 0) {
        $opt['limit'] = $limit;
    }

    if (in_array('page', [$entity['id'], $entity['parent_id']])) {
        if ($app['action'] !== 'admin') {
            $crit[] = ['status', 'published'];
            $crit[] = ['disabled', false];
        }

        if ($cfg['parent_id']) {
            $crit[] = ['parent_id', $cfg['parent_id'] === -1 ? $app['id'] : $cfg['parent_id']];
        }
    }

    if ($cfg['sort'] && $get['sort'] && preg_match('#^(-)?([a-z0-9-_]+)$#', $get['sort'], $match) && !empty($attrs[$match[2]])) {
        $opt['order'] = [$match[2] => $match[1] ? 'desc' : 'asc'];
        $sort = $get['sort'];
    }

    if ($cfg['filter'] || $cfg['search']) {
        $fa = $cfg['filter'] ? arr\extract($entity['attr'], $cfg['filter']) : [];
        $get['filter'] = $get['filter'] && is_array($get['filter']) ? array_intersect_key($get['filter'], $fa) : [];

        foreach (array_keys($get['filter']) as $attrId) {
            $attr = $fa[$attrId];
            $op = APP['op']['='];

            if ($attr['multiple'] || !$attr['opt'] && in_array($attr['backend'], ['json', 'text', 'varchar'])) {
                $op = APP['op']['~'];
            } elseif ($get['filter'][$attrId] && in_array($attr['backend'], ['datetime', 'date'])) {
                $get['filter'][$attrId] = attr\datetime($get['filter'][$attrId], $attr['cfg.frontend'], $attr['cfg.backend']);
                $op = $attr['backend'] === 'datetime' ? APP['op']['^'] : $op = APP['op']['='];
            } elseif ($get['filter'][$attrId] && $attr['backend'] === 'time') {
                $get['filter'][$attrId] = attr\datetime($get['filter'][$attrId], $attr['cfg.frontend'], $attr['cfg.backend']);
            }

            $crit[] = [$attrId, $get['filter'][$attrId], $op];
        }

        if ($cfg['search'] && $get['q'] && ($q = array_filter(explode(' ', (string) $get['q'])))) {
            foreach ($q as $v) {
                $call = function ($attrId) use ($v): array {
                    return [$attrId, $v, APP['op']['~']];
                };
                $crit[] = array_map($call, $cfg['search']);
            }
        }

        $filter = layout\render(layout\cfg([
            'type' => 'filter',
            'parent_id' => $block['id'],
            'cfg' => [
                'attr' => $fa,
                'data' => arr\replace(entity\item($entity['id']), $get['filter']),
                'q' => $get['q'],
                'search' => !!$cfg['search'],
            ],
        ]));
    }

    if ($cfg['pager']) {
        $size = entity\size($entity['id'], $crit);
        $total = $limit > 0 && ($c = (int) ceil($size / $limit)) ? $c : 1;
        $get['cur'] = min(max((int) $get['cur'], 1), $total);
        $opt['offset'] = ($get['cur'] - 1) * $limit;
        $pager = layout\render(layout\cfg([
            'type' => 'pager',
            'parent_id' => $block['id'],
            'cfg' => [
                'cur' => $get['cur'],
                'limit' => $limit,
                'limits' => $cfg['limit'],
                'size' => $size,
            ],
        ]));
    }

    if ($entity['id'] === 'version') {
        $ids = array_column(entity\all($entity['id'], $crit, ['select' => ['page_id']] + $opt), 'page_id');
        $data = $ids ? entity\all('page', [['id', $ids]]) : [];
    } else {
        $data = entity\all($entity['id'], $crit, $opt);
    }

    $var = [
        'attr' => $attrs,
        'data' => $data,
        'filter' => $filter,
        'link' => $cfg['link'],
        'mode' => in_array($cfg['mode'], ['admin', 'browser']) ? $cfg['mode'] : null,
        'pager-bottom' => in_array($cfg['pager'], ['both', 'bottom']) ? $pager : null,
        'pager-top' => in_array($cfg['pager'], ['both', 'top']) ? $pager : null,
        'sort' => $sort,
        'title' => $cfg['title'] ? str\enc(app\i18n($cfg['title'])) : null,
        'url' => $request['url'],
    ];

    return app\tpl($block['tpl'], $var);
}

/**
 * Filter
 */
function filter(array $block): string
{
    $type = app\cfg('block', 'filter');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);

    if (!$cfg['attr'] && !$cfg['search']) {
        return '';
    }

    return app\tpl($block['tpl'], $cfg);
}

/**
 * Pager
 */
function pager(array $block): string
{
    $type = app\cfg('block', 'pager');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);

    if ($cfg['cur'] < 1 || $cfg['limit'] < 0 || $cfg['size'] <= 0) {
        return '';
    }

    $url = app\data('request', 'url');
    $total = $cfg['limit'] && ($c = (int) ceil($cfg['size'] / $cfg['limit'])) ? $c : 1;
    $cfg['cur'] = min(max($cfg['cur'], 1), $total);
    $offset = ($cfg['cur'] - 1) * $cfg['limit'];
    $low = $offset + 1;
    $up = $cfg['limit'] ? min($offset + $cfg['limit'], $cfg['size']) : $cfg['size'];
    $min = max(1, min($cfg['cur'] - intdiv($cfg['pages'], 2), $total - $cfg['pages'] + 1));
    $max = min($min + $cfg['pages'] - 1, $total);
    $limits = [];
    $links = [];

    foreach ($cfg['limits'] as $k => $l) {
        if (is_int($l) && $l >= 0) {
            $limits[] = [
                'name' => $l ?: app\i18n('All'),
                'url' => app\url($url, ['cur' => null, 'limit' => $k === 0 ? null : $l], true),
                'active' => $l === $cfg['limit']
            ];
        }
    }

    if ($cfg['cur'] >= 2) {
        $p = ['cur' => $cfg['cur'] === 2 ? null : $cfg['cur'] - 1];
        $links[] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $p, true), 'class' => 'prev'];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $p = ['cur' => $i === 1 ? null : $i];
        $links[] = ['name' => $i, 'url' => app\url($url, $p, true), 'active' => $i === $cfg['cur'], 'class' => null];
    }

    if ($cfg['cur'] < $total) {
        $links[] = ['name' => app\i18n('Next'), 'url' => app\url($url, ['cur' => $cfg['cur'] + 1], true), 'class' => 'next'];
    }

    $var = [
        'info' => app\i18n('%s to %s of %s', (string) $low, (string) $up, (string) $cfg['size']),
        'limits' => count($limits) > 1 ? $limits : [],
        'links' => $links
    ];

    return app\tpl($block['tpl'], $var);
}

/**
 * Database
 */
function db(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'db')['cfg'], $block['cfg']);

    if ($cfg['entity_id'] && $cfg['id'] && ($data = entity\one($cfg['entity_id'], [['id', $cfg['id']]]))) {
        return layout\render(layout\db($data, ['id' => $block['id']]));
    }

    return '';
}

/**
 * Content
 */
function content(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'content')['cfg'], $block['cfg']);

    if (!($data = $cfg['data']) || !($attrs = arr\extract($data['_entity']['attr'], $cfg['attr_id']))) {
        return '';
    }

    $out = '';

    foreach ($attrs as $attr) {
        if (!$html = attr\viewer($data, $attr)) {
            continue;
        } elseif ($attr['id'] === 'title') {
            $html = $data['link'] ? app\html('a', ['href' => $data['link']], $html) : $html;
            $out .= app\html('h2', [], $html);
        } elseif ($attr['id'] === 'media') {
            $class = preg_match('#^<(audio|iframe|video)#', $html, $match) ? $match[1] : 'image';
            $html = $data['link'] && $class === 'image' ? app\html('a', ['href' => $data['link']], $html) : $html;
            $out .= app\html('figure', ['class' => $class], $html);
        } else {
            $out .= app\html('div', ['class' => $attr['id']], $html);
        }
    }

    $class = str_replace('_', '-', $cfg['data']['entity_id']);

    return $out ? app\html('section', ['id' => $block['id'], 'class' => $class], $out) : '';
}

/**
 * Edit Form
 */
function edit(array $block): string
{
    $type = app\cfg('block', 'edit');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);
    $app = app\data('app');

    if (!($entity = $app['entity']) || !($attrs = arr\extract($entity['attr'], $cfg['attr_id']))) {
        return '';
    }

    $old = null;

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

    $p = [];

    if ($id) {
        $p = [$old];

        if (in_array('page', [$entity['id'], $entity['parent_id']])) {
            $v = entity\one('version', [['page_id', $id]], ['select' => APP['version'], 'order' => ['timestamp' => 'desc']]);
            unset($v['_old'], $v['_entity']);
            $p[] = $v;
        }
    }

    $p[] = $data;
    $data = arr\replace(entity\item($entity['id']), ...$p);
    $var = ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)];

    return app\tpl($block['tpl'], $var);
}

/**
 * Profile Form
 */
function profile(array $block): string
{
    $type = app\cfg('block', 'profile');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);
    $request = app\data('request');

    if (!($account = app\data('account')) || !($attrs = arr\extract($account['_entity']['attr'], $cfg['attr_id']))) {
        return '';
    }

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

    $data = $data ? arr\replace($account + ['_error' => []], $data) : $account;
    $var = ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)];

    return app\tpl($block['tpl'], $var);
}

/**
 * Login Form
 */
function login(array $block): string
{
    if ($data = app\data('request', 'post')) {
        if (!empty($data['username']) && !empty($data['password']) && ($data = app\login($data['username'], $data['password']))) {
            session\regenerate();
            session\set('account', $data['id']);
            request\redirect(app\url('account/dashboard'));
            return '';
        }

        app\msg('Invalid name and password combination');
    }

    $block['tpl'] = $block['tpl'] ?? app\cfg('block', 'login')['tpl'];
    $entity = app\cfg('entity', 'account');
    $a = ['username' => ['unique' => false, 'min' => 0, 'max' => 0], 'password' => ['min' => 0, 'max' => 0]];
    $attrs = arr\extend(arr\extract($entity['attr'], ['username', 'password']), $a);
    $var = ['attr' => $attrs, 'data' => [], 'multipart' => false];

    return app\tpl($block['tpl'], $var);
}

/**
 * Navigation
 *
 * @throws DomainException
 */
function nav(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'nav')['cfg'], $block['cfg']);

    if (!$cfg['data']) {
        return '';
    }

    $count = count($cfg['data']);
    $start = current($cfg['data'])['level'] ?? 1;
    $base = ['name' => null, 'url' => null, 'disabled' => false, 'level' => $start];
    $level = 0;
    $i = 0;
    $attrs = ['id' => $block['id']];
    $call = function (array $it): ?string {
        $url = app\data('request', 'url');

        if ($it['url'] === $url) {
            return 'active';
        }

        if ($it['url'] && strpos($url, preg_replace('#\.html#', '', $it['url'])) === 0) {
            return 'path';
        }

        return null;
    };
    $html = ($cfg['title'] ? app\html('h2', [], app\i18n($cfg['title'])) : '') . layout\children($block['id']);

    if ($cfg['toggle']) {
        $html .= app\html('a', ['data-action' => 'toggle', 'data-target' => $block['id']]);
        $attrs['data-toggle'] = '';
    }

    foreach ($cfg['data'] as $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $item = arr\replace($base, $item);
        $item['level'] = $item['level'] - $start + 1;
        $a = $item['url'] && !$item['disabled'] ? ['href' => $item['url']] : [];
        $c = (array) $call($item);
        $class = '';
        $toggle = '';

        if ($next = next($cfg['data'])) {
            $next = arr\replace($base, $next);
            $next['level'] = $next['level'] - $start + 1;
        }

        if ($next && $item['level'] < $next['level']) {
            if (!$c && $call($next)) {
                $c = ['path'];
            }

            $c[] = 'parent';

            if ($cfg['toggle']) {
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

        if ($item['level'] > $level) {
            $html .= '<ul><li' . $class . '>';
        } elseif ($item['level'] < $level) {
            $html .= '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>';
        } else {
            $html .= '</li><li' . $class . '>';
        }

        $html .= $toggle . app\html('a', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return $cfg['tag'] ? app\html($cfg['tag'], $attrs, $html) : $html;
}

/**
 * Menu Navigation
 */
function menu(array $block): string
{
    $block['cfg'] = arr\replace(app\cfg('block', 'menu')['cfg'], $block['cfg']);

    if ($block['cfg']['url']) {
        $page = entity\one('page', [['status', 'published'], ['entity_id', 'page_content'], ['url', $block['cfg']['url']]]);
    } else {
        $page = app\data('app', 'page');
    }

    if ($block['cfg']['submenu'] && empty($page['path'][1])) {
        return '';
    }

    $rootCrit = [['status', 'published'], ['entity_id', 'page_content']];
    $rootCrit[] = $block['cfg']['submenu'] ? ['id', $page['path'][1]] : ['url', '/'];
    $select = ['id', 'name', 'url', 'disabled', 'pos', 'level'];
    $opt = ['select' => $select, 'order' => ['pos' => 'asc']];

    if (!$root = entity\one('page', $rootCrit, ['select' => $select])) {
        return '';
    }

    $crit = [['status', 'published'], ['entity_id', 'page_content'], ['pos', $root['pos'] . '.', APP['op']['^']]];

    if ($block['cfg']['submenu']) {
        $parent = $page['path'];
        unset($parent[0]);
        $crit[] = [['id', $page['path']], ['parent_id', $parent]];
    } else {
        $crit[] = ['menu', true];
    }

    $block['cfg']['data'] = entity\all('page', $crit, $opt);
    $block['cfg']['title'] = null;

    if ($block['cfg']['root'] && $block['cfg']['submenu']) {
        $block['cfg']['title'] = $root['name'];
    } elseif ($block['cfg']['root']) {
        $root['level']++;
        $block['cfg']['data'] = [$root['id'] => $root] + $block['cfg']['data'];
    }

    unset($block['cfg']['root'], $block['cfg']['submenu']);

    return nav($block);
}

/**
 * Toolbar Navigation
 */
function toolbar(array $block): string
{
    $data = app\cfg('toolbar');
    $empty = [];

    foreach ($data as $id => $item) {
        if (!$item['active'] || $item['action'] && !app\allowed($item['action'])) {
            unset($data[$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent_id']) {
            unset($empty[$item['parent_id']]);
        }
    }

    $block['cfg'] = ['data' => array_diff_key($data, $empty), 'toggle' => true];

    return nav($block);
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
    $crit = [['status', 'published'], ['entity_id', 'page_content'], ['id', $page['path']]];
    $all = entity\all('page', $crit, ['select' => ['id', 'name', 'url', 'disabled'], 'order' => ['level' => 'asc']]);

    foreach ($all as $item) {
        $a = $item['disabled'] || $item['id'] === $page['id'] ? [] : ['href' => $item['url']];
        $html .= ($html ? ' ' : '') . app\html('a', $a, $item['name']);
    }

    return app\html('nav', ['id' => $block['id']], $html);
}
