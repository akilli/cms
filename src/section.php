<?php
declare(strict_types = 1);

namespace section;

use app;
use arr;
use ent;
use html;
use http;
use session;

/**
 * Container section
 */
function container(array $§): string
{
    $§['vars'] = arr\replace(['tag' => null], $§['vars']);
    $html = '';

    foreach (arr\order(arr\crit(app\layout(), [['parent_id', $§['id']]]), ['sort' => 'asc']) as $child) {
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
    $ent = app\cfg('ent', $§['vars']['eId'] ?: http\req('ent'));
    unset($§['vars']['act'], $§['vars']['eId']);

    if (!$act || !$ent || !isset($ent['act'][$act])) {
        return '';
    }

    $§['vars']['attr'] = $ent['act'][$act];
    $opt = ['limit' => app\cfg('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && $ent['version']) {
        $crit[] = ['status', 'published'];
    }

    $p = ['cur' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $p += $act === 'browser' ? ['CKEditorFuncNum' => null] : [];
    $p = arr\replace($p, http\req('param'));

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
    $p['cur'] = min(max($p['cur'], 1), $pages);
    $opt['offset'] = ($p['cur'] - 1) * $opt['limit'];

    if ($p['sort'] && in_array($p['sort'], $§['vars']['attr'])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        unset($p['sort'], $p['dir']);
    }

    $§['vars']['data'] = ent\all($ent['id'], $crit, $opt);
    $cur = max($p['cur'] ?? 0, 1);
    $pages = (int) ceil($§['vars']['size'] / $opt['limit']);
    $§['vars']['offset'] = ($cur - 1) * $opt['limit'];
    $cfg = app\cfg('app', 'pager');
    $min = max(1, min($cur - intdiv($cfg, 2), $pages - $cfg + 1));
    $max = min($min + $cfg - 1, $pages);
    $§['vars']['pager'] = [];

    if ($cur >= 2) {
        $§['vars']['pager'][] = ['name' => app\i18n('Previous'), 'param' => ['cur' => $cur - 1] + $p];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $§['vars']['pager'][] = ['name' => $i, 'param' => ['cur' => $i] + $p, 'active' => $i === $cur];
    }

    if ($cur < $pages) {
        $§['vars']['pager'][] = ['name' => app\i18n('Next'), 'param' => ['cur' => $cur + 1] + $p];
    }

    $§['vars']['ent'] = $ent;
    $§['vars']['limit'] = $opt['limit'];
    $§['vars']['param'] = $p;

    return tpl($§);
}

/**
 * Menu section
 */
function menu(array $§): string
{
    $§['vars'] = arr\replace(['mode' => null], $§['vars']);
    $id = http\req('id');
    $cur = http\req('ent') === 'page' && $id ? ent\one('page', [['id', $id], ['status', 'published']]) : null;
    $anc = $cur && count($cur['path']) > 1 ? ent\one('page', [['id', $cur['path'][0]], ['status', 'published']]) : $cur;
    $crit = [['status', 'published']];
    $opt = ['select' => ['id', 'name', 'url', 'parent_id', 'level', 'path'], 'order' => ['pos' => 'asc']];

    if ($§['vars']['mode'] === 'sub') {
        if (!$anc) {
            return '';
        }

        $crit[] = ['parent_id', null, APP['crit']['!=']];
        $crit[] = [['id', $cur['path']], ['parent_id', [$anc['id'], $cur['id'], $cur['parent_id']]]];
    } elseif ($§['vars']['mode'] === 'top') {
        $cur = $anc;
        $crit[] = ['parent_id', null];
    }

    if (!$menu = ent\all('page', $crit, $opt)) {
        return '';
    }

    $count = count($menu);
    $level = 0;
    $i = 0;
    $html = '';

    foreach ($menu as $item) {
        if ($item['parent_id'] && ($§['vars']['mode'] !== 'sub' || $item['parent_id'] !== $anc['id']) && empty($menu[$item['parent_id']])) {
            continue;
        }

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
    $§['vars'] = ['id' => $§['id'], 'tpl' => $§['tpl']] + $§['vars'];
    $§ = function ($key) use ($§) {
        return $§['vars'][$key] ?? null;
    };
    ob_start();
    include app\tpl((string) $§('tpl'));

    return ob_get_clean();
}
