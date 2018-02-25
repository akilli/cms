<?php
declare(strict_types = 1);

namespace section;

use app;
use arr;
use ent;
use html;
use req;
use session;
use DomainException;

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

/**
 * Meta section
 */
function meta(array $§): string
{
    $§['vars'] = arr\replace(['desc' => '', 'title' => app\cfg('app', 'name')], $§['vars']);

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
 * Index section
 */
function index(array $§): string
{
    $§['vars'] = arr\replace(['act' => 'index', 'eId' => null], $§['vars']);
    $ent = app\cfg('ent', $§['vars']['eId'] ?: app\data('ent'));
    unset($§['vars']['eId']);

    if (!$ent || !isset($ent['act'][$§['vars']['act']])) {
        return '';
    }

    $crit = $§['vars']['act'] !== 'admin' && in_array('page', [$ent['id'], $ent['parent']]) ? [['status', 'published']] : [];
    $opt = ['limit' => app\cfg('app', 'limit')];
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
    $§['vars']['attr'] = ent\attr($ent, $ent['act'][$§['vars']['act']]);

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
 * Nav section
 *
 * @throws DomainException
 */
function nav(array $§): string
{
    $§['vars'] = arr\replace(['data' => []], $§['vars']);

    if (!$§['vars']['data']) {
        return '';
    }

    $url = req\data('url');
    $count = count($§['vars']['data']);
    $level = 0;
    $i = 0;
    $html = '';

    foreach ($§['vars']['data'] as $item) {
        $item = arr\replace(['name' => null, 'url' => null, 'priv' => null, 'level' => 1], $item);

        if (!$item['name']) {
            throw new DomainException(app\i18n('Invalid data'));
        } elseif ($item['priv'] && !app\allowed($item['priv'])) {
            continue;
        }

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

    return html\tag('nav', ['id' => $§['id']], $html);
}

/**
 * Menu section
 */
function menu(array $§): string
{
    $§['vars'] = [];
    $crit = [['status', 'published'], ['menu', true], ['level', 0, APP['crit']['>']]];
    $opt = ['order' => ['pos' => 'asc']];
    $§['vars']['data'] = ent\all('page', $crit, $opt);

    return nav($§);
}

/**
 * Toolbar section
 */
function toolbar(array $§): string
{
    $§['vars'] = ['data' => app\cfg('toolbar')];

    return nav($§);
}
