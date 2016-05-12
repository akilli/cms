<?php
namespace qnd;

use PDO;

/**
 * Size entity
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function eav_size(string $entity, array $criteria = [], array $options = []): int
{
    $meta = data('meta', $entity);
    $conMeta = data('meta', 'content');
    $attrs = $meta['attributes'];
    $valAttrs = array_diff_key($attrs, $conMeta['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $meta['id'];

    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        } elseif (!empty($valAttrs[$code])) {
            $alias = qi($code);
            $attrs[$code]['column'] = $alias . '.' . $attr['column'];
            $params[$code] = ':' . str_replace('-', '_', $code);
            $joins[$code] = sprintf(
                'LEFT JOIN eav %1$s ON %1$s.content_id = e.id AND %1$s.attribute_id = %2$s',
                $alias,
                $params[$code]
            );
        } else {
            $attrs[$code]['column'] = 'e.' . $attr['column'];
        }
    }

    $stmt = prep(
        'SELECT COUNT(*) AS total FROM content e %s %s',
        implode(' ', $joins),
        where($criteria, $attrs, $options)
    );

    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $attrs[$code]['id'], PDO::PARAM_STR);
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
function eav_load(string $entity, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $meta = data('meta', $entity);
    $conAttrs = data('meta', 'content')['attributes'];
    $attrs = $meta['attributes'];
    $valAttrs = array_diff_key($attrs, $conAttrs);
    $joins = $params = [];
    $criteria['entity_id'] = $meta['id'];
    $options = ['search' => $index === 'search'];

    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        } elseif (!empty($valAttrs[$code])) {
            $alias = qi($code);
            $attrs[$code]['column'] = $alias . '.' . $attr['column'];
            $params[$code] = ':' . str_replace('-', '_', $code);
            $joins[$code] = sprintf(
                'LEFT JOIN eav %1$s ON %1$s.content_id = e.id AND %1$s.attribute_id = %2$s',
                $alias,
                $params[$code]
            );
        } else {
            $attrs[$code]['column'] = 'e.' . $attr['column'];
        }
    }

    $stmt = db()->prepare(
        select($attrs)
        . from($meta['table'], 'e')
        . (!empty($joins) ? ' ' . implode(' ', $joins) : '')
        . where($criteria, $attrs, $options)
        . order($order, $attrs)
        . limit($limit)
    );

    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $attrs[$code]['id'], PDO::PARAM_STR);
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
 */
function eav_create(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $attrs = $item['_meta']['attributes'];
    $conAttrs = data('meta', 'content')['attributes'];
    $valAttrs = array_diff_key($attrs, $conAttrs);
    $cols = cols($conAttrs, $item);

    $stmt = prep(
        'INSERT INTO content (%s) VALUES (%s)',
        implode(', ', $cols['col']),
        implode(', ', $cols['param'])
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
    $stmt = db()->prepare('
        INSERT INTO 
            eav
            (attribute_id, content_id, value) 
         VALUES 
            (:attribute_id, :content_id, :value)
    ');

    foreach ($valAttrs as $code => $attr) {
        if (!array_key_exists($code, $item)) {
            continue;
        }

        $stmt->bindValue(':attribute_id', $attr['id'], PDO::PARAM_STR);
        $stmt->bindValue(':content_id', $item['id'], PDO::PARAM_INT);
        $stmt->bindValue(':value', $item[$code], PDO::PARAM_STR);
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
 */
function eav_save(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $attrs = $item['_meta']['attributes'];
    $conAttrs = data('meta', 'content')['attributes'];
    $valAttrs = array_diff_key($attrs, $conAttrs);
    $cols = cols($conAttrs, $item);

    $stmt = prep(
        'UPDATE content SET %s WHERE id = :_id',
        implode(', ', $cols['set'])
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':_id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));
    $stmt->execute();

    // Save values
    $stmt = db()->prepare('
        INSERT INTO 
            eav
        SET
            attribute_id = :attribute_id,
            content_id = :content_id,
            value = :value
        ON DUPLICATE KEY UPDATE
            value = VALUES(value)
    ');

    foreach ($valAttrs as $code => $attr) {
        if (!array_key_exists($code, $item)) {
            continue;
        }

        $stmt->bindValue(':attribute_id', $attr['id'], PDO::PARAM_STR);
        $stmt->bindValue(':content_id', $item['id'], PDO::PARAM_INT);
        $stmt->bindValue(':value', $item[$code], PDO::PARAM_STR);
        $stmt->execute();
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
