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

    if (is_string($attr['opt'][0])) {
        $params = $attr['opt'][1] ?? [];
        return $attr['opt'][0](...$params);
    }

    return $attr['opt'][0];
}

/**
 * Option name
 *
 * @param int|string $id
 * @param mixed $value
 *
 * @return string
 */
function opt_name($id, $value): string
{
    if (is_array($value) && !empty($value['name'])) {
        return $value['name'];
    }

    if (is_scalar($value)) {
        return (string) $value;
    }

    return (string) $id;
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

        $data[$item['position']]['name'] = $item['name'];
        $data[$item['position']]['level'] = $item['level'];
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
