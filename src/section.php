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
    if (empty($section['vars']['entity']) || !($meta = data('meta', $section['vars']['entity']))) {
        return '';
    }

    $section['vars'] = array_replace(
        ['criteria' => null, 'index' => null, 'order' => null, 'limit' => config('limit.section')],
        $section['vars']
    );
    $section['vars']['data'] = model_load(
        $section['vars']['entity'],
        $section['vars']['criteria'],
        $section['vars']['index'],
        $section['vars']['order'],
        $section['vars']['limit'] ? (array) $section['vars']['limit'] : null
    );
    $section['vars']['header'] = _($meta['name']);

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
 * Menu section
 *
 * @param array $section
 *
 * @return string
 */
function section_menu(array & $section): string
{
    if (empty($section['vars']['root_id']) || empty($section['vars']['entity'])) {
        return '';
    }

    $data = model_load($section['vars']['entity'], ['root_id' => $section['vars']['root_id']], ['root_id', 'id']);
    $count = count($data);
    $level = 0;
    $i = 0;
    $html = '';

    foreach ($data as $item) {
        $i++;
        $class = $item['target'] === request('path') ? ' class="current"' : '';
        $end = $i === $count ? str_repeat('</li></ul>', $item['level']) : '';

        if ($item['level'] > $level) {
            $start = '<ul><li' . $class . '>';
        } elseif ($item['level'] < $level) {
            $start = '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>';
        } else {
            $start = '</li><li' . $class . '>';
        }

        $html .= $start . '<a href="' . url($item['target']) . '"' . $class . '>' . $item['name'] . '</a>' . $end;
        $level = $item['level'];
    }

    return $html ? '<nav id="' . $section['id'] . '">' . $html . '</nav>' : '';
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
 * Toolbar section
 *
 * @param array $section
 *
 * @return string
 */
function section_toolbar(array & $section): string
{
    if (!isset($section['vars']['data'])) {
        $section['vars']['data'] = data('toolbar');
    } elseif (empty($section['vars']['data'])) {
        return '';
    }

    foreach ($section['vars']['data'] as $key => $item) {
        if (!empty($item['children'])) {
            $child = $section;
            $child['id'] .= '.' . uniqid(mt_rand(), true);
            $child['parent'] = $section['id'];
            $child['vars']['data'] = $item['children'];
            $item['children'] = $section['vars']['data'][$key]['children'] = section_toolbar($child);
        } else {
            $item['children'] = $section['vars']['data'][$key]['children'] = '';
        }

        if (!empty($item['controller']) && !allowed($item['controller'])
            || empty($item['url']) && empty($item['children'])
        ) {
            unset($section['vars']['data'][$key]);
        }
    }

    return !empty($section['vars']['data']) ? section_template($section) : '';
}
