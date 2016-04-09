<?php
namespace akilli;

/**
 * HTML Block
 *
 * @param array $block
 *
 * @return string
 */
function block_template(array & $block): string
{
    $ƒ = function ($key = null, $default = null) use ($block) {
        if ($key === null) {
            return isset($block['vars']) && is_array($block['vars']) ? $block['vars'] : [];
        }

        return $block['vars'][$key] ?? $default;
    };
    $output = function (string $§) use ($ƒ) {
        ob_start();
        include path('template', $§);

        return ob_get_clean();
    };

    return $output($block['template']);
}

/**
 * Container Block
 *
 * @param array $block
 *
 * @return string
 */
function block_container(array & $block): string
{
    $html = '';

    if (!empty($block['children']) && is_array($block['children'])) {
        asort($block['children'], SORT_NUMERIC);

        foreach (array_keys($block['children']) as $id) {
            $html .= render($id);
        }
    }

    return $html;
}

/**
 * Entity Block
 *
 * @param array $block
 *
 * @return string
 */
function block_entity(array & $block): string
{
    if (empty($block['vars']['entity']) || !($meta = data('meta', $block['vars']['entity']))) {
        return '';
    }

    $block['vars'] = array_replace(
        ['criteria' => null, 'index' => null, 'order' => null, 'limit' => config('limit.block')],
        $block['vars']
    );
    $block['vars']['data'] = model_load(
        $block['vars']['entity'],
        $block['vars']['criteria'],
        $block['vars']['index'],
        $block['vars']['order'],
        $block['vars']['limit'] ? (array) $block['vars']['limit'] : null
    );
    $block['vars']['header'] = _($meta['name']);

    return block_template($block);
}

/**
 * Pager Block
 *
 * @param array $block
 *
 * @return string
 */
function block_pager(array & $block): string
{
    if (empty($block['vars']['pages']) || $block['vars']['pages'] < 1 || empty($block['vars']['page'])) {
        return '';
    }

    if (empty($block['vars']['params'])) {
        $block['vars']['params'] = [];
    }

    $block['vars']['first'] = url('*/*', $block['vars']['params']);

    if ($block['vars']['pages'] === 1) {
        $block['vars']['last'] = $block['vars']['first'];
    } else {
        $block['vars']['last'] = url(
            '*/*',
            array_replace($block['vars']['params'], ['page' => $block['vars']['pages']])
        );
    }

    $block['vars']['previous'] = $block['vars']['next'] = '#';

    if ($block['vars']['page'] < $block['vars']['pages']) {
        $block['vars']['next'] = url(
            '*/*',
            array_replace($block['vars']['params'], ['page' => $block['vars']['page'] + 1])
        );
    }

    if ($block['vars']['page'] === 2) {
        $block['vars']['previous'] = url('*/*', $block['vars']['params']);
    } elseif ($block['vars']['page'] > 2) {
        $block['vars']['previous'] = url(
            '*/*',
            array_replace($block['vars']['params'], ['page' => $block['vars']['page'] - 1])
        );
    }

    return block_template($block);
}

/**
 * Menu Block
 *
 * @param array $block
 *
 * @return string
 */
function block_menu(array & $block): string
{
    if (empty($block['vars']['entity']) || !($meta = data('meta', $block['vars']['entity']))) {
        return '';
    }

    $collection = model_load($meta['attributes']['root_id']['options_entity']);
    $criteria = !empty($block['vars']['root_id']) ? ['root_id' => $block['vars']['root_id']] : null;
    $roots = model_load($block['vars']['entity'], $criteria, ['root_id', 'id']);
    $html = '';
    $class = empty($block['vars']['class']) ? '' : ' class="' . $block['vars']['class'] . '"';
    $rootsCount = count($roots);

    foreach ($roots as $rootId => $data) {
        $level = 0;
        $count = count($data);
        $i = 0;

        if ($rootsCount > 1 && !empty($collection[$rootId]['name'])) {
            $html .= '<h1>' . $collection[$rootId]['name'] . '</h1>';
        }

        foreach ($data as $item) {
            $i++;
            $current = $item['target'] === request('path') ? ' class="current"' : '';
            $end = $i === $count ? str_repeat('</li></ul>', $item['level']) : '';
            $description = !empty($item['description']) ? $item['description'] : $item['name'];

            if ($item['level'] > $level) {
                $start = '<ul><li' . $current . '>';
            } elseif ($item['level'] < $level) {
                $start = '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $current . '>';
            } else {
                $start = '</li><li' . $current . '>';
            }

            $html .= $start . '<a href="' . url($item['target']) . '" title="' . $description . '"'
                . $current . '>' . $item['name'] . '</a>' . $end;
            $level = $item['level'];
        }
    }

    return $html ? '<nav id="' . $block['id'] . '"' . $class . '>' . $html . '</nav>' : '';
}

/**
 * Message Block
 *
 * @param array $block
 *
 * @return string
 */
function block_message(array & $block): string
{
    if (!$block['vars']['data'] = session('message')) {
        return '';
    }

    session('message', null, true);

    return block_template($block);
}

/**
 * Toolbar Block
 *
 * @param array $block
 *
 * @return string
 */
function block_toolbar(array & $block): string
{
    if (!isset($block['vars']['data'])) {
        $block['vars']['data'] = toolbar();
    } elseif (empty($block['vars']['data'])) {
        return '';
    }

    foreach ($block['vars']['data'] as $key => $item) {
        if (!empty($item['children'])) {
            $child = $block;
            $child['id'] .= '.' . uniqid(mt_rand(), true);
            $child['parent'] = $block['id'];
            $child['vars']['data'] = $item['children'];
            $item['children'] = $block['vars']['data'][$key]['children'] = block_toolbar($child);
        } else {
            $item['children'] = $block['vars']['data'][$key]['children'] = '';
        }

        if (!empty($item['controller']) && !allowed($item['controller'])
            || empty($item['url']) && empty($item['children'])
        ) {
            unset($block['vars']['data'][$key]);
        }
    }

    return !empty($block['vars']['data']) ? block_template($block) : '';
}
