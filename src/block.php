<?php
declare(strict_types = 1);

namespace block;

use account;
use app;
use arr;
use attr;
use entity;
use layout;
use request;
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
    $attr = [
        'lang' => app\data('lang'),
        'data-action' => app\data('action'),
        'data-entity' => app\data('entity_id'),
        'data-parent' => app\data('parent_id'),
        'data-url' => request\data('url'),
    ];
    $head = layout\block('head');
    $body = layout\block('body');
    $msg = '';

    foreach (app\msg() as $item) {
        $msg .= app\html('p', [], $item);
    }

    $msg = $msg ? app\html('section', ['class' => 'msg'], $msg) : '';
    $body = str_replace(app\html('template', ['id' => 'msg']), $msg, $body);

    return "<!doctype html>\n" . app\html('html', $attr, $head . $body);
}

/**
 * Message
 */
function msg(): string
{
    return app\html('template', ['id' => 'msg']);
}

/**
 * Title
 */
function title(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'title')['cfg'], $block['cfg']);

    if ($cfg['text']) {
        $text = app\i18n($cfg['text']);
    } elseif (app\data('public')) {
        $text = app\data('page')['title'] ?? app\data('page')['name'] ?? '';
    } else {
        $text = app\data('entity')['name'] ?? '';
    }

    return $text ? app\html('h1', [], app\enc($text)) : '';
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
    $desc = app\cfg('app', 'meta.description');
    $title = app\cfg('app', 'meta.title');

    if ($page = app\data('page')) {
        $desc = $page['meta_description'];

        if ($page['meta_title']) {
            $title = $page['meta_title'];
        } else {
            $all = entity\all('page', [['id', $page['path']], ['level', 0, APP['op']['>']]], ['select' => ['name'], 'order' => ['level' => 'asc']]);

            foreach ($all as $item) {
                $title = $item['name'] . ($title ? ' - ' . $title : '');
            }
        }
    } elseif ($entity = app\data('entity')) {
        $title = $entity['name'] . ($title ? ' - ' . $title : '');
    }

    $var = ['description' => app\enc($desc), 'title' => app\enc($title)];

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

    if (!($entity = app\data('entity')) || !($id = app\data('id'))) {
        return '';
    }

    $attrs = arr\extract($entity['attr'], $cfg['attr_id']);
    $data = app\data('page') ?: entity\one($entity['id'], [['id', $id]]);
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

    if (($page = app\data('page')) && $page['entity_id'] !== 'page_content') {
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
    $cfg['entity_id'] = $cfg['entity_id'] ?: app\data('entity_id');
    $entity = app\cfg('entity', $cfg['entity_id']);
    $call = function ($v): bool {
        return is_int($v) && $v >= 0;
    };
    $cfg['limit'] = array_filter(is_array($cfg['limit']) ? $cfg['limit'] : [$cfg['limit']], $call);

    if (!$entity || !($attr = arr\extract($entity['attr'], $cfg['attr_id'])) || !$cfg['limit']) {
        return '';
    }

    $crit = $cfg['crit'];
    $opt = ['order' => $cfg['order']];
    $get = arr\replace(['cur' => null, 'filter' => [], 'limit' => null, 'q' => null, 'sort' => null], request\data('get'));
    $filter = '';
    $sort = $cfg['sort'] ? null : false;
    $pager = null;
    $limit = is_int($get['limit']) && $get['limit'] >= 0 && in_array($get['limit'], $cfg['limit']) ? $get['limit'] : $cfg['limit'][0];

    if ($limit > 0) {
        $opt['limit'] = $limit;
    }

    if (in_array('page', [$entity['id'], $entity['parent_id']])) {
        if (app\data('action') !== 'admin') {
            $crit[] = ['status', 'published'];
            $crit[] = ['disabled', false];
        }

        if ($cfg['parent_id']) {
            $crit[] = ['parent_id', $cfg['parent_id'] === -1 ? app\data('id') : $cfg['parent_id']];
        }
    }

    if ($cfg['sort'] && $get['sort'] && preg_match('#^(-)?([a-z0-9-_]+)$#', $get['sort'], $match) && !empty($attr[$match[2]])) {
        $opt['order'] = [$match[2] => $match[1] ? 'desc' : 'asc'];
        $sort = $get['sort'];
    }

    if ($cfg['filter'] || $cfg['search']) {
        $fa = $cfg['filter'] ? arr\extract($entity['attr'], $cfg['filter']) : [];
        $get['filter'] = $get['filter'] && is_array($get['filter']) ? array_intersect_key($get['filter'], $fa) : [];

        foreach (array_keys($get['filter']) as $attrId) {
            $op = APP['op']['='];

            if ($fa[$attrId]['multiple'] || !$fa[$attrId]['opt'] && in_array($fa[$attrId]['backend'], ['json', 'text', 'varchar'])) {
                $op = APP['op']['~'];
            } elseif ($get['filter'][$attrId] && in_array($fa[$attrId]['backend'], ['datetime', 'date'])) {
                $get['filter'][$attrId] = attr\datetime($get['filter'][$attrId], APP['attr.date.frontend'], APP['attr.date.backend']);
                $op = $fa[$attrId]['backend'] === 'datetime' ? APP['op']['^'] : $op = APP['op']['='];
            } elseif ($get['filter'][$attrId] && $fa[$attrId]['backend'] === 'time') {
                $get['filter'][$attrId] = attr\datetime($get['filter'][$attrId], APP['attr.time.frontend'], APP['attr.time.backend']);
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

        $filter = filter(['cfg' => ['attr' => $fa, 'data' => arr\replace(entity\item($entity['id']), $get['filter']), 'q' => $get['q'], 'search' => !!$cfg['search']]]);
    }

    if ($cfg['pager']) {
        $size = entity\size($entity['id'], $crit);
        $total = $limit > 0 && ($c = (int) ceil($size / $limit)) ? $c : 1;
        $get['cur'] = min(max((int) $get['cur'], 1), $total);
        $opt['offset'] = ($get['cur'] - 1) * $limit;
        $pager = pager(['cfg' => ['cur' => $get['cur'], 'limit' => $limit, 'limits' => $cfg['limit'], 'size' => $size]]);
    }

    if ($entity['id'] === 'version') {
        $ids = array_column(entity\all($entity['id'], $crit, ['select' => ['page_id']] + $opt), 'page_id');
        $data = $ids ? entity\all('page', [['id', $ids]]) : [];
        $entity = app\cfg('entity', 'page');
    } else {
        $data = entity\all($entity['id'], $crit, $opt);
    }

    // Page teaser
    if (in_array('page', [$entity['id'], $entity['parent_id']]) && !empty($attr['content'])) {
        foreach ($data as $id => $item) {
            $data[$id]['content'] = preg_match('#(<p[^>]*>.*?</p>)#', trim($item['content']), $m) ? $m[1] : '';
        }
    }

    $var = [
        'attr' => $attr,
        'content' => $cfg['content'],
        'data' => $data,
        'filter' => $filter,
        'mode' => in_array($cfg['mode'], ['admin', 'browser']) ? $cfg['mode'] : null,
        'pager-bottom' => in_array($cfg['pager'], ['both', 'bottom']) ? $pager : null,
        'pager-top' => in_array($cfg['pager'], ['both', 'top']) ? $pager : null,
        'sort' => $sort,
        'title' => $cfg['title'] ? app\enc(app\i18n($cfg['title'])) : null,
        'url' => request\data('url'),
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

    $url = request\data('url');
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
        return layout\render(arr\replace($block, layout\db($data)));
    }

    return '';
}

/**
 * Content
 */
function content(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'content')['cfg'], $block['cfg']);

    if (!$data = $cfg['data']) {
        return '';
    }

    $attrs = $data['_entity']['attr'];
    $html = '';

    if ($data['title'] && ($val = attr\viewer($data, $attrs['title']))) {
        $val = $data['link'] ? app\html('a', ['href' => $data['link']], $val) : $val;
        $html .= app\html('h2', [], $val);
    }

    if ($data['media'] && ($val = attr\viewer($data, $attrs['media']))) {
        $class = preg_match('#^<(audio|iframe|video)#', $val, $match) ? $match[1] : 'image';
        $val = $data['link'] && $class === 'image' ? app\html('a', ['href' => $data['link']], $val) : $val;
        $html .= app\html('figure', ['class' => $class], $val);
    }

    if ($data['content'] && ($val = attr\viewer($data, $attrs['content']))) {
        $html .= app\html('div', ['class' => 'content'], $val);
    }

    $class = str_replace('_', '-', $cfg['data']['entity_id']);

    return $html ? app\html('section', ['id' => $block['id'], 'class' => $class] + $cfg['attr'], $html) : '';
}

/**
 * Edit Form
 */
function edit(array $block): string
{
    $type = app\cfg('block', 'edit');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);

    if (!($entity = app\data('entity')) || !($attr = arr\extract($entity['attr'], $cfg['attr_id']))) {
        return '';
    }

    $old = null;

    if (($id = app\data('id')) && !($old = entity\one($entity['id'], [['id', $id]]))) {
        app\msg('Nothing to edit');
        request\redirect(app\url($entity['id'] . '/admin'));
        return '';
    }

    if ($data = request\data('post')) {
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
    $var = ['attr' => $attr, 'data' => $data, 'file' => !!arr\filter($attr, 'uploadable', true)];

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

    if (!($account = account\data()) || !($attr = arr\extract($account['_entity']['attr'], $cfg['attr_id']))) {
        return '';
    }

    if ($data = request\data('post')) {
        if (!empty($data['password']) && (empty($data['confirmation']) || $data['password'] !== $data['confirmation'])) {
            $data['_error']['password'][] = app\i18n('Password and password confirmation must be identical');
            $data['_error']['confirmation'][] = app\i18n('Password and password confirmation must be identical');
        } else {
            unset($data['confirmation']);
            $data = ['id' => $account['id']] + $data;

            if (entity\save('account', $data)) {
                request\redirect(request\data('url'));
            }
        }
    }

    $data = $data ? arr\replace($account + ['_error' => []], $data) : $account;
    $var = ['attr' => $attr, 'data' => $data, 'file' => !!arr\filter($attr, 'uploadable', true)];

    return app\tpl($block['tpl'], $var);
}

/**
 * Login Form
 */
function login(array $block): string
{
    $block['tpl'] = $block['tpl'] ?? app\cfg('block', 'login')['tpl'];
    $entity = app\cfg('entity', 'account');
    $a = ['username' => ['unique' => false, 'min' => 0, 'max' => 0], 'password' => ['min' => 0, 'max' => 0]];
    $attr = array_replace_recursive(arr\extract($entity['attr'], ['username', 'password']), $a);
    $var = ['attr' => $attr, 'data' => [], 'file' => false];

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
        if ($it['url'] === request\data('url')) {
            return 'active';
        }

        if ($it['url'] && strpos(request\data('url'), preg_replace('#\.html#', '', $it['url'])) === 0) {
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
        $page = app\data('page');
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

    foreach ($block['cfg']['data'] as $id => $item) {
        $block['cfg']['data'][$id]['name'] = $item['name'];
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
    if (!$page = app\data('page')) {
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
