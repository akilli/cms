<?php
declare(strict_types = 1);

namespace qnd;

use RuntimeException;

/**
 * Data
 *
 * @param string $section
 * @param string $id
 *
 * @return mixed
 */
function data(string $section, string $id = null)
{
    $data = & registry('data.' . $section);

    if ($data === null) {
        $data = data_load(path('data', $section . '.php'));
        $data = event('data.load.' . $section, $data);
    }

    if ($id === null) {
        return $data;
    }

    return $data[$id] ?? null;
}

/**
 * Load file data
 *
 * @param string $file
 *
 * @return array
 */
function data_load(string $file): array
{
    return is_readable($file) && ($data = include $file) && is_array($data) ? $data : [];
}

/**
 * Sort order
 *
 * @param array $data
 * @param array $order
 *
 * @return array
 */
function data_order(array $data, array $order): array
{
    uasort(
        $data,
        function (array $a, array $b) use ($order) {
            foreach ($order as $key => $dir) {
                $factor = $dir === 'desc' ? -1 : 1;

                if ($result = ($a[$key] ?? null) <=> ($b[$key] ?? null)) {
                    return $result * $factor;
                }
            }

            return 0;
        }
    );

    return $data;
}

/**
 * Entity data
 *
 * @param array $entity
 *
 * @return array
 *
 * @throws RuntimeException
 */
function data_entity(array $entity): array
{
    if (empty($entity['id']) || empty($entity['name']) || empty($entity['attr'])) {
        throw new RuntimeException(_('Invalid entity configuration'));
    }

    $entity = array_replace(data('default', 'entity'), $entity);
    $entity['name'] = _($entity['name']);
    $entity['tab'] = $entity['tab'] ?: $entity['id'];
    $sort = 0;
    $default = data('default', 'attr');
    $data = data('attr');

    foreach ($entity['attr'] as $id => $attr) {
        if (empty($attr['name']) || empty($attr['type']) || empty($data['type'][$attr['type']])) {
            throw new RuntimeException(_('Invalid attribute configuration'));
        }

        $type = $data['type'][$attr['type']];
        $backend = $data['backend'][$attr['backend'] ?? $type['backend']];
        $frontend = $data['frontend'][$attr['frontend'] ?? $type['frontend']];
        $attr = array_replace($default, $backend, $frontend, $type, $attr);
        $attr['id'] = $id;
        $attr['name'] = _($attr['name']);
        $attr['entity'] = $entity['id'];

        if ($attr['col'] === false) {
            $attr['col'] = null;
        } elseif (!$attr['col']) {
            $attr['col'] = $attr['id'];
        }

        if (!is_numeric($attr['sort'])) {
            $attr['sort'] = $sort;
            $sort += 100;
        }

        $entity['attr'][$id] = $attr;
    }

    return $entity;
}
