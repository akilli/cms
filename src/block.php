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
    $ent = $§['vars']['ent'] ? app\cfg('ent', $§['vars']['ent']) : app\data('ent');
    $opt = ['limit' => (int) $§['vars']['limit']];
    $pager = (int) $§['vars']['pager'];
    unset($§['vars']['ent'], $§['vars']['limit'], $§['vars']['pager']);

    if (!$ent || $opt['limit'] <= 0) {
        return '';
    }

    $crit = $§['vars']['act'] !== 'admin' && in_array('page', [$ent['id'], $ent['parent']]) ? [['status', 'published']] : [];
    $url = req\data('url');
    $p = arr\replace(['CKEditorFuncNum' => null, 'cur' => null, 'q' => null, 'sort' => null, 'dir' => null], req\data('get'));
    $p['cur'] = (int) $p['cur'];
    $p['q'] = (string) $p['q'];

    if ($§['vars']['search'] && $p['q'] && ($q = array_filter(explode(' ', $p['q'])))) {
        $searchable = array_keys(arr\crit($ent['attr'], [['searchable', true]])) ?: ['name'];
        $c = [];

        foreach ($searchable as $s) {
            $c[] = [$s, $q, APP['crit']['~']];
        }

        $crit[] = $c;
    } else {
        $p['q'] = null;
    }

    $§['vars']['size'] = ent\size($ent['id'], $crit);
    $pages = (int) ceil($§['vars']['size'] / $opt['limit']) ?: 1;
    $cur = min(max($p['cur'], 1), $pages);
    $opt['offset'] = ($cur - 1) * $opt['limit'];
    $§['vars']['attr'] = ent\attr($ent, $§['vars']['attr']);

    if ($p['sort'] && !empty($§['vars']['attr'][$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        $p['sort'] = null;
        $p['dir'] = null;
        $opt['order'] = ['id' => 'desc'];
    }

    $§['vars']['pager'] = [];

    if ($pager > 0) {
        $min = max(1, min($cur - intdiv($pager, 2), $pages - $pager + 1));
        $max = min($min + $pager - 1, $pages);

        if ($cur >= 2) {
            $lp = ['cur' => $cur === 2 ? null : $cur - 1];
            $§['vars']['pager'][] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $lp, true)];
        }

        for ($i = $min; $min < $max && $i <= $max; $i++) {
            $lp = ['cur' => $i === 1 ? null : $i];
            $§['vars']['pager'][] = ['name' => $i, 'url' => app\url($url, $lp, true), 'active' => $i === $cur];
        }

        if ($cur < $pages) {
            $§['vars']['pager'][] = ['name' => app\i18n('Next'), 'url' => app\url($url, ['cur' => $cur + 1], true)];
        }
    }

    $§['vars']['data'] = ent\all($ent['id'], $crit, $opt);
    $§['vars']['dir'] = $p['dir'];
    $§['vars']['link'] = $§['vars']['act'] === 'index';
    $§['vars']['empty'] = $§['vars']['act'] === 'admin';
    $§['vars']['ent'] = $ent;
    $§['vars']['max'] = min($opt['offset'] + $opt['limit'], $§['vars']['size']);
    $§['vars']['min'] = $opt['offset'] + 1;
    $§['vars']['q'] = $p['q'];
    $§['vars']['rte'] = $p['CKEditorFuncNum'];
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
    $opt = ['order' => ['pos' => 'asc']];
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
