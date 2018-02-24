<?php
declare(strict_types = 1);

namespace section;

use app;
use arr;
use ent;
use html;
use req;
use session;

/**
 * Container section
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
 * Index section
 */
function index(array $§): string
{
    $§['vars'] = arr\replace(['act' => null, 'eId' => null], $§['vars']);
    $act = $§['vars']['act'];
    $ent = app\cfg('ent', $§['vars']['eId'] ?: app\data('ent'));
    unset($§['vars']['act'], $§['vars']['eId']);

    if (!$act || !$ent || !isset($ent['act'][$act])) {
        return '';
    }

    $crit = $act !== 'admin' && in_array('page', [$ent['id'], $ent['parent']]) ? [['status', 'published']] : [];
    $opt = ['limit' => app\cfg('app', 'limit')];
    $p = ['cur' => 0, 'q' => '', 'sort' => null, 'dir' => null];

    if ($act === 'browser') {
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
    $§['vars']['attr'] = ent\attr($ent, $act);

    if ($p['sort'] && !empty($§['vars']['attr'][$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        unset($p['sort'], $p['dir']);
        $opt['order'] = ['id' => 'desc'];
    }

    $§['vars']['data'] = ent\all($ent['id'], $crit, $opt);
    $cfg = app\cfg('app', 'pager');
    $min = max(1, min($cur - intdiv($cfg, 2), $pages - $cfg + 1));
    $max = min($min + $cfg - 1, $pages);
    $url = req\data('url');
    $§['vars']['pager'] = [];

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

    $§['vars']['ent'] = $ent;
    $§['vars']['max'] = min($opt['offset'] + $opt['limit'], $§['vars']['size']);
    $§['vars']['min'] = $opt['offset'] + 1;
    $§['vars']['param'] = $p;
    $§['vars']['title'] = $ent['name'];
    $§['vars']['url'] = $url;

    return tpl($§);
}

/**
 * Menu section
 */
function menu(array $§): string
{
    $§['vars'] = [];
    $cur = ent\one('page', [['url', req\data('url')], ['status', 'published']]);
    $crit = [['status', 'published'], ['menu', true], ['level', 0, APP['crit']['>']]];
    $opt = ['order' => ['pos' => 'asc']];

    if (!$menu = ent\all('page', $crit, $opt)) {
        return '';
    }

    $count = count($menu);
    $level = 0;
    $i = 0;
    $html = '';

    foreach ($menu as $item) {
        $a = ['href' => $item['url']];
        $class = '';

        if ($cur && $cur['id'] === $item['id']) {
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

        $html .= html\tag('a', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return html\tag('nav', ['id' => $§['id']], $html);
}

/**
 * Meta section
 */
function meta(array $§): string
{
    $§['vars'] = arr\replace(['desc' => null, 'title' => null], $§['vars']);
    $§['vars']['title'] = $§['vars']['title'] ?: app\cfg('app', 'name');

    return tpl($§);
}

/**
 * Message section
 */
function msg(array $§): string
{
    $§['vars'] = [];

    if (!$§['vars']['data'] = session\get('msg')) {
        return '';
    }

    session\set('msg', null);

    return tpl($§);
}

/**
 * Template section
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
