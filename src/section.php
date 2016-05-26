<?php
namespace qnd;

/**
 * HTML section
 *
 * @param array $section
 *
 * @return string
 */
function section_template(array & $section): string
{
    $§ = function ($key = null, $default = null) use ($section) {
        if ($key === null) {
            return isset($section['vars']) && is_array($section['vars']) ? $section['vars'] : [];
        }

        return $section['vars'][$key] ?? $default;
    };
    $output = function (string $ţ) use ($§) {
        ob_start();
        include path('template', $ţ);

        return ob_get_clean();
    };

    return $output($section['template']);
}

/**
 * Container section
 *
 * @param array $section
 *
 * @return string
 */
function section_container(array & $section): string
{
    $html = '';

    if (!empty($section['children']) && is_array($section['children'])) {
        asort($section['children'], SORT_NUMERIC);

        foreach (array_keys($section['children']) as $id) {
            $html .= §($id);
        }
    }

    return $html;
}

/**
 * Entity section
 *
 * @param array $section
 *
 * @return string
 */
function section_entity(array & $section): string
{
    if (empty($section['vars']['entity']) || !($entity = data('entity', $section['vars']['entity']))) {
        return '';
    }

    $section['vars'] = array_replace(['crit' => [], 'opts' => []], $section['vars']);
    $section['vars']['data'] = all($section['vars']['entity'], $section['vars']['crit'], $section['vars']['opts']);
    $section['vars']['title'] = _($entity['name']);

    return section_template($section);
}

/**
 * Pager section
 *
 * @param array $section
 *
 * @return string
 */
function section_pager(array & $section): string
{
    if (empty($section['vars']['pages']) || $section['vars']['pages'] < 1 || empty($section['vars']['page'])) {
        return '';
    }

    if (empty($section['vars']['params'])) {
        $section['vars']['params'] = [];
    }

    $section['vars']['first'] = url('*/*', $section['vars']['params']);

    if ($section['vars']['pages'] === 1) {
        $section['vars']['last'] = $section['vars']['first'];
    } else {
        $section['vars']['last'] = url(
            '*/*',
            array_replace($section['vars']['params'], ['page' => $section['vars']['pages']])
        );
    }

    $section['vars']['previous'] = $section['vars']['next'] = '#';

    if ($section['vars']['page'] < $section['vars']['pages']) {
        $section['vars']['next'] = url(
            '*/*',
            array_replace($section['vars']['params'], ['page' => $section['vars']['page'] + 1])
        );
    }

    if ($section['vars']['page'] === 2) {
        $section['vars']['previous'] = url('*/*', $section['vars']['params']);
    } elseif ($section['vars']['page'] > 2) {
        $section['vars']['previous'] = url(
            '*/*',
            array_replace($section['vars']['params'], ['page' => $section['vars']['page'] - 1])
        );
    }

    return section_template($section);
}

/**
 * Message section
 *
 * @param array $section
 *
 * @return string
 */
function section_message(array & $section): string
{
    if (!$section['vars']['data'] = session('message')) {
        return '';
    }

    session('message', null, true);

    return section_template($section);
}

/**
 * Node section
 *
 * @param array $section
 *
 * @return string
 */
function section_node(array & $section): string
{
    if (empty($section['vars']['root_id']) || !$data = all('node', ['root_id' => $section['vars']['root_id']])) {
        return '';
    }

    $count = count($data);
    $level = 0;
    $i = 0;
    $html = '';

    if (!empty($section['vars']['title'])) {
        $html .= '<h1>' . $section['vars']['title'] . '</h1>';
    }

    foreach ($data as $item) {
        $class = $item['target'] && $item['target'] === request('path') ? ' class="active"' : '';

        if ($item['level'] > $level) {
             $html .= '<ul><li' . $class . '>';
        } elseif ($item['level'] < $level) {
             $html .= '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>';
        } else {
             $html .= '</li><li' . $class . '>';
        }

        if ($item['target']) {
            $html .= '<a href="' . url($item['target']) . '"' . $class . '>' . $item['name'] . '</a>';
        } else {
            $html .= '<span>' . $item['name'] . '</span>';
        }

        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return '<nav id="' . $section['id'] . '">' . $html . '</nav>';
}
