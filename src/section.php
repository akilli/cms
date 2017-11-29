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
    $§['vars']['tag'] = $§['vars']['tag'] ?? null;
    $html = '';

    foreach (arr\order(arr\crit(app\layout(), [['parent_id', $§['id']]]), ['sort' => 'asc']) as $child) {
        $html .= app\§($child['id']);
    }

    return $html && $§['vars']['tag'] ? html\tag($§['vars']['tag'], ['id' => $§['id']], $html) : $html;
}

/**
 * Message section
 */
function msg(array $§): string
{
    if (!$§['vars']['data'] = session\get('msg')) {
        return '';
    }

    session\set('msg', null);

    return tpl($§);
}

/**
 * Navigation section
 */
function nav(array $§): string
{
    $§['vars'] += ['mode' => null, 'current' => http\req('id')];
    $cur = $§['vars']['current'] ? ent\one('page', [['id', $§['vars']['current']], ['active', true]]) : null;
    $anc = $cur && count($cur['path']) > 1 ? ent\one('page', [['id', $cur['path'][0]]]) : $cur;
    $crit = [['active', true]];

    if ($§['vars']['mode'] === 'top') {
        $cur = $anc;
        $crit[] = ['depth', 1];
    } elseif ($§['vars']['mode'] === 'sub') {
        if (!$anc) {
            return '';
        }

        $crit[] = ['pos', $anc['pos'] . '.', APP['crit']['~^']];
    }

    if (!$nav = ent\all('page', $crit, ['select' => ['id', 'name', 'url', 'depth'], 'order' => ['pos' => 'asc']])) {
        return '';
    }

    $count = count($nav);
    $depth = 0;
    $i = 0;
    $html = '';

    foreach ($nav as $page) {
        $a = ['href' => $page['url']];
        $class = '';

        if ($cur && $cur['id'] === $page['id']) {
            $a['class'] = 'active';
            $class .= ' class="active"';
        }

        if ($page['depth'] > $depth) {
             $html .= '<ul><li' . $class . '>';
        } elseif ($page['depth'] < $depth) {
             $html .= '</li>' . str_repeat('</ul></li>', $depth - $page['depth']) . '<li' . $class . '>';
        } else {
             $html .= '</li><li' . $class . '>';
        }

        $html .= html\tag('a', $a, $page['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $page['depth']) : '';
        $depth = $page['depth'];
    }

    return html\tag('nav', ['id' => $§['id']], $html);
}

/**
 * Pager section
 */
function pager(array $§): string
{
    $§['vars'] += ['size' => 0, 'limit' => 0, 'links' => [], 'params' => []];

    if ($§['vars']['size'] < 1 || $§['vars']['limit'] < 1) {
        return '';
    }

    $cur = max($§['vars']['params']['cur'] ?? 0, 1);
    $p = $§['vars']['params'];
    unset($§['vars']['params']);
    $pages = (int) ceil($§['vars']['size'] / $§['vars']['limit']);
    $§['vars']['offset'] = ($cur - 1) * $§['vars']['limit'];
    $cfg = app\cfg('app', 'pager');
    $min = max(1, min($cur - intdiv($cfg, 2), $pages - $cfg + 1));
    $max = min($min + $cfg - 1, $pages);

    if ($cur >= 2) {
        $§['vars']['links'][] = ['name' => app\i18n('Previous'), 'params' => ['cur' => $cur - 1] + $p];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $§['vars']['links'][] = ['name' => $i, 'params' => ['cur' => $i] + $p, 'active' => $i === $cur];
    }

    if ($cur < $pages) {
        $§['vars']['links'][] = ['name' => app\i18n('Next'), 'params' => ['cur' => $cur + 1] + $p];
    }

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
