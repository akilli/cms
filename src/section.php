<?php
declare(strict_types = 1);

namespace cms;

/**
 * Container section
 *
 * @param array $§
 *
 * @return string
 */
function section_container(array $§): string
{
    $§['vars']['tag'] = $§['vars']['tag'] ?? null;
    $html = '';

    if (!empty($§['children']) && is_array($§['children'])) {
        asort($§['children'], SORT_NUMERIC);

        foreach (array_keys($§['children']) as $id) {
            $html .= section($id);
        }
    }

    return $html && $§['vars']['tag'] ? html($§['vars']['tag'], ['id' => $§['id']], $html) : $html;
}

/**
 * Message section
 *
 * @param array $§
 *
 * @return string
 */
function section_message(array $§): string
{
    if (!$§['vars']['data'] = session_get('message')) {
        return '';
    }

    session_set('message', null);

    return section_template($§);
}

/**
 * Navigation section
 *
 * @param array $§
 *
 * @return string
 */
function section_nav(array $§): string
{
    $§['vars'] += ['mode' => null, 'current' => request('id')];
    $cur = $§['vars']['current'] ? one('page', [['id', $§['vars']['current']]]) : null;
    $anc = $cur && count($cur['path']) > 1 ? one('page', [['id', $cur['path'][0]]]) : $cur;
    $crit = [];

    if ($§['vars']['mode'] === 'top') {
        $cur = $anc;
        $crit = [['depth', 1]];
    } elseif ($§['vars']['mode'] === 'sub') {
        if (!$anc) {
            return '';
        }

        $crit = [['pos', $anc['pos'] . '.', CRIT['~^']]];
    }

    if (!$nav = all('page', $crit, ['select' => ['id', 'name', 'url', 'depth'], 'order' => ['pos' => 'asc']])) {
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

        $html .= html('a', $a, $page['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $page['depth']) : '';
        $depth = $page['depth'];
    }

    return html('nav', ['id' => $§['id']], $html);
}

/**
 * Pager section
 *
 * @param array $§
 *
 * @return string
 */
function section_pager(array $§): string
{
    $§['vars'] += ['size' => 0, 'limit' => 0, 'links' => [], 'params' => []];

    if ($§['vars']['size'] < 1 || $§['vars']['limit'] < 1) {
        return '';
    }

    $§['vars']['pages'] = (int) ceil($§['vars']['size'] / $§['vars']['limit']);
    $§['vars']['page'] = max($§['vars']['params']['page'] ?? 0, 1);
    $§['vars']['offset'] = ($§['vars']['page'] - 1) * $§['vars']['limit'];
    unset($§['vars']['params']['page']);
    $c = max(0, data('app', 'pager'));
    $min = max(1, min($§['vars']['page'] - intdiv($c, 2), $§['vars']['pages'] - $c + 1));
    $max = min($min + $c - 1, $§['vars']['pages']);

    if ($§['vars']['page'] >= 2) {
        $p = ['page' => $§['vars']['page'] - 1] + $§['vars']['params'];
        $§['vars']['links'][] = ['name' => _('Previous'), 'params' => $p];
    }

    for ($i = $min; $min < $max && $i <= $max; $i++) {
        $p = ['page' => $i] + $§['vars']['params'];
        $§['vars']['links'][] = ['name' => $i, 'params' => $p, 'active' => $i === $§['vars']['page']];
    }

    if ($§['vars']['page'] < $§['vars']['pages']) {
        $p = ['page' => $§['vars']['page'] + 1] + $§['vars']['params'];
        $§['vars']['links'][] = ['name' => _('Next'), 'params' => $p];
    }

    return section_template($§);
}

/**
 * Template section
 *
 * @param array $§
 *
 * @return string
 */
function section_template(array $§): string
{
    $§['vars'] = ['id' => $§['id'], 'template' => $§['template']] + $§['vars'];
    $§ = function ($key) use ($§) {
        return $§['vars'][$key] ?? null;
    };
    ob_start();
    include path('template', $§('template'));

    return ob_get_clean();
}
