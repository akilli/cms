<?php
namespace block;

use akilli;
use config;
use i18n;
use http;
use model;
use role;
use session;
use toolbar;
use url;
use view;

/**
 * HTML Block
 *
 * @param array $block
 *
 * @return string
 */
function template(array & $block): string
{
    $block['template_path'] = akilli\template($block['template']);

    if (!$block['template'] || !$block['template_path']) {
        return '';
    }

    $block['data'] = function ($key = null, $default = null) use ($block) {
        if ($key === null) {
            return isset($block['vars']) && is_array($block['vars']) ? $block['vars'] : [];
        }

        return $block['vars'][$key] ?? $default;
    };

    // Output
    ob_start();

    include $block['template_path'];

    return ob_get_clean();
}

/**
 * Container Block
 *
 * @param array $block
 *
 * @return string
 */
function container(array & $block): string
{
    $html = '';

    if (!empty($block['children']) && is_array($block['children'])) {
        asort($block['children'], SORT_NUMERIC);

        foreach (array_keys($block['children']) as $id) {
            $html .= view\render($id);
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
function entity(array & $block): string
{
    if (empty($block['vars']['entity']) || !($metadata = akilli\data('metadata', $block['vars']['entity']))) {
        return '';
    }

    $block['vars'] = array_replace(
        ['criteria' => null, 'index' => null, 'order' => null, 'limit' => config\value('limit.block')],
        $block['vars']
    );
    $block['vars']['data'] = model\load(
        $block['vars']['entity'],
        $block['vars']['criteria'],
        $block['vars']['index'],
        $block['vars']['order'],
        $block['vars']['limit']
    );
    $block['vars']['header'] = i18n\translate($metadata['name']);

    return template($block);
}

/**
 * Pager Block
 *
 * @param array $block
 *
 * @return string
 */
function pager(array & $block): string
{
    if (empty($block['vars']['pages']) || $block['vars']['pages'] < 1 || empty($block['vars']['page'])) {
        return '';
    }

    if (empty($block['vars']['params'])) {
        $block['vars']['params'] = [];
    }

    $block['vars']['first'] = url\path('*/*', $block['vars']['params']);

    if ($block['vars']['pages'] === 1) {
        $block['vars']['last'] = $block['vars']['first'];
    } else {
        $block['vars']['last'] = url\path(
            '*/*',
            array_replace($block['vars']['params'], ['page' => $block['vars']['pages']])
        );
    }

    $block['vars']['previous'] = $block['vars']['next'] = '#';

    if ($block['vars']['page'] < $block['vars']['pages']) {
        $block['vars']['next'] = url\path(
            '*/*',
            array_replace($block['vars']['params'], ['page' => $block['vars']['page'] + 1])
        );
    }

    if ($block['vars']['page'] === 2) {
        $block['vars']['previous'] = url\path('*/*', $block['vars']['params']);
    } elseif ($block['vars']['page'] > 2) {
        $block['vars']['previous'] = url\path(
            '*/*',
            array_replace($block['vars']['params'], ['page' => $block['vars']['page'] - 1])
        );
    }

    return template($block);
}

/**
 * Menu Block
 *
 * @param array $block
 *
 * @return string
 */
function menu(array & $block): string
{
    if (empty($block['vars']['entity']) || !($metadata = akilli\data('metadata', $block['vars']['entity']))) {
        return '';
    }

    $root = !empty($metadata['attributes']['root_id']);

    if ($root) {
        $collection = model\load($metadata['attributes']['root_id']['foreign_entity_id']);
        $criteria = !empty($block['vars']['root_id']) ? ['root_id' => $block['vars']['root_id']] : null;
        $roots = model\load($block['vars']['entity'], $criteria, ['root_id', 'id']);
    } else {
        $roots[] = model\load($block['vars']['entity']);
    }

    $html = '';
    $class = empty($block['vars']['class']) ? '' : ' class="' . $block['vars']['class'] . '"';
    $rootsCount = count($roots);

    foreach ($roots as $rootId => $data) {
        $level = 0;
        $count = count($data);
        $i = 0;

        if ($root && $rootsCount > 1 && !empty($collection[$rootId]['name'])) {
            $html .= '<h1>' . $collection[$rootId]['name'] . '</h1>';
        }

        foreach ($data as $item) {
            $i++;
            $current = $item['target'] === http\request('path') ? ' class="current"' : '';
            $end = $i === $count ? str_repeat('</li></ul>', $item['level']) : '';
            $description = !empty($item['description']) ? $item['description'] : $item['name'];

            if ($item['level'] > $level) {
                $start = '<ul><li' . $current . '>';
            } elseif ($item['level'] < $level) {
                $start = '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $current . '>';
            } else {
                $start = '</li><li' . $current . '>';
            }

            $html .= $start . '<a href="' . url\path($item['target']) . '" title="' . $description . '"'
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
function message(array & $block): string
{
    if (!$block['vars']['data'] = session\data('message')) {
        return '';
    }

    session\data('message', null, true);

    return template($block);
}

/**
 * Toolbar Block
 *
 * @param array $block
 *
 * @return string
 */
function toolbar(array & $block): string
{
    if (!isset($block['vars']['data'])) {
        $block['vars']['data'] = toolbar\data();
    } elseif (empty($block['vars']['data'])) {
        return '';
    }

    foreach ($block['vars']['data'] as $key => $item) {
        if (!empty($item['children'])) {
            $child = $block;
            $child['id'] .= '.' . uniqid(mt_rand(), true);
            $child['parent'] = $block['id'];
            $child['vars']['data'] = $item['children'];
            $item['children'] = $block['vars']['data'][$key]['children'] = toolbar($child);
        } else {
            $item['children'] = $block['vars']['data'][$key]['children'] = '';
        }

        if (!empty($item['controller']) && !role\allowed($item['controller'])
            || empty($item['url']) && empty($item['children'])
        ) {
            unset($block['vars']['data'][$key]);
        }
    }

    return !empty($block['vars']['data']) ? template($block) : '';
}
