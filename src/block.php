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
 * Page Banner
 */
function banner(array $block): string
{
    $block['tpl'] = $block['tpl'] ?: app\cfg('block', 'banner')['tpl'];
    $var = [];

    if (($page = app\get('page')) && $page['entity'] !== 'page_content') {
        $page = entity\one('page', [['id', $page['path']], ['entity', 'page_content']], ['select' => ['image'], 'order' => ['level' => 'desc']]);
    }

    if ($page && ($var['img'] = attr\viewer($page, $page['_entity']['attr']['image']))) {
        return app\render($block['tpl'], $var);
    }

    return '';
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

/**
 * Container
 */
function container(array $block): string
{
    $cfg = arr\replace(app\cfg('block', 'container')['cfg'], $block['cfg']);
    $html = '';

    foreach (arr\order(arr\crit(app\layout(), [['parent_id', $block['id']]]), ['sort' => 'asc']) as $child) {
        $html .= app\block($child['id']);
    }

    return $html && $cfg['tag'] ? app\html($cfg['tag'], ['id' => $block['id']], $html) : $html;
}

/**
 * Content
 */
function content(array $block): string
{
    $type = app\cfg('block', 'content');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $block['cfg'] = arr\replace($type['cfg'], $block['cfg']);

    if (!$block['cfg']['content']) {
        return '';
    }

    $block['cfg']['title'] = $block['cfg']['title'] ? app\enc($block['cfg']['title']) : null;

    return app\render($block['tpl'], $block['cfg']);
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

    if (!$entity || !($block['cfg']['attr'] = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
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
    $block['cfg']['file'] = !!arr\crit($block['cfg']['attr'], [['type', 'upload']]);
    $block['cfg']['title'] = $entity['name'];

    return form($block);
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
 * Index
 */
function index(array $block): string
{
    $type = app\cfg('block', 'index');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $block['cfg'] = arr\replace($type['cfg'], $block['cfg']);
    $block['cfg']['entity_id'] = $block['cfg']['entity_id'] ?: app\get('entity_id');
    $entity = app\cfg('entity', $block['cfg']['entity_id']);

    if (!$entity || !($block['cfg']['attr'] = arr\extract($entity['attr'], $block['cfg']['attr_id'])) || $block['cfg']['limit'] <= 0) {
        return '';
    }

    $crit = $block['cfg']['crit'];
    $opt = ['limit' => $block['cfg']['limit'], 'order' => $block['cfg']['order'] ?: ['id' => 'desc']];

    if (in_array('page', [$entity['id'], $entity['parent_id']])) {
        if (app\get('action') !== 'admin') {
            $crit[] = ['status', 'published'];
            $crit[] = ['disabled', false];
        }

        if ($block['cfg']['parent_id']) {
            $crit[] = ['parent_id', $block['cfg']['parent_id'] === true ? app\get('id') : $block['cfg']['parent_id']];
        }
    }

    $p = arr\replace(['cur' => null, 'q' => null, 'sort' => null, 'dir' => null], request\get('param'));

    if ($block['cfg']['search']) {
        if (($p['q'] = trim((string) $p['q'])) && ($q = array_filter(explode(' ', $p['q'])))) {
            $c = [];

            foreach ($block['cfg']['search'] as $attrId) {
                $c[] = [$attrId, $q, APP['crit']['~']];
            }

            $crit[] = $c;
        }

        $block['cfg']['search'] = search(['cfg' => ['q' => $p['q']]]);
    } else {
        $block['cfg']['search'] = null;
    }

    $size = entity\size($entity['id'], $crit);
    $total = (int) ceil($size / $opt['limit']) ?: 1;
    $p['cur'] = min(max((int) $p['cur'], 1), $total);
    $opt['offset'] = ($p['cur'] - 1) * $opt['limit'];

    if ($p['sort'] && !empty($block['cfg']['attr'][$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        $p['sort'] = null;
        $p['dir'] = null;
    }

    if ($block['cfg']['pager']) {
        $block['cfg']['pager'] = pager(['cfg' => ['cur' => $p['cur'], 'limit' => $opt['limit'], 'size' => $size]]);
    } else {
        $block['cfg']['pager'] = null;
    }

    $block['cfg']['data'] = entity\all($entity['id'], $crit, $opt);
    $block['cfg']['dir'] = $p['dir'];
    $block['cfg']['sort'] = $p['sort'];
    $block['cfg']['title'] = app\enc($block['cfg']['title'] ?? $entity['name']);
    $block['cfg']['url'] = request\get('url');

    return app\render($block['tpl'], $block['cfg']);
}

/**
 * Login Form
 */
function login(array $block): string
{
    $block['cfg'] = ['attr_id' => ['name', 'password'], 'entity_id' => 'account', 'title' => app\i18n('Login')];

    return form($block);
}

/**
 * Menu Navigation
 */
function menu(array $block): string
{
    $block['cfg'] = arr\replace(app\cfg('block', 'menu')['cfg'], $block['cfg']);
    $page = app\get('page');
    $sub = $block['cfg']['mode'] === 'sub';

    if ($sub && empty($page['path'][1])) {
        return '';
    }

    $crit = [['status', 'published'], ['entity', 'page_content']];
    $crit[] = $sub ? ['id', $page['path'][1]] : ['url', '/'];
    $select = ['id', 'name', 'url', 'disabled', 'menu_name', 'pos', 'level'];
    $opt = ['select' => $select, 'order' => ['pos' => 'asc']];

    if (!$root = entity\one('page', $crit, ['select' => $select])) {
        return '';
    }

    $crit = [['status', 'published'], ['entity', 'page_content'], ['pos', $root['pos'] . '.', APP['crit']['~^']]];

    if ($sub) {
        $parent = $page['path'];
        unset($parent[0]);
        $crit[] = [['id', $page['path']], ['parent_id', $parent]];
    } else {
        $crit[] = ['menu', true];
    }

    $block['cfg']['data'] = entity\all('page', $crit, $opt);
    $block['cfg']['title'] = null;

    if ($block['cfg']['root'] && $sub) {
        $block['cfg']['title'] = $root['menu_name'] ?: $root['name'];
    } elseif ($block['cfg']['root']) {
        $root['level']++;
        $block['cfg']['data'] = [$root['id'] => $root] + $block['cfg']['data'];
    }

    foreach ($block['cfg']['data'] as $id => $item) {
        $block['cfg']['data'][$id]['name'] = $item['menu_name'] ?: $item['name'];
    }

    return nav($block);
}

/**
 * Meta
 */
function meta(array $block): string
{
    $block['tpl'] = $block['tpl'] ?: app\cfg('block', 'meta')['tpl'];
    $block['cfg'] = [];
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

    $block['cfg']['description'] = $desc ? app\enc($desc) : '';
    $block['cfg']['title'] = $title ? app\enc($title) : '';

    return app\render($block['tpl'], $block['cfg']);
}

/**
 * Navigation
 *
 * @throws DomainException
 */
function nav(array $block): string
{
    $block['cfg'] = arr\replace(app\cfg('block', 'nav')['cfg'], $block['cfg']);

    if (!$block['cfg']['data']) {
        return '';
    }

    $url = request\get('url');
    $count = count($block['cfg']['data']);
    $start = current($block['cfg']['data'])['level'] ?? 1;
    $level = 0;
    $i = 0;
    $html = '';
    $attrs = ['id' => $block['id']];

    if ($block['cfg']['title']) {
        $html .= app\html('h2', [], $block['cfg']['title']);
    }

    $html .= container(array_replace($block, ['cfg' => ['tag' => null]]));

    if ($block['cfg']['toggle']) {
        $html .= app\html('span', ['data-action' => 'toggle', 'data-target' => $block['id']]);
        $attrs['data-toggle'] = '';
    }

    foreach ($block['cfg']['data'] as $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $item = arr\replace(['name' => null, 'url' => null, 'disabled' => false, 'level' => $start], $item);
        $item['level'] = $item['level'] - $start + 1;
        $a = $item['url'] && !$item['disabled'] ? ['href' => $item['url']] : [];
        $class = [];
        $c = '';
        $toggle = '';

        if ($item['url'] && $item['url'] === $url) {
            $class[] = 'active';
        } elseif ($item['url'] && strpos($url, preg_replace('#\.html#', '', $item['url'])) === 0) {
            $class[] = 'path';
        }

        if (($next = next($block['cfg']['data'])) && $item['level'] < ($next['level'] ?? $start)) {
            $class[] = 'parent';

            if ($block['cfg']['toggle']) {
                $toggle = app\html('span', ['data-action' => 'toggle']);
            }
        }

        if ($class) {
            $a['class'] = implode(' ', $class);
            $c = ' class="' . $a['class'] . '"';
        }

        if ($item['level'] > $level) {
            $html .= '<ul><li' . $c . '>';
        } elseif ($item['level'] < $level) {
            $html .= '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $c . '>';
        } else {
            $html .= '</li><li' . $c . '>';
        }

        $html .= $toggle . app\html('a', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return $block['cfg']['tag'] ? app\html($block['cfg']['tag'], $attrs, $html) : $html;
}

/**
 * Pager
 */
function pager(array $block): string
{
    $type = app\cfg('block', 'pager');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $block['cfg'] = arr\replace($type['cfg'], $block['cfg']);
    $block['cfg']['size'] = (int) $block['cfg']['size'];
    $cur = $block['cfg']['cur'] ?? request\get('param')['cur'] ?? 1;
    $limit = (int) $block['cfg']['limit'];
    $pages = (int) $block['cfg']['pages'];

    if ($limit <= 0 || $block['cfg']['size'] <= 0) {
        return '';
    }

    $url = request\get('url');
    $total = (int) ceil($block['cfg']['size'] / $limit) ?: 1;
    $cur = min(max($cur, 1), $total);
    $offset = ($cur - 1) * $limit;
    $min = max(1, min($cur - intdiv($pages, 2), $total - $pages + 1));
    $max = min($min + $pages - 1, $total);
    $block['cfg']['info'] = app\i18n(
        '%s to %s of %s',
        (string) ($offset + 1),
        (string) min($offset + $limit, $block['cfg']['size']),
        (string) $block['cfg']['size']
    );
    $block['cfg']['links'] = [];

    if ($cur >= 2) {
        $lp = ['cur' => $cur === 2 ? null : $cur - 1];
        $block['cfg']['links'][] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $lp, true)];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $lp = ['cur' => $i === 1 ? null : $i];
        $block['cfg']['links'][] = ['name' => $i, 'url' => app\url($url, $lp, true), 'active' => $i === $cur];
    }

    if ($cur < $total) {
        $block['cfg']['links'][] = ['name' => app\i18n('Next'), 'url' => app\url($url, ['cur' => $cur + 1], true)];
    }

    return app\render($block['tpl'], $block['cfg']);
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
 * Search
 */
function search(array $block): string
{
    $type = app\cfg('block', 'search');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $cfg = arr\replace($type['cfg'], $block['cfg']);
    $var = ['q' => $cfg['q'] ?? request\get('param')['q'] ?? null];

    return app\render($block['tpl'], $var);
}

/**
 * Page Sidebar
 */
function sidebar(array $block): string
{
    if (!$page = app\get('page')) {
        return '';
    }

    $block['cfg'] = arr\replace(app\cfg('block', 'sidebar')['cfg'], $block['cfg']);

    if (!$page['sidebar'] && is_int($block['cfg']['inherit'])) {
        $crit = [['id', $page['path']], ['sidebar', '', APP['crit']['!=']], ['level', $block['cfg']['inherit'], APP['crit']['>=']]];
        $opt = ['select' => ['sidebar'], 'order' => ['level' => 'desc']];
        $page['sidebar'] = entity\one('page', $crit, $opt)['sidebar'] ?? '';
    }

    return attr\viewer($page, $page['_entity']['attr']['sidebar']);
}

/**
 * Toolbar Navigation
 */
function toolbar(array $block): string
{
    $block['cfg'] = [];
    $block['cfg']['data'] = app\cfg('toolbar');
    $empty = [];

    foreach ($block['cfg']['data'] as $id => $item) {
        if ($item['priv'] && !app\allowed($item['priv'])) {
            unset($block['cfg']['data'][$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent_id']) {
            unset($empty[$item['parent_id']]);
        }
    }

    $block['cfg']['data'] = array_diff_key($block['cfg']['data'], $empty);

    return nav($block);
}

/**
 * Template
 */
function tpl(array $block): string
{
    return $block['tpl'] ? app\render($block['tpl']) : '';
}

/**
 * View
 */
function view(array $block): string
{
    $type = app\cfg('block', 'view');
    $block['tpl'] = $block['tpl'] ?? $type['tpl'];
    $block['cfg'] = arr\replace($type['cfg'], $block['cfg']);

    if (!($entity = app\get('entity')) || !($id = app\get('id'))) {
        return '';
    }

    $block['cfg']['attr'] = arr\extract($entity['attr'], $block['cfg']['attr_id']);
    $block['cfg']['data'] = app\get('page') ?: entity\one($entity['id'], [['id', $id]]);

    return $block['cfg']['attr'] && $block['cfg']['data'] ? app\render($block['tpl'], $block['cfg']) : '';
}
