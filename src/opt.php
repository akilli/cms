<?php
namespace qnd;

/**
 * Option
 *
 * @param array $attr
 *
 * @return array
 */
function opt(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return [_('No'), _('Yes')];
    }

    if (empty($attr['opt'][0]) || !is_string($attr['opt'][0]) && !is_array($attr['opt'][0])) {
        return [];
    }

    if (is_array($attr['opt'][0])) {
        return $attr['opt'][0];
    }

    // Cache
    $data = & registry('opt');
    $key = $attr['entity'] . '.' . $attr['uid'];

    if (isset($data[$key])) {
        return $data[$key];
    }

    if ($attr['type'] === 'entity') {
        $data[$key] = array_column(all($attr['opt'][0]), 'name', 'id');
    } else {
        $call = fqn($attr['opt'][0]);
        $params = $attr['opt'][1] ?? [];
        $data[$key] = $call(...$params);
    }

    return $data[$key];
}

/**
 * Attribute options
 *
 * @return array
 */
function opt_attr(): array
{
    return array_map(
        function ($item) {
            return $item['name'];
        },
        data('attr')
    );
}

/**
 * Privilege options
 *
 * @return array
 */
function opt_privilege(): array
{
    return array_map(
        function ($item) {
            return $item['name'];
        },
        array_filter(
            data('privilege'),
            function ($item) {
                return !empty($item['active']) && empty($item['callback']);
            }
        )
    );
}

/**
 * Theme options
 *
 * @return array
 */
function opt_theme(): array
{
    $data = [];

    foreach (glob(path('theme', '*'), GLOB_ONLYDIR) as $dir) {
        $theme = basename($dir);
        $data[$theme] = $theme;
    }

    return $data;
}

/**
 * Menu options
 *
 * @return array
 */
function opt_position(): array
{
    $nodes = all('node', [], ['index' => ['root_id', 'id']]);
    $data = [];

    foreach (all('menu') as $id => $menu) {
        $data[$id  . ':0'] = $menu['name'];

        if (!empty($nodes[$id])) {
            foreach ($nodes[$id] as $node) {
                $data[$node['pos']] = str_repeat('&nbsp;', $node['level'] * 4) . $node['name'];
            }
        }
    }

    return $data;
}
