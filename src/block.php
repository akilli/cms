<?php
declare(strict_types = 1);

namespace block;

use account;
use app;
use arr;
use attr;
use entity;
use request;
use DomainException;

/**
 * Container
 */
function container(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'container')['cfg'], $block['cfg']);
    $attrs = $block['parent_id'] === 'root' ? [] : ['id' => $block['id']];
    $html = '';

    foreach (arr\order(arr\crit(app\layout(), [['parent_id', $block['id']]]), ['sort' => 'asc']) as $child) {
        $html .= app\block($child['id']);
    }

    return $html && $cfg['tag'] ? app\html($cfg['tag'], $attrs, $html) : $html;
}

/**
 * Root Container
 */
function root(): string
{
    $attr = [
        'lang' => app\get('lang'),
        'data-action' => app\get('action'),
        'data-entity' => app\get('entity_id'),
        'data-parent' => app\get('parent_id'),
        'data-url' => request\get('url'),
    ];
    $head = app\block('head');
    $body = app\block('body');
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
 * Content
 */
function content(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'content')['cfg'], $block['cfg']);

    return $cfg['content'] ? app\html('section', ['id' => $block['id'], 'class' => 'block-content'], $cfg['content']) : '';
}

/**
 * Template
 */
function tpl(array $block): string
{
    return $block['tpl'] ? app\render($block['tpl']) : '';
}

/**
 * Meta
 */
function meta(array $block): string
{
    $block['tpl'] = $block['tpl'] ?: app\cfg('block', 'meta')['tpl'];
    $desc = app\cfg('app', 'meta.description');
    $title = app\cfg('app', 'meta.title');

    if ($page = app\get('page')) {
        $desc = $page['meta_description'];

        if ($page['meta_title']) {
            $title = $page['meta_title'];
        } else {
            $all = entity\all('page', [['id', $page['path']], ['level', 0, APP['crit']['>']]], ['select' => ['name', 'menu_name'], 'order' => ['level' => 'asc']]);

            foreach ($all as $item) {
                $title = ($item['menu_name'] ?: $item['name']) . ($title ? ' - ' . $title : '');
            }
        }
    } elseif ($entity = app\get('entity')) {
        $title = $entity['name'] . ($title ? ' - ' . $title : '');
    }

    $var = ['description' => $desc ? app\enc($desc) : '', 'title' => $title ? app\enc($title) : ''];

    return app\render($block['tpl'], $var);
}

/**
 * View
 */
function view(array $block): string
{
    $type = app\cfg('block', 'view');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);

    if (!($entity = app\get('entity')) || !($id = app\get('id'))) {
        return '';
    }

    $var = [
        'attr' => arr\extract($entity['attr'], $cfg['attr_id']),
        'data' => app\get('page') ?: entity\one($entity['id'], [['id', $id]]),
    ];

    return $var['attr'] && $var['data'] ? app\render($block['tpl'], $var) : '';
}

/**
 * Page Banner
 */
function banner(array $block): string
{
    $block['tpl'] = $block['tpl'] ?: app\cfg('block', 'banner')['tpl'];

    if (($page = app\get('page')) && $page['entity'] !== 'page_content') {
        $page = entity\one('page', [['id', $page['path']], ['entity', 'page_content']], ['select' => ['image'], 'order' => ['level' => 'desc']]);
    }

    if ($page && ($img = attr\viewer($page, $page['_entity']['attr']['image']))) {
        return app\render($block['tpl'], ['img' => $img]);
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
    $cfg['entity_id'] = $cfg['entity_id'] ?: app\get('entity_id');
    $entity = app\cfg('entity', $cfg['entity_id']);

    if (!$entity || !($attr = arr\extract($entity['attr'], $cfg['attr_id'])) || $cfg['limit'] <= 0) {
        return '';
    }

    $crit = $cfg['crit'];
    $opt = ['limit' => $cfg['limit'], 'order' => $cfg['order'] ?: ['id' => 'desc']];
    $p = arr\replace(['cur' => null, 'q' => null, 'sort' => null, 'dir' => null], request\get('param'));
    $search = null;

    if (in_array('page', [$entity['id'], $entity['parent_id']])) {
        if (app\get('action') !== 'admin') {
            $crit[] = ['status', 'published'];
            $crit[] = ['disabled', false];
        }

        if ($cfg['parent_id']) {
            $crit[] = ['parent_id', $cfg['parent_id'] === true ? app\get('id') : $cfg['parent_id']];
        }
    }

    if ($cfg['search']) {
        if ($p['q'] && ($q = array_filter(explode(' ', (string) $p['q'])))) {
            foreach ($q as $v) {
                $call = function ($attrId) use ($v) {
                    return [$attrId, $v, APP['crit']['~']];
                };
                $crit[] = array_map($call, $cfg['search']);
            }
        }

        $search = search([]);
    }

    $size = entity\size($entity['id'], $crit);
    $total = (int) ceil($size / $opt['limit']) ?: 1;
    $p['cur'] = min(max((int) $p['cur'], 1), $total);
    $opt['offset'] = ($p['cur'] - 1) * $opt['limit'];

    if ($p['sort'] && !empty($attr[$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        $p['sort'] = null;
        $p['dir'] = null;
    }

    $var = [
        'attr' => $attr,
        'data' => entity\all($entity['id'], $crit, $opt),
        'dir' => $p['dir'],
        'entity_id' => $cfg['entity_id'],
        'pager' => $cfg['pager'] ? pager(['cfg' => ['cur' => $p['cur'], 'limit' => $opt['limit'], 'size' => $size]]) : null,
        'search' => $search,
        'sort' => $p['sort'],
        'title' => $cfg['title'] ? app\enc($cfg['title']) : null,
        'url' => request\get('url'),
    ];

    return app\render($block['tpl'], $var);
}

/**
 * Pager
 */
function pager(array $block): string
{
    $type = app\cfg('block', 'pager');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);
    $cfg['cur'] = $cfg['cur'] ?? request\get('param')['cur'] ?? 1;

    if ($cfg['limit'] <= 0 || $cfg['size'] <= 0) {
        return '';
    }

    $url = request\get('url');
    $total = (int) ceil($cfg['size'] / $cfg['limit']) ?: 1;
    $cfg['cur'] = min(max($cfg['cur'], 1), $total);
    $offset = ($cfg['cur'] - 1) * $cfg['limit'];
    $min = max(1, min($cfg['cur'] - intdiv($cfg['pages'], 2), $total - $cfg['pages'] + 1));
    $max = min($min + $cfg['pages'] - 1, $total);
    $links = [];

    if ($cfg['cur'] >= 2) {
        $lp = ['cur' => $cfg['cur'] === 2 ? null : $cfg['cur'] - 1];
        $links[] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $lp, true)];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $lp = ['cur' => $i === 1 ? null : $i];
        $links[] = ['name' => $i, 'url' => app\url($url, $lp, true), 'active' => $i === $cfg['cur']];
    }

    if ($cfg['cur'] < $total) {
        $links[] = ['name' => app\i18n('Next'), 'url' => app\url($url, ['cur' => $cfg['cur'] + 1], true)];
    }

    $var = [
        'info' => app\i18n('%s to %s of %s', (string) ($offset + 1), (string) min($offset + $cfg['limit'], $cfg['size']), (string) $cfg['size']),
        'links' => $links
    ];

    return app\render($block['tpl'], $var);
}

/**
 * Search
 */
function search(array $block): string
{
    $block['tpl'] = $block['tpl'] ?? app\cfg('block', 'search')['tpl'];
    $var = ['q' => request\get('param')['q'] ?? null];

    return app\render($block['tpl'], $var);
}

/**
 * Form
 */
function form(array $block): string
{
    $type = app\cfg('block', 'form');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);

    if (!$cfg['entity_id'] || !($entity = app\cfg('entity', $cfg['entity_id'])) || !($attr = arr\extract($entity['attr'], $cfg['attr_id']))) {
        return '';
    }

    $var = [
        'data' => $cfg['data'],
        'attr' => $attr,
        'file' => !!arr\crit($attr, [['type', 'upload']]),
        'title' => $cfg['title'] ? app\enc($cfg['title']) : null,
    ];

    return app\render($block['tpl'], $var);
}

/**
 * Create Form
 */
function create(array $block): string
{
    $type = app\cfg('block', 'create');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $block['cfg'] = arr\replace($type['cfg'], $block['cfg']);
    $block['cfg']['entity_id'] = $block['cfg']['entity_id'] ?: app\get('entity_id');

    if (($data = request\get('data')) && entity\save($block['cfg']['entity_id'], $data)) {
        $data = [];
    }

    $block['cfg']['data'] = arr\replace(entity\item($block['cfg']['entity_id']), $data);

    return form($block);
}

/**
 * Edit Form
 */
function edit(array $block): string
{
    $type = app\cfg('block', 'edit');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $block['cfg'] = arr\replace($type['cfg'], $block['cfg']);
    $entity = app\get('entity');

    if (!$entity || !$block['cfg']['attr_id']) {
        return '';
    }

    $old = null;

    if (($id = app\get('id')) && !($old = entity\one($entity['id'], [['id', $id]]))) {
        app\msg('Nothing to edit');
        request\redirect(app\url($entity['id'] . '/admin'));
        return '';
    }

    if ($data = request\get('data')) {
        if ($id) {
            $data['id'] = $id;
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


    $block['cfg']['data'] = arr\replace(entity\item($entity['id']), ...$p);
    $block['cfg']['entity_id'] = $entity['id'];
    $block['cfg']['title'] = $entity['name'];

    return form($block);
}

/**
 * Password Form
 */
function password(array $block): string
{
    if ($data = request\get('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            $data['_error']['password'] = app\i18n('Password and password confirmation must be identical');
            $data['_error']['confirmation'] = app\i18n('Password and password confirmation must be identical');
        } else {
            $data = ['id' => account\get('id'), 'password' => $data['password']];

            if (entity\save('account', $data)) {
                request\redirect(request\get('url'));
            }
        }
    }

    $block['tpl'] = $block['tpl'] ?: app\cfg('block', 'password')['tpl'];
    $block['cfg'] = ['attr_id' => ['password', 'confirmation'], 'data' => $data, 'entity_id' => 'account', 'title' => app\i18n('Password')];

    return form($block);
}

/**
 * Login Form
 */
function login(array $block): string
{
    $block['tpl'] = $block['tpl'] ?? app\cfg('block', 'login')['tpl'];
    $entity = app\cfg('entity', 'account');
    $a = ['name' => ['unique' => false, 'minlength' => 0, 'maxlength' => 0], 'password' => ['minlength' => 0, 'maxlength' => 0]];
    $var = [
        'data' => [],
        'attr' => array_replace_recursive(arr\extract($entity['attr'], ['name', 'password']), $a),
        'file' => false,
        'title' => app\i18n('Login'),
    ];

    return app\render($block['tpl'], $var);
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
    $html = '';
    $attrs = ['id' => $block['id']];
    $call = function (array $it): ?string {
        if ($it['url'] === request\get('url')) {
            return 'active';
        }

        if ($it['url'] && strpos(request\get('url'), preg_replace('#\.html#', '', $it['url'])) === 0) {
            return 'path';
        }

        return null;
    };

    if ($cfg['title']) {
        $html .= app\html('h2', [], $cfg['title']);
    }

    $html .= container(array_replace($block, ['cfg' => ['tag' => null]]));

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
        $page = entity\one('page', [['status', 'published'], ['entity', 'page_content'], ['url', $block['cfg']['url']]]);
    } else {
        $page = app\get('page');
    }

    if ($block['cfg']['submenu'] && empty($page['path'][1])) {
        return '';
    }

    $rootCrit = [['status', 'published'], ['entity', 'page_content']];
    $rootCrit[] = $block['cfg']['submenu'] ? ['id', $page['path'][1]] : ['url', '/'];
    $select = ['id', 'name', 'url', 'disabled', 'menu_name', 'pos', 'level'];
    $opt = ['select' => $select, 'order' => ['pos' => 'asc']];

    if (!$root = entity\one('page', $rootCrit, ['select' => $select])) {
        return '';
    }

    $crit = [['status', 'published'], ['entity', 'page_content'], ['pos', $root['pos'] . '.', APP['crit']['~^']]];

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
        $block['cfg']['title'] = $root['menu_name'] ?: $root['name'];
    } elseif ($block['cfg']['root']) {
        $root['level']++;
        $block['cfg']['data'] = [$root['id'] => $root] + $block['cfg']['data'];
    }

    foreach ($block['cfg']['data'] as $id => $item) {
        $block['cfg']['data'][$id]['name'] = $item['menu_name'] ?: $item['name'];
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
        if (!$item['active'] || $item['parent_id'] && empty($data[$item['parent_id']]) || $item['priv'] && !app\allowed($item['priv'])) {
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
    if (!$page = app\get('page')) {
        return '';
    }

    $html = '';
    $crit = [['status', 'published'], ['entity', 'page_content'], ['id', $page['path']]];
    $all = entity\all('page', $crit, ['select' => ['id', 'name', 'url', 'disabled', 'menu_name'], 'order' => ['level' => 'asc']]);

    foreach ($all as $item) {
        if ($item['id'] !== $page['id'] || $page['entity'] === 'page_content') {
            $a = $item['disabled'] || $item['id'] === $page['id'] ? [] : ['href' => $item['url']];
            $html .= ($html ? ' ' : '') . app\html('a', $a, $item['menu_name'] ?: $item['name']);
        }
    }

    return app\html('nav', ['id' => $block['id']], $html);
}
