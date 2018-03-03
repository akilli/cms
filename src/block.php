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
    $§['vars'] = arr\replace(['tag' => null], $§['vars']);
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
    $§['vars'] = arr\replace(['desc' => null, 'title' => null], $§['vars']);
    $§['vars']['desc'] = $§['vars']['desc'] ? app\enc($§['vars']['desc']) : null;
    $§['vars']['title'] = app\enc($§['vars']['title'] ?: app\cfg('app', 'name'));

    return tpl($§);
}

/**
 * Message
 */
function msg(array $§): string
{
    $§['vars'] = [];

    return ($§['vars']['data'] = app\msg()) ? tpl($§) : '';
}

/**
 * Index
 */
function index(array $§): string
{
    $§['vars'] = arr\replace(['act' => 'index', 'attr' => [], 'ent' => null, 'limit' => 10, 'pager' => 0, 'search' => false], $§['vars']);
    $ent = $§['vars']['ent'] ? app\cfg('ent', $§['vars']['ent']) : app\data('ent');

    if (!$ent || $§['vars']['limit'] <= 0) {
        return '';
    }

    $limit = (int) $§['vars']['limit'];
    $pager = (int) $§['vars']['pager'];
    unset($§['vars']['ent'], $§['vars']['limit'], $§['vars']['pager']);
    $§['vars']['attr'] = ent\attr($ent, $§['vars']['attr']);
    $crit = $§['vars']['act'] !== 'admin' && in_array('page', [$ent['id'], $ent['parent']]) ? [['status', 'published']] : [];
    $opt = ['limit' => $limit];
    $url = req\data('url');
    $p = ['cur' => 0, 'q' => '', 'sort' => null, 'dir' => null];

    if ($§['vars']['act'] === 'browser') {
        $p += ['CKEditorFuncNum' => null];
    }

    $p = arr\replace($p, req\data('get'));

    if ($p['q'] && ($q = array_filter(explode(' ', (string) $p['q'])))) {
        $searchable = array_keys(arr\crit($ent['attr'], [['searchable', true]])) ?: ['name'];
        $c = [];

        foreach ($searchable as $s) {
            $c[] = [$s, $q, APP['crit']['~']];
        }

        $crit[] = $c;
    } else {
        unset($p['q']);
    }

    $§['vars']['size'] = ent\size($ent['id'], $crit);
    $pages = (int) ceil($§['vars']['size'] / $opt['limit']) ?: 1;
    $cur = min(max($p['cur'], 1), $pages);
    unset($p['cur']);
    $opt['offset'] = ($cur - 1) * $opt['limit'];

    if ($p['sort'] && !empty($§['vars']['attr'][$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        unset($p['sort'], $p['dir']);
        $opt['order'] = ['id' => 'desc'];
    }

    $§['vars']['data'] = ent\all($ent['id'], $crit, $opt);
    $§['vars']['pager'] = [];

    if ($pager > 0) {
        $min = max(1, min($cur - intdiv($pager, 2), $pages - $pager + 1));
        $max = min($min + $pager - 1, $pages);

        if ($cur >= 2) {
            $lp = $cur === 2 ? $p : ['cur' => $cur - 1] + $p;
            $§['vars']['pager'][] = ['name' => app\i18n('Previous'), 'url' => app\url($url, $lp)];
        }

        for ($i = $min; $min < $max && $i <= $max; $i++) {
            $lp = $i === 1 ? $p : ['cur' => $i] + $p;
            $§['vars']['pager'][] = ['name' => $i, 'url' => app\url($url, $lp), 'active' => $i === $cur];
        }

        if ($cur < $pages) {
            $lp = ['cur' => $cur + 1] + $p;
            $§['vars']['pager'][] = ['name' => app\i18n('Next'), 'url' => app\url($url, $lp)];
        }

        if ($cur > 1) {
            $p['cur'] = $cur;
        }
    }

    $§['vars']['ent'] = $ent;
    $§['vars']['max'] = min($opt['offset'] + $opt['limit'], $§['vars']['size']);
    $§['vars']['min'] = $opt['offset'] + 1;
    $§['vars']['param'] = $p;
    $§['vars']['title'] = $ent['name'];
    $§['vars']['url'] = $url;

    return tpl($§);
}

/**
 * Form
 */
function form(array $§): string
{
    $§['vars'] = arr\replace(['attr' => [], 'data' => [], 'title' => null], $§['vars']);

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
    $§['vars'] = arr\replace(['title' => null], $§['vars']);
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
    $§['vars'] = arr\replace(['attr' => [], 'data' => []], $§['vars']);

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
    $§['vars'] = arr\replace(['data' => [], 'tag' => 'nav'], $§['vars']);

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
    $§['vars'] = arr\replace(['tag' => 'nav'], $§['vars']);
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
    $§['vars'] = arr\replace(['tag' => 'nav'], $§['vars']);
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
