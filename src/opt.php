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
    if ($attr['type'] === 'entity') {
        return all(...$attr['opt']);
    }

    if ($attr['backend'] === 'bool') {
        return [_('No'), _('Yes')];
    }

    if (empty($attr['opt'][0]) || !is_string($attr['opt'][0]) && !is_array($attr['opt'][0])) {
        return [];
    }

    if (is_array($attr['opt'][0])) {
        return $attr['opt'][0];
    }

    $callback = fqn($attr['opt'][0]);
    $params = $attr['opt'][1] ?? [];

    return $callback(...$params);
}

/**
 * Privilege options
 *
 * @return array
 */
function opt_privilege(): array
{
    return array_filter(
        data('privilege'),
        function ($item) {
            return !empty($item['active']) && empty($item['callback']);
        }
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
    $roots = all('menu');
    $data = [];

    foreach (all('node') as $item) {
        if (empty($data[$item['root_id']  . ':0'])) {
            $data[$item['root_id']  . ':0']['name'] = $roots[$item['root_id']]['name'];
            $data[$item['root_id']  . ':0']['class'] = 'group';
        }

        $data[$item['pos']]['name'] = $item['name'];
        $data[$item['pos']]['level'] = $item['level'];
    }

    // Add roots without items
    foreach ($roots as $id => $root) {
        if (empty($data[$id  . ':0'])) {
            $data[$id  . ':0']['name'] = $root['name'];
            $data[$id  . ':0']['class'] = 'group';
        }
    }

    return $data;
}
