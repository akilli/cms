<?php
namespace qnd;

/**
 * Option
 *
 * @param array $attr
 *
 * @return array
 */
function option(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return option_translate(['No', 'Yes']);
    }

    if (empty($attr['options'][0]) || !is_string($attr['options'][0]) && !is_array($attr['options'][0])) {
        return [];
    }

    if (is_string($attr['options'][0])) {
        $params = $attr['options'][1] ?? [];
        return option_translate($attr['options'][0](...$params));
    }

    return option_translate($attr['options'][0]);
}

/**
 * Option name
 *
 * @param int|string $id
 * @param mixed $value
 *
 * @return string
 */
function option_name($id, $value): string
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
 * Translate options
 *
 * @param array $options
 *
 * @return array
 */
function option_translate(array $options): array
{
    foreach ($options as $key => $value) {
        if (is_scalar($value)) {
            $options[$key] = _($value);
        } elseif (is_array($value) && !empty($value['name'])) {
            $options[$key]['name'] = _($value['name']);
        }
    }

    return $options;
}

/**
 * Tree options
 *
 * @return array
 */
function option_position(): array
{
    $roots = entity_load('menu');
    $data = [];

    foreach (entity_load('node') as $item) {
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
