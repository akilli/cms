<?php
namespace qnd;

use PDO;
use RuntimeException;

/**
 * Size entity
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function eav_size(string $entity, array $criteria = null, array $options = []): int
{
    $meta = data('meta', $entity);
    $contentMeta = data('meta', 'content');
    $valueMeta = data('meta', 'eav');
    $attrs = $meta['attributes'];
    $valueAttrs = array_diff_key($attrs, $contentMeta['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $meta['id'];

    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        } elseif (!empty($valueAttrs[$code])) {
            $alias = qi($code);
            $attrs[$code]['column'] = $alias . '.' . $attr['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = ' LEFT JOIN ' . $valueMeta['table'] . ' ' . $alias . ' ON '
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
 * Load entity
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function eav_load(string $entity, array $criteria = null, $index = null, array $order = [], array $limit = []): array
{
    $meta = data('meta', $entity);
    $contentMeta = data('meta', 'content');
    $valueMeta = data('meta', 'eav');
    $attrs = $meta['attributes'];
    $valueAttrs = array_diff_key($attrs, $contentMeta['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $meta['id'];
    $options = ['search' => $index === 'search'];

    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        } elseif (!empty($valueAttrs[$code])) {
            $alias = qi($code);
            $attrs[$code]['column'] = $alias . '.' . $attr['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = ' LEFT JOIN ' . $valueMeta['table'] . ' ' . $alias . ' ON '
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
        . order($order, $attrs)
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
 * Create entity
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

    $meta = $item['_meta'];
    $contentMeta = data('meta', 'content');
    $attrs = $meta['attributes'];
    $contentAttrs = $contentMeta['attributes'];
    $valueAttrs = array_diff_key($attrs, $contentAttrs);
    $item['entity_id'] = $meta['id'];
    $cols = cols($contentAttrs, $item);

    $stmt = db()->prepare(
        'INSERT INTO ' . $meta['table']
        . ' (' . implode(', ', $cols['col']) . ') VALUES (' . implode(', ', $cols['param']) . ')'
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // Set DB generated id
    if ($attrs['id']['generator'] === 'auto') {
        $item['id'] = (int) db()->lastInsertId();
    }

    // Insert values
    $stmt = db()->prepare(
        'INSERT INTO eav
         (entity_id, attribute_id, content_id, value) 
         VALUES 
         (:__entity_id__, :__attribute_id__, :__content_id__, :__value__)'
    );

    foreach ($valueAttrs as $code => $attr) {
        if (!array_key_exists($code, $item)) {
            continue;
        }

        $stmt->bindValue(':__entity_id__', $item['entity_id'], db_type($attrs['entity_id'], $item['entity_id']));
        $stmt->bindValue(':__attribute_id__', $attr['id'], db_type($attr, $attr['id']));
        $stmt->bindValue(':__content_id__', $item['id'], db_type($attrs['id'], $item['id']));
        $stmt->bindValue(':__value__', $item[$code], db_type($attr, $item[$code]));
        $stmt->execute();
    }

    return true;
}

/**
 * Save entity
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

    $meta = $item['_meta'];
    $contentMeta = data('meta', 'content');
    $attrs = $meta['attributes'];
    $contentAttrs = $contentMeta['attributes'];
    $valueAttrs = array_diff_key($attrs, $contentAttrs);
    $valueModel = meta_skeleton('eav');

    if ($valueAttrs) {
        $values = entity_load('eav', ['content_id' => $item['_old']['id']], 'attribute_id');
    } else {
        $values = [];
    }

    $item['entity_id'] = $meta['id'];
    $cols = cols($contentAttrs, $item);

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
    if ($valueAttrs) {
        $save = [];
        $i = 0;

        foreach ($valueAttrs as $code => $attr) {
            if (!array_key_exists($code, $item)) {
                continue;
            }

            $valueItem = [
                'entity_id' => $item['entity_id'],
                'attribute_id' => $attr['id'],
                'content_id' => $item['id'],
                'value' => $item[$code]
            ];

            if (!empty($values[$code])) {
                $save[$values[$code]['id']] = array_replace($values[$code], $valueItem);
            } else {
                $save[--$i] = array_replace($valueModel, $valueItem);
            }
        }

        if (!entity_save('eav', $save)) {
            throw new RuntimeException(_('Data could not be saved'));
        }
    }

    return true;
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function eav_delete(array $item): bool
{
    return !empty($item['_meta']['id']) && $item['_meta']['id'] === $item['entity_id'] && flat_delete($item);
}
