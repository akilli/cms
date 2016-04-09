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
    } elseif (!empty($attr['foreign_entity_id'])) {
        return option_foreign($attr);
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
 * Retrieve foreign entity options
 *
 * @param array $attr
 *
 * @return array
 */
function option_foreign(array $attr): array
{
    return option_translate(model_load($attr['foreign_entity_id']));
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
        } elseif (preg_match('#^:(attribute|item)\.(.+)#', $param, $match)) {
            $params[] = ${$match[1]}[$match[2]] ?? null;
        } else {
            $params[] = $param;
        }
    }

    return option_translate($attr['options_callback'](...$params));
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
 * Menubasis
 *
 * @param string $entity
 *
 * @return array
 */
function option_menubasis(string $entity): array
{
    $meta = data('meta', $entity);
    $collection = model_load($meta['attributes']['root_id']['foreign_entity_id']);
    $data = [];

    foreach (model_load($entity) as $item) {
        if (empty($data[$item['root_id']  . ':0'])) {
            $data[$item['root_id']  . ':0']['name'] = $collection[$item['root_id']]['name'];
            $data[$item['root_id']  . ':0']['class'] = 'group';
        }

        $data[$item['menubasis']]['name'] = $item['name'];
        $data[$item['menubasis']]['level'] = $item['level'];
    }

    // Add roots without items to index menubasis
    foreach ($collection as $id => $item) {
        if (empty($data[$id  . ':0'])) {
            $data[$id  . ':0']['name'] = $item['name'];
            $data[$id  . ':0']['class'] = 'group';
        }
    }

    return $data;
}
