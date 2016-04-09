<?php
namespace akilli;

use RuntimeException;

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function eav_size(string $entity, array $criteria = null, array $options = []): int
{
    $meta = db_meta($entity);
    $contentMeta = db_meta('content');
    $valueMeta = db_meta('eav');
    $attrs = $meta['attributes'];
    $valueAttributes = array_diff_key($attrs, $contentMeta['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $meta['id'];

    // Prepare attributes
    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        } elseif (!empty($valueAttributes[$code])) {
            $alias = qi($code);
            $attrs[$code]['column'] = $alias . '.' . $attr['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = 'LEFT JOIN ' . $valueMeta['table'] . ' ' . $alias . ' ON '
                . $alias . '.' . $valueMeta['attributes']['content_id']['column']
                . ' = e.' . $meta['attributes']['id']['column'] . ' AND '
                . $alias . '.' . $valueMeta['attributes']['attribute_id']['column'] . ' = ' . $params[$code];
        } else {
            $attrs[$code]['column'] = 'e.' . $attr['column'];
        }
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) as total'
        . from($meta['table'], 'e')
        . (!empty($joins) ? implode(' ', $joins) : '')
        . where($criteria, $attrs, $options)
    );

    foreach ($params as $code => $param) {
        $stmt->bindValue(
            $param,
            $attrs[$code]['id'],
            db_type($valueMeta['attributes']['attribute_id'], $attrs[$code]['id'])
        );
    }

    $stmt->execute();

    return (int) $stmt->fetch()['total'];
}

/**
 * Load data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int[] $limit
 *
 * @return array
 */
function eav_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = null): array
{
    $meta = db_meta($entity);
    $contentMeta = db_meta('content');
    $valueMeta = db_meta('eav');
    $attrs = $meta['attributes'];
    $valueAttributes = array_diff_key($attrs, $contentMeta['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $meta['id'];
    $options = ['search' => $index === 'search'];

    // Prepare attributes
    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        } elseif (!empty($valueAttributes[$code])) {
            $alias = qi($code);
            $attrs[$code]['column'] = $alias . '.' . $attr['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = 'LEFT JOIN ' . $valueMeta['table'] . ' ' . $alias . ' ON '
                . $alias . '.' . $valueMeta['attributes']['content_id']['column']
                . ' = e.' . $meta['attributes']['id']['column'] . ' AND '
                . $alias . '.' . $valueMeta['attributes']['attribute_id']['column'] . ' = ' . $params[$code];
        } else {
            $attrs[$code]['column'] = 'e.' . $attr['column'];
        }
    }

    $stmt = db()->prepare(
        select($attrs)
        . from($meta['table'], 'e')
        . (!empty($joins) ? implode(' ', $joins) : '')
        . where($criteria, $attrs, $options)
        . order((array) $order, $attrs)
        . limit($limit)
    );

    foreach ($params as $code => $param) {
        $stmt->bindValue(
            $param,
            $attrs[$code]['id'],
            db_type($valueMeta['attributes']['attribute_id'], $attrs[$code]['id'])
        );
    }

    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Create
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function eav_create(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);
    $contentMeta = db_meta('content');
    $attrs = $meta['attributes'];
    $contentAttributes = $contentMeta['attributes'];
    $valueAttributes = array_diff_key($attrs, $contentAttributes);
    $valueModel = meta_skeleton('eav');
    $item['entity_id'] = $meta['id'];
    $cols = cols($contentAttributes, $item);

    $stmt = db()->prepare(
        'INSERT INTO ' . $meta['table']
        . ' (' . implode(', ', $cols['col']) . ') VALUES (' . implode(', ', $cols['param']) . ')'
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // Set DB generated id
    if (!empty($attrs['id']['auto'])) {
        $item['id'] = (int) db()->lastInsertId($meta['sequence']);
    }

    // Insert values
    if ($valueAttributes) {
        $save = [];
        $i = 0;

        foreach ($valueAttributes as $code => $attr) {
            if (!array_key_exists($code, $item)) {
                continue;
            }

            $valueCode = 'value_' . $attr['backend'];
            $save[--$i] = array_replace(
                $valueModel,
                [
                    'entity_id' => $item['entity_id'],
                    'attribute_id' => $attr['id'],
                    'content_id' => $item['id'],
                    $valueCode => $item[$code]
                ]
            );
        }

        if (count($save) > 0 && !model_save('eav', $save)) {
            throw new RuntimeException('Save call failed');
        }
    }

    return true;
}

/**
 * Save
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function eav_save(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);
    $contentMeta = db_meta('content');
    $attrs = $meta['attributes'];
    $contentAttributes = $contentMeta['attributes'];
    $valueAttributes = array_diff_key($attrs, $contentAttributes);
    $valueModel = meta_skeleton('eav');

    if ($valueAttributes) {
        $values = model_load('eav', ['content_id' => $item['_old']['id']], 'attribute_id');
    } else {
        $values = [];
    }

    $item['entity_id'] = $meta['id'];
    $cols = cols($contentAttributes, $item);

    $stmt = db()->prepare(
        'UPDATE ' . $meta['table']
        . ' SET ' . implode(', ', $cols['set'])
        . ' WHERE ' . $attrs['id']['column'] . '  = :id'
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));
    $stmt->execute();

    // Save values
    if ($valueAttributes) {
        $save = [];
        $i = 0;

        foreach ($valueAttributes as $code => $attr) {
            if (!array_key_exists($code, $item)) {
                continue;
            }

            $valueCode = 'value_' . $attr['backend'];
            $valueItem = [
                'entity_id' => $item['entity_id'],
                'attribute_id' => $attr['id'],
                'content_id' => $item['id'],
                $valueCode => $item[$code]
            ];

            if (!empty($values[$code])) {
                $save[$values[$code]['id']] = array_replace($values[$code], $valueItem);
            } else {
                $save[--$i] = array_replace($valueModel, $valueItem);
            }
        }

        if (!model_save('eav', $save)) {
            throw new RuntimeException('Save call failed');
        }
    }

    return true;
}

/**
 * Delete data
 *
 * @param array $item
 *
 * @return bool
 */
function eav_delete(array $item): bool
{
    return !empty($item['_meta']['id']) && $item['_meta']['id'] === $item['entity_id'] && flat_delete($item);
}
