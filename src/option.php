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
        return option_bool();
    } elseif (!empty($attr['options_entity'])) {
        return option_entity($attr);
    } elseif (!empty($attr['options_callback'])) {
        return option_callback($attr);
    }

    return option_translate($attr['options']);
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
    } elseif (is_scalar($value)) {
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
 * Bool options
 *
 * @return array
 */
function option_bool(): array
{
    return option_translate([_('No'), _('Yes')]);
}

/**
 * Entity options
 *
 * @param array $attr
 *
 * @return array
 */
function option_entity(array $attr): array
{
    return option_translate(entity_load($attr['options_entity']));
}

/**
 * Options callback
 *
 * @param array $attr
 *
 * @return array
 */
function option_callback(array $attr): array
{
    $callback = $attr['options_callback'][0];
    $params = $attr['options_callback'][1] ?? [];

    return option_translate($callback(...$params));
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
            $data[$item['root_id']  . ':0']['class'] = ['group'];
        }

        $data[$item['position']]['name'] = $item['name'];
        $data[$item['position']]['level'] = $item['level'];
    }

    // Add roots without items
    foreach ($roots as $id => $root) {
        if (empty($data[$id  . ':0'])) {
            $data[$id  . ':0']['name'] = $root['name'];
            $data[$id  . ':0']['class'] = ['group'];
        }
    }

    return $data;
}
