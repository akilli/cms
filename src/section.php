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
    if (empty($§['vars']['pages']) || $§['vars']['pages'] < 1 || empty($§['vars']['page'])) {
        return '';
    }

    if (empty($§['vars']['params'])) {
        $§['vars']['params'] = [];
    }

    $§['vars']['prev'] = $§['vars']['next'] = '#';

    if ($§['vars']['page'] < $§['vars']['pages']) {
        $§['vars']['next'] = url('*/*', array_replace($§['vars']['params'], ['page' => $§['vars']['page'] + 1]));
    }

    if ($§['vars']['page'] === 2) {
        $§['vars']['prev'] = url('*/*', $§['vars']['params']);
    } elseif ($§['vars']['page'] > 2) {
        $§['vars']['prev'] = url('*/*', array_replace($§['vars']['params'], ['page' => $§['vars']['page'] - 1]));
    }

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
