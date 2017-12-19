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
 * Entity section
 */
function ent(array $§): string
{
    $§['vars'] = arr\replace(['act' => null, 'crit' => [], 'ent' => null, 'opt' => []], $§['vars']);
    $p = [$§['vars']['ent'], $§['vars']['crit'], $§['vars']['opt']];
    $§['vars'] = ['data' => ent\one(...$p), 'act' => $§['vars']['act']];

    return tpl($§);
}

/**
 * Index section
 */
function index(array $§): string
{
    $§['vars'] = arr\replace(['act' => null, 'crit' => [], 'ent' => null, 'opt' => [], 'params' => []], $§['vars']);
    $p = [$§['vars']['ent'], $§['vars']['crit'], $§['vars']['opt']];
    $§['vars'] = ['data' => ent\all(...$p), 'act' => $§['vars']['act'], 'params' => $§['vars']['params']];

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
 * Navigation section
 */
function nav(array $§): string
{
    $§['vars'] = arr\replace(['mode' => null], $§['vars']);
    $id = http\req('id');
    $cur = http\req('ent') === 'page' && $id ? ent\one('page', [['id', $id], ['status', 'published']]) : null;
    $anc = $cur && count($cur['path']) > 1 ? ent\one('page', [['id', $cur['path'][0]]]) : $cur;
    $crit = [['status', 'published']];

    if ($§['vars']['mode'] === 'top') {
        $cur = $anc;
        $crit[] = ['level', 1];
    } elseif ($§['vars']['mode'] === 'sub' && !$anc) {
        return '';
    } elseif ($§['vars']['mode'] === 'sub') {
        $crit[] = ['pos', $anc['pos'] . '.', APP['crit']['~^']];
    }

    if (!$nav = ent\all('page', $crit, ['select' => ['id', 'name', 'url', 'level'], 'order' => ['pos' => 'asc']])) {
        return '';
    }

    $count = count($nav);
    $level = 0;
    $i = 0;
    $html = '';

    foreach ($nav as $page) {
        $a = ['href' => $page['url']];
        $class = '';

        if ($cur && $cur['id'] === $page['id']) {
            $a['class'] = 'active';
            $class .= ' class="active"';
        }

        if ($page['level'] > $level) {
             $html .= '<ul><li' . $class . '>';
        } elseif ($page['level'] < $level) {
             $html .= '</li>' . str_repeat('</ul></li>', $level - $page['level']) . '<li' . $class . '>';
        } else {
             $html .= '</li><li' . $class . '>';
        }

        $html .= html\tag('a', $a, $page['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $page['level']) : '';
        $level = $page['level'];
    }

    return html\tag('nav', ['id' => $§['id']], $html);
}

/**
 * Pager section
 */
function pager(array $§): string
{
    $§['vars'] = arr\replace(['limit' => 0, 'params' => [], 'size' => 0], $§['vars']);

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
    $§['vars']['links'] = [];

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
