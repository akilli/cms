<?php
declare(strict_types = 1);

namespace block;

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
    $attrs = ['id' => $block['id']] + $block['vars']['attr'];
    $html = '';

    foreach (arr\order(arr\crit(app\layout(), [['parent', $block['id']]]), ['sort' => 'asc']) as $child) {
        $html .= app\block($child['id']);
    }

    return $block['vars']['tag'] ? app\html($block['vars']['tag'], $attrs, $html) : $html;
}

/**
 * Template
 */
function tpl(array $block): string
{
    if (!$block['tpl']) {
        return '';
    }

    $block['vars'] = ['id' => $block['id'], 'tpl' => $block['tpl']] + $block['vars'];
    $block = function ($key) use ($block) {
        return $block['vars'][$key] ?? null;
    };
    ob_start();
    include app\tpl((string) $block('tpl'));

    return ob_get_clean();
}

/**
 * Meta
 */
function meta(array $block): string
{
    if ($page = app\get('page')) {
        $block['vars']['description'] = $page['meta_description'];

        if ($page['meta_title']) {
            $block['vars']['title'] = $page['meta_title'];
        } else {
            $all = entity\all('page', [['id', $page['path']], ['level', 0, APP['crit']['>']]], ['select' => ['name', 'menu_name'], 'order' => ['level' => 'asc']]);

            foreach ($all as $item) {
                $block['vars']['title'] = ($item['menu_name'] ?: $item['name']) . ($block['vars']['title'] ? ' - ' . $block['vars']['title'] : '');
            }
        }
    } elseif (app\get('entity') && ($entity = app\cfg('entity', app\get('entity')))) {
        $block['vars']['title'] = $entity['name'] . ($block['vars']['title'] ? ' - ' . $block['vars']['title'] : '');
    }

    $block['vars']['description'] = $block['vars']['description'] ? app\enc($block['vars']['description']) : '';
    $block['vars']['title'] = $block['vars']['title'] ? app\enc($block['vars']['title']) : '';

    return tpl($block);
}

/**
 * Search
 */
function search(array $block): string
{
    $block['vars']['q'] = $block['vars']['q'] ?? request\get('param')['q'] ?? null;

    return tpl($block);
}

/**
 * Pager
 */
function pager(array $block): string
{
    $block['vars']['size'] = (int) $block['vars']['size'];
    $cur = $block['vars']['cur'] ?? request\get('param')['cur'] ?? 1;
    $limit = (int) $block['vars']['limit'];
    $pages = (int) $block['vars']['pages'];

    if ($limit <= 0 || $block['vars']['size'] <= 0) {
        return '';
    }

    $url = request\get('url');
    $total = (int) ceil($block['vars']['size'] / $limit) ?: 1;
    $cur = min(max($cur, 1), $total);
    $offset = ($cur - 1) * $limit;
    $block['vars']['max'] = min($offset + $limit, $block['vars']['size']);
    $block['vars']['min'] = $offset + 1;
    $block['vars']['links'] = [];
    $min = max(1, min($cur - intdiv($pages, 2), $total - $pages + 1));
    $max = min($min + $pages - 1, $total);

    if ($cur >= 2) {
        $lp = ['cur' => $cur === 2 ? null : $cur - 1];
        $block['vars']['links'][] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $lp, true)];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $lp = ['cur' => $i === 1 ? null : $i];
        $block['vars']['links'][] = ['name' => $i, 'url' => app\url($url, $lp, true), 'active' => $i === $cur];
    }

    if ($cur < $total) {
        $block['vars']['links'][] = ['name' => app\i18n('Next'), 'url' => app\url($url, ['cur' => $cur + 1], true)];
    }

    return tpl($block);
}

/**
 * Index
 */
function index(array $block): string
{
    $block['vars']['entity'] = app\cfg('entity', $block['vars']['entity'] ?: app\get('entity'));
    $crit = is_array($block['vars']['crit']) ? $block['vars']['crit'] : [];
    $opt = ['limit' => (int) $block['vars']['limit']];
    $opt['order'] = $block['vars']['order'] && is_array($block['vars']['order']) ? $block['vars']['order'] : ['id' => 'desc'];

    if (!$block['vars']['entity'] || $opt['limit'] <= 0) {
        return '';
    }

    if (in_array('page', [$block['vars']['entity']['id'], $block['vars']['entity']['parent']])) {
        if (!$block['vars']['inaccessible']) {
            $crit[] = ['status', 'published'];
            $crit[] = ['disabled', false];
        }

        if ($block['vars']['parent_id']) {
            $crit[] = ['parent_id', $block['vars']['parent_id'] === true ? app\get('id') : (int) $block['vars']['parent_id']];
        }
    }

    $p = arr\replace(['cur' => null, 'q' => null, 'sort' => null, 'dir' => null], request\get('param'));

    if ($block['vars']['search']) {
        if (($p['q'] = trim((string) $p['q'])) && ($q = array_filter(explode(' ', $p['q'])))) {
            $c = [];

            foreach ($block['vars']['search'] as $attrId) {
                $c[] = [$attrId, $q, APP['crit']['~']];
            }

            $crit[] = $c;
        }

        $search = ['id' => $block['id'] . '-search', 'type' => 'search', 'vars' => ['q' => $p['q']]];
        app\layout($search['id'], $search);
        $block['vars']['search'] = app\block($search['id']);
    } else {
        $block['vars']['search'] = null;
    }

    $size = entity\size($block['vars']['entity']['id'], $crit);
    $total = (int) ceil($size / $opt['limit']) ?: 1;
    $p['cur'] = min(max((int) $p['cur'], 1), $total);
    $opt['offset'] = ($p['cur'] - 1) * $opt['limit'];

    if ($p['sort'] && in_array($p['sort'], $block['vars']['attr'])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        $p['sort'] = null;
        $p['dir'] = null;
    }

    if ($block['vars']['pager']) {
        $pager = ['id' => $block['id'] . '-pager', 'type' => 'pager', 'vars' => ['cur' => $p['cur'], 'limit' => $opt['limit'], 'size' => $size]];
        app\layout($pager['id'], $pager);
        $block['vars']['pager'] = app\block($pager['id']);
    } else {
        $block['vars']['pager'] = null;
    }

    $block['vars']['data'] = entity\all($block['vars']['entity']['id'], $crit, $opt);
    $block['vars']['dir'] = $p['dir'];
    $block['vars']['sort'] = $p['sort'];
    $block['vars']['title'] = app\enc($block['vars']['title'] ?? $block['vars']['entity']['name']);
    $block['vars']['url'] = request\get('url');

    return tpl($block);
}

/**
 * Form
 */
function form(array $block): string
{
    if (!$block['vars']['entity']) {
        return '';
    }

    $block['vars']['title'] = $block['vars']['title'] ? app\enc($block['vars']['title']) : null;
    $block['vars']['file'] = false;

    foreach ($block['vars']['attr'] as $attrId) {
        if ($block['vars']['file'] = ($block['vars']['entity']['attr'][$attrId]['type'] ?? null) === 'upload') {
            break;
        }
    }

    return tpl($block);
}

/**
 * Create Form
 */
function create(array $block): string
{
    $block['vars']['entity'] = app\cfg('entity', $block['vars']['entity'] ?: app\get('entity'));

    if (($data = request\get('data')) && entity\save($block['vars']['entity']['id'], $data)) {
        if ($block['vars']['redirect']) {
            request\redirect(app\url($block['vars']['entity']['id'] . '/edit/' . $data['id']));
            return '';
        }

        $data = [];
    }

    $block['vars']['data'] = array_replace(entity\item($block['vars']['entity']), $data);
    $block['vars']['title'] = $block['vars']['title'] ?? $block['vars']['entity']['name'];

    return form($block);
}

/**
 * Login Form
 */
function login(array $block): string
{
    $block['vars']['attr'] = ['name', 'password'];
    $block['vars']['data'] = [];
    $a = ['name' => ['unique' => false, 'minlength' => 0, 'maxlength' => 0], 'password' => ['minlength' => 0, 'maxlength' => 0]];
    $block['vars']['entity'] = app\cfg('entity', 'account');
    $block['vars']['entity']['attr'] = array_replace_recursive($block['vars']['entity']['attr'], $a);

    return form($block);
}

/**
 * View
 */
function view(array $block): string
{
    return $block['vars']['entity'] ? tpl($block) : '';
}

/**
 * Page banner
 */
function banner(array $block): string
{
    if (($page = app\get('page')) && $page['entity'] !== 'content') {
        $page = entity\one('page', [['id', $page['path']], ['entity', 'content']], ['select' => ['image'], 'order' => ['level' => 'desc']]);
    }

    return $page && ($block['vars']['img'] = attr\viewer($page, $page['_entity']['attr']['image'])) ? tpl($block) : '';
}


/**
 * Page sidebar
 */
function sidebar(array $block): string
{
    if (!$page = app\get('page')) {
        return '';
    }

    if (!$page['sidebar'] && $block['vars']['inherit']) {
        $crit = [['id', $page['path']], ['sidebar', '', APP['crit']['!=']]];
        $opt = ['select' => ['sidebar'], 'order' => ['level' => 'desc']];
        $page['sidebar'] = entity\one('page', $crit, $opt)['sidebar'] ?? '';
    }

    return attr\viewer($page, $page['_entity']['attr']['sidebar']);
}

/**
 * Navigation
 *
 * @throws DomainException
 */
function nav(array $block): string
{
    if (!$block['vars']['data']) {
        return '';
    }

    $url = request\get('url');
    $count = count($block['vars']['data']);
    $start = current($block['vars']['data'])['level'] ?? 1;
    $level = 0;
    $i = 0;
    $html = '';

    if ($block['vars']['toggle']) {
        $html .= app\html('span', ['data-action' => 'toggle', 'data-target' => $block['vars']['toggle']]);
    }

    if ($block['vars']['title']) {
        $html .= app\html('h2', [], $block['vars']['title']);
    }

    foreach ($block['vars']['data'] as $item) {
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

        if (($next = next($block['vars']['data'])) && $item['level'] < ($next['level'] ?? $start)) {
            $class[] = 'parent';

            if ($block['vars']['toggle']) {
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

    return $html;
}

/**
 * Menu Navigation
 */
function menu(array $block): string
{
    $page = app\get('page');
    $sub = $block['vars']['mode'] === 'sub';

    if ($sub && empty($page['path'][1])) {
        return '';
    }

    $crit = [['status', 'published'], ['entity', 'content']];
    $crit[] = $sub ? ['id', $page['path'][1]] : ['url', '/'];
    $select = ['id', 'name', 'url', 'disabled', 'menu_name', 'pos', 'level'];
    $opt = ['select' => $select, 'order' => ['pos' => 'asc']];

    if (!$root = entity\one('page', $crit, ['select' => $select])) {
        return '';
    }

    $crit = [['status', 'published'], ['entity', 'content'], ['pos', $root['pos'] . '.', APP['crit']['~^']]];

    if ($sub) {
        $parent = $page['path'];
        unset($parent[0]);
        $crit[] = [['id', $page['path']], ['parent_id', $parent]];
    } else {
        $crit[] = ['menu', true];
    }

    $block['vars']['data'] = entity\all('page', $crit, $opt);

    if ($block['vars']['root'] && $sub) {
        $block['vars']['title'] = $root['menu_name'] ?: $root['name'];
    } elseif ($block['vars']['root']) {
        $root['level']++;
        $block['vars']['data'] = [$root['id'] => $root] + $block['vars']['data'];
    }

    foreach ($block['vars']['data'] as $id => $item) {
        $block['vars']['data'][$id]['name'] = $item['menu_name'] ?: $item['name'];
    }

    return nav($block);
}

/**
 * Toolbar Navigation
 */
function toolbar(array $block): string
{
    $block['vars']['data'] = app\cfg('toolbar');
    $empty = [];

    foreach ($block['vars']['data'] as $id => $item) {
        if ($item['priv'] && !app\allowed($item['priv'])) {
            unset($block['vars']['data'][$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent']) {
            unset($empty[$item['parent']]);
        }
    }

    $block['vars']['data'] = array_diff_key($block['vars']['data'], $empty);

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
    $all = entity\all('page', [['id', $page['path']]], ['select' => ['id', 'name', 'url', 'disabled', 'menu_name'], 'order' => ['level' => 'asc']]);

    foreach ($all as $item) {
        if ($item['id'] !== $page['id'] || $page['entity'] === 'content') {
            $a = $item['disabled'] || $item['id'] === $page['id'] ? [] : ['href' => $item['url']];
            $html .= ($html ? ' ' : '') . app\html('a', $a, $item['menu_name'] ?: $item['name']);
        }
    }

    return app\html('nav', ['id' => $block['id']], $html);
}
