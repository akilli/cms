<?php
namespace qnd;

/**
 * Attribute options
 *
 * @param array $attr
 *
 * @return array
 */
function attribute_option(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return attribute_option_bool();
    } elseif (!empty($attr['options_entity'])) {
        return attribute_option_entity($attr);
    } elseif (!empty($attr['options_callback'])) {
        return attribute_option_callback($attr);
    }

    return attribute_option_translate($attr['options']);
}

/**
 * Option name
 *
 * @param int|string $id
 * @param mixed $value
 *
 * @return string
 */
function attribute_option_name($id, $value): string
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
function attribute_option_translate(array $options): array
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
function attribute_option_bool(): array
{
    return attribute_option_translate([_('No'), _('Yes')]);
}

/**
 * Entity options
 *
 * @param array $attr
 *
 * @return array
 */
function attribute_option_entity(array $attr): array
{
    return attribute_option_translate(entity_load($attr['options_entity']));
}

/**
 * Options callback
 *
 * @param array $attr
 *
 * @return array
 */
function attribute_option_callback(array $attr): array
{
    return attribute_option_translate($attr['options_callback'](...$attr['options_callback_param']));
}

/**
 * Tree options
 *
 * @return array
 */
function attribute_option_position(): array
{
    $roots = entity_load('tree_root');
    $data = [];

    foreach (entity_load('tree') as $item) {
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
