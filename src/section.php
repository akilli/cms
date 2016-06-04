<?php
namespace qnd;

/**
 * Template section
 *
 * @param array $§
 *
 * @return string
 */
function section_template(array & $§): string
{
    return render($§);
}

/**
 * Container section
 *
 * @param array $§
 *
 * @return string
 */
function section_container(array & $§): string
{
    $html = '';

    if (!empty($§['children']) && is_array($§['children'])) {
        asort($§['children'], SORT_NUMERIC);

        foreach (array_keys($§['children']) as $id) {
            $html .= §($id);
        }
    }

    return $html;
}

/**
 * Entity section
 *
 * @param array $§
 *
 * @return string
 */
function section_entity(array & $§): string
{
    if (empty($§['vars']['entity']) || !($entity = data('entity', $§['vars']['entity']))) {
        return '';
    }

    $§['vars'] = array_replace(['crit' => [], 'opts' => []], $§['vars']);
    $§['vars']['data'] = all($§['vars']['entity'], $§['vars']['crit'], $§['vars']['opts']);
    $§['vars']['title'] = _($entity['name']);

    return render($§);
}

/**
 * Pager section
 *
 * @param array $§
 *
 * @return string
 */
function section_pager(array & $§): string
{
    if (empty($§['vars']['size']) || $§['vars']['size'] < 1 || empty($§['vars']['limit']) || $§['vars']['limit'] < 1) {
        return '';
    }

    $§['vars'] += ['links' => [], 'params' => []];
    $§['vars']['pages'] = (int) ceil($§['vars']['size'] / $§['vars']['limit']);
    $§['vars']['page'] = max($§['vars']['params']['page'] ?? 0, 1);
    $§['vars']['offset'] = ($§['vars']['page'] - 1) * $§['vars']['limit'];
    unset($§['vars']['params']['page']);
    $c = max(0, config('entity.pager'));
    $min = max(1, $§['vars']['page'] - $c);
    $max = min($§['vars']['pages'], $§['vars']['page'] + $c);
    $min = $max - $min < 2 * $c && $max - 2 * $c >= 1 ? $max - 2 * $c : $min;
    $max = $max - $min < 2 * $c && $min + 2 * $c <= $§['vars']['pages'] ? $min + 2 * $c : $max;
    $prev = '#';
    $next = '#';

    if ($§['vars']['page'] === 2) {
        $prev = url('*/*', $§['vars']['params']);
    } elseif ($§['vars']['page'] > 2) {
        $prev = url('*/*', ['page' => $§['vars']['page'] - 1] + $§['vars']['params']);
    }

    if ($§['vars']['page'] < $§['vars']['pages']) {
        $next = url('*/*', ['page' => $§['vars']['page'] + 1] + $§['vars']['params']);
    }

    $§['vars']['links'][] = html_tag('a', ['href' => $prev], _('Previous'));

    for ($i = $min; $i <= $max; $i++) {
        if ($i === $§['vars']['page']) {
            $§['vars']['links'][] = html_tag('span', [], $i);
        } else {
            $p = $i > 1 ? ['page' => $i] : [];
            $§['vars']['links'][] = html_tag('a', ['href' => url('*/*', $p + $§['vars']['params'])], $i);
        }
    }

    $§['vars']['links'][] = html_tag('a', ['href' => $next], _('Next'));

    return render($§);
}

/**
 * Message section
 *
 * @param array $§
 *
 * @return string
 */
function section_message(array & $§): string
{
    if (!$§['vars']['data'] = session('message')) {
        return '';
    }

    session('message', null, true);

    return render($§);
}

/**
 * Node section
 *
 * @param array $§
 *
 * @return string
 */
function section_node(array & $§): string
{
    if (empty($§['vars']['crit']) || !$data = all('node', $§['vars']['crit'])) {
        return '';
    }

    $count = count($data);
    $level = 0;
    $i = 0;
    $html = '';

    if (!empty($§['vars']['title'])) {
        $html .= html_tag('h1', [], $§['vars']['title']);
    }

    foreach ($data as $item) {
        $attrs = [];
        $class = '';

        if ($item['target'] === request('path')) {
            $attrs['class'] = 'active';
            $class .= ' class="active"';
        }

        if ($item['level'] > $level) {
             $html .= '<ul><li' . $class . '>';
        } elseif ($item['level'] < $level) {
             $html .= '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>';
        } else {
             $html .= '</li><li' . $class . '>';
        }

        if ($item['target'] !== '#') {
            $attrs['href'] = url($item['target']);
            $html .= html_tag('a', $attrs, $item['name']);
        } else {
            $html .= html_tag('span', [], $item['name']);
        }

        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return html_tag('nav', ['id' => $§['id']], $html);
}
