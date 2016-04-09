<?php
namespace akilli;

/**
 * Retrieve attribute options
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function option(array $attr, array $item): array
{
    if ($attr['backend'] === 'bool') {
        return option_bool();
    } elseif (!empty($attr['options_entity'])) {
        return option_entity($attr);
    } elseif (!empty($attr['options_callback'])) {
        return option_callback($attr, $item);
    }

    return option_translate($attr['options']);
}

/**
 * Retrieve bool options
 *
 * @return array
 */
function option_bool(): array
{
    return option_translate([_('No'), _('Yes')]);
}

/**
 * Retrieve entity options
 *
 * @param array $attr
 *
 * @return array
 */
function option_entity(array $attr): array
{
    return option_translate(model_load($attr['options_entity']));
}

/**
 * Retrieve callback options
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function option_callback(array $attr, array $item): array
{
    $params = [];

    foreach ($attr['options_callback_param'] as $param) {
        if ($param === ':attribute') {
            $params[] = $attr;
        } elseif ($param === ':item') {
            $params[] = $item;
        } else {
            $params[] = $param;
        }
    }

    return option_translate($attr['options_callback'](...$params));
}

/**
 * Menubasis
 *
 * @param array $item
 *
 * @return array
 */
function option_menubasis(array $item): array
{
    $roots = model_load($item['_meta']['attributes']['root_id']['options_entity']);
    $data = [];

    foreach (model_load($item['_meta']['id']) as $menu) {
        if (empty($data[$menu['root_id']  . ':0'])) {
            $data[$menu['root_id']  . ':0']['name'] = $roots[$menu['root_id']]['name'];
            $data[$menu['root_id']  . ':0']['class'] = 'group';
        }

        $data[$menu['menubasis']]['name'] = $menu['name'];
        $data[$menu['menubasis']]['level'] = $menu['level'];
    }

    // Add roots without items to index menubasis
    foreach ($roots as $id => $root) {
        if (empty($data[$id  . ':0'])) {
            $data[$id  . ':0']['name'] = $root['name'];
            $data[$id  . ':0']['class'] = 'group';
        }
    }

    return $data;
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
