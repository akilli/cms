<?php
declare(strict_types = 1);

namespace block;

use app;
use arr;
use ent;
use html;
use req;
use session;
use DomainException;

/**
 * Container
 */
function container(array $§): string
{
    $html = '';

    foreach (arr\order(arr\crit(app\layout(), [['parent', $§['id']]]), ['sort' => 'asc']) as $child) {
        $html .= app\§($child['id']);
    }

    return $§['vars']['tag'] ? html\tag($§['vars']['tag'], ['id' => $§['id']], $html) : $html;
}

/**
 * Template
 */
function tpl(array $§): string
{
    if (!$§['tpl']) {
        return '';
    }

    $§['vars'] = ['id' => $§['id'], 'token' => session\token(), 'tpl' => $§['tpl']] + $§['vars'];
    $§ = function ($key) use ($§) {
        return $§['vars'][$key] ?? null;
    };
    ob_start();
    include app\tpl((string) $§('tpl'));

    return ob_get_clean();
}

/**
 * Meta
 */
function meta(array $§): string
{
    $§['vars']['desc'] = $§['vars']['desc'] ? app\enc($§['vars']['desc']) : null;
    $§['vars']['title'] = app\enc($§['vars']['title'] ?: app\cfg('app', 'name'));

    return tpl($§);
}

/**
 * Message
 */
function msg(array $§): string
{
    return ($§['vars']['data'] = app\msg()) ? tpl($§) : '';
}

/**
 * Search
 */
function search(array $§): string
{
    $§['vars']['q'] = $§['vars']['q'] ?? req\data('get')['q'] ?? null;

    return tpl($§);
}

/**
 * Pager
 */
function pager(array $§): string
{
    $§['vars']['size'] = (int) $§['vars']['size'];
    $cur = $§['vars']['cur'] ?? req\data('get')['cur'] ?? 1;
    $limit = (int) $§['vars']['limit'];
    $pages = (int) $§['vars']['pages'];
    unset($§['vars']['cur'], $§['vars']['limit'], $§['vars']['pages']);

    if ($limit <= 0 || $§['vars']['size'] <= 0) {
        return '';
    }

    $url = req\data('url');
    $total = (int) ceil($§['vars']['size'] / $limit) ?: 1;
    $cur = min(max($cur, 1), $total);
    $offset = ($cur - 1) * $limit;
    $§['vars']['max'] = min($offset + $limit, $§['vars']['size']);
    $§['vars']['min'] = $offset + 1;
    $§['vars']['links'] = [];
    $min = max(1, min($cur - intdiv($pages, 2), $total - $pages + 1));
    $max = min($min + $pages - 1, $total);

    if ($cur >= 2) {
        $lp = ['cur' => $cur === 2 ? null : $cur - 1];
        $§['vars']['links'][] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $lp, true)];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $lp = ['cur' => $i === 1 ? null : $i];
        $§['vars']['links'][] = ['name' => $i, 'url' => app\url($url, $lp, true), 'active' => $i === $cur];
    }

    if ($cur < $total) {
        $§['vars']['links'][] = ['name' => app\i18n('Next'), 'url' => app\url($url, ['cur' => $cur + 1], true)];
    }

    return tpl($§);
}

/**
 * Entity
 */
function ent(array $§): string
{
    $§['vars']['ent'] = $§['vars']['ent'] ? app\cfg('ent', $§['vars']['ent']) : app\data('ent');

    if (!$§['vars']['ent']) {
        return '';
    }

    $§['vars']['crit'] = is_array($§['vars']['crit']) ? $§['vars']['crit'] : [];
    $§['vars']['order'] = is_array($§['vars']['order']) ? $§['vars']['order'] : [];
    $§['vars']['limit'] = (int) $§['vars']['limit'];
    $§['vars']['offset'] = (int) $§['vars']['offset'];
    $opt = [];

    if ($§['vars']['order']) {
        $opt['order'] = $§['vars']['order'];
    }

    if ($§['vars']['limit'] > 0) {
        $opt['limit'] = $§['vars']['limit'];
    }

    if ($§['vars']['offset'] > 0) {
        $opt['offset'] = $§['vars']['offset'];
    }

    $§['vars']['attr'] = ent\attr($§['vars']['ent'], $§['vars']['attr']);
    $§['vars']['data'] = ent\all($§['vars']['ent']['id'], $§['vars']['crit'], $opt);
    $§['vars']['size'] = ent\size($§['vars']['ent']['id'], $§['vars']['crit']);

    return tpl($§);
}

/**
 * Index
 */
function index(array $§): string
{
    $act = $§['vars']['act'];
    $ent = $§['vars']['ent'] ? app\cfg('ent', $§['vars']['ent']) : app\data('ent');
    $opt = ['limit' => (int) $§['vars']['limit']];
    unset($§['vars']['act'], $§['vars']['ent'], $§['vars']['limit']);

    if (!$ent || $opt['limit'] <= 0) {
        return '';
    }

    $§['vars']['attr'] = ent\attr($ent, $§['vars']['attr']);
    $crit = $act !== 'admin' && in_array('page', [$ent['id'], $ent['parent']]) ? [['status', 'published']] : [];
    $url = req\data('url');
    $p = arr\replace(['CKEditorFuncNum' => null, 'cur' => null, 'el' => null, 'q' => null, 'sort' => null, 'dir' => null], req\data('get'));
    $p['cur'] = (int) $p['cur'];
    $p['q'] = trim((string) $p['q']);

    if ($§['vars']['search']) {
        if ($p['q']) {
            $q = array_filter(explode(' ', $p['q']));
            $searchable = array_keys(arr\crit($ent['attr'], [['searchable', true]])) ?: ['name'];
            $c = [];

            foreach ($searchable as $s) {
                $c[] = [$s, $q, APP['crit']['~']];
            }

            $crit[] = $c;
        }

        $search = ['id' => $§['id'] . '.search', 'type' => 'search', 'vars' => ['q' => $p['q']]];
        app\layout($search['id'], $search);
        $§['vars']['search'] = app\§($search['id']);
    } else {
        $§['vars']['search'] = null;
        $p['q'] = null;
    }

    $size = ent\size($ent['id'], $crit);
    $total = (int) ceil($size / $opt['limit']) ?: 1;
    $cur = min(max($p['cur'], 1), $total);
    $opt['offset'] = ($cur - 1) * $opt['limit'];

    if ($p['sort'] && !empty($§['vars']['attr'][$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        $p['sort'] = null;
        $p['dir'] = null;
        $opt['order'] = ['id' => 'desc'];
    }

    if ($§['vars']['pager']) {
        $pager = ['id' => $§['id'] . '.pager', 'type' => 'pager', 'vars' => ['cur' => $cur, 'limit' => $opt['limit'], 'size' => $size]];
        app\layout($pager['id'], $pager);
        $§['vars']['pager'] = app\§($pager['id']);
    } else {
        $§['vars']['pager'] = null;
        $p['cur'] = null;
    }

    if ($act === 'admin') {
        $§['vars']['actions'] = ['view', 'edit', 'delete'];
    } elseif ($act === 'browser') {
        $§['vars']['actions'] = ['rte'];
    } else {
        $§['vars']['actions'] = [];
    }

    $§['vars']['create'] = $act === 'admin';
    $§['vars']['data'] = ent\all($ent['id'], $crit, $opt);
    $§['vars']['dir'] = $p['dir'];
    $§['vars']['el'] = $act === 'browser' ? $p['el'] : null;
    $§['vars']['ent'] = $ent;
    $§['vars']['head'] = $act === 'admin';
    $§['vars']['link'] = $act === 'index';
    $§['vars']['rte'] = $act === 'browser' ? $p['CKEditorFuncNum'] : null;
    $§['vars']['sort'] = $p['sort'];
    $§['vars']['title'] = $ent['name'];
    $§['vars']['url'] = $url;

    return tpl($§);
}

/**
 * Form
 */
function form(array $§): string
{
    if (empty($§['vars']['data']['_ent'])) {
        return '';
    }

    $§['vars']['attr'] = ent\attr($§['vars']['data']['_ent'], $§['vars']['attr']);
    $§['vars']['file'] = in_array('file', array_column($§['vars']['attr'], 'type'));
    $§['vars']['title'] = $§['vars']['title'] ? app\enc($§['vars']['title']) : null;

    return tpl($§);
}

/**
 * Form
 */
function login(array $§): string
{
    $§['vars']['attr'] = ent\attr(app\cfg('ent', 'account'), ['incl' => ['name', 'password']]);
    $§['vars']['attr']['name'] = array_replace($§['vars']['attr']['name'], ['unique' => false, 'minlength' => 0, 'maxlength' => 0]);
    $§['vars']['attr']['password'] = array_replace($§['vars']['attr']['password'], ['minlength' => 0, 'maxlength' => 0]);
    $§['vars']['data'] = [];
    $§['vars']['file'] = false;
    $§['vars']['title'] = $§['vars']['title'] ? app\enc($§['vars']['title']) : null;

    return tpl($§);
}

/**
 * View
 */
function view(array $§): string
{
    if (empty($§['vars']['data']['_ent'])) {
        return '';
    }

    $§['vars']['attr'] = ent\attr($§['vars']['data']['_ent'], $§['vars']['attr']);

    return tpl($§);
}

/**
 * Nav
 *
 * @throws DomainException
 */
function nav(array $§): string
{
    if (!$§['vars']['data']) {
        return '';
    }

    $url = req\data('url');
    $count = count($§['vars']['data']);
    $level = 0;
    $i = 0;
    $html = '';

    foreach ($§['vars']['data'] as $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $item = arr\replace(['name' => null, 'url' => null, 'level' => 1], $item);
        $a = $item['url'] ? ['href' => $item['url']] : [];
        $class = '';

        if ($item['url'] === $url) {
            $a['class'] = 'active';
            $class .= ' class="active"';
        }

        if ($item['level'] > $level) {
             $html .= '<ul><li' . $class . '>';
        } elseif ($item['level'] < $level) {
             $html .= '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>';
        } else {
             $html .= '</li><li' . $class . '>';
        }

        $html .= html\tag($item['url'] ? 'a' : 'span', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return $§['vars']['tag'] ? html\tag($§['vars']['tag'], ['id' => $§['id']], $html) : $html;
}

/**
 * Menu
 */
function menu(array $§): string
{
    $crit = [['status', 'published'], ['menu', true], ['level', 0, APP['crit']['>']]];
    $opt = ['select' => ['id', 'name', 'url', 'level'], 'order' => ['pos' => 'asc']];
    $§['vars']['data'] = ent\all('page', $crit, $opt);

    return nav($§);
}

/**
 * Toolbar
 */
function toolbar(array $§): string
{
    $§['vars']['data'] = app\cfg('toolbar');
    $empty = [];

    foreach ($§['vars']['data'] as $id => $item) {
        if ($item['priv'] && !app\allowed($item['priv'])) {
            unset($§['vars']['data'][$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent']) {
            unset($empty[$item['parent']]);
        }
    }

    $§['vars']['data'] = array_diff_key($§['vars']['data'], $empty);

    return nav($§);
}
