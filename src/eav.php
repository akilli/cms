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
    $metadata = db_meta($entity);
    $db = db($metadata['db']);
    $contentMetadata = db_meta('eav_content');
    $valueMetadata = db_meta('eav_value');
    $attributes = $metadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentMetadata['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $metadata['id'];

    // Prepare attributes
    foreach ($attributes as $code => $attribute) {
        if (empty($attribute['column'])) {
            continue;
        } elseif (!empty($valueAttributes[$code])) {
            $alias = db_quote_identifier($db, $code);
            $attributes[$code]['column'] = $alias . '.' . $attribute['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = 'LEFT JOIN ' . $valueMetadata['table'] . ' ' . $alias . ' ON '
                . $alias . '.' . $valueMetadata['attributes']['content_id']['column']
                . ' = e.' . $metadata['attributes']['id']['column'] . ' AND '
                . $alias . '.' . $valueMetadata['attributes']['attribute_id']['column'] . ' = ' . $params[$code];
        } else {
            $attributes[$code]['column'] = 'e.' . $attribute['column'];
        }
    }

    // Prepare statement
    $stmt = $db->prepare(
        'SELECT COUNT(*) as total'
        . db_from($db, $metadata['table'], 'e')
        . (!empty($joins) ? implode(' ', $joins) : '')
        . db_where($db, $criteria, $attributes, null, false, !empty($options['search']))
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue(
            $param,
            $attributes[$code]['id'],
            db_type($valueMetadata['attributes']['attribute_id'], $attributes[$code]['id'])
        );
    }

    // Execute statement
    $stmt->execute();

    // Result
    $item = $stmt->fetch();

    return (int) $item['total'];
}

/**
 * Load data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int|array $limit
 *
 * @return array
 */
function eav_load(string $entity, array $criteria = null, $index = null, array $order = null, $limit = null): array
{
    $metadata = db_meta($entity);
    $db = db($metadata['db']);
    $contentMetadata = db_meta('eav_content');
    $valueMetadata = db_meta('eav_value');
    $attributes = $metadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentMetadata['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $metadata['id'];

    // Prepare attributes
    foreach ($attributes as $code => $attribute) {
        if (empty($attribute['column'])) {
            continue;
        } elseif (!empty($valueAttributes[$code])) {
            $alias = db_quote_identifier($db, $code);
            $attributes[$code]['column'] = $alias . '.' . $attribute['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = 'LEFT JOIN ' . $valueMetadata['table'] . ' ' . $alias . ' ON '
                . $alias . '.' . $valueMetadata['attributes']['content_id']['column']
                . ' = e.' . $metadata['attributes']['id']['column'] . ' AND '
                . $alias . '.' . $valueMetadata['attributes']['attribute_id']['column'] . ' = ' . $params[$code];
        } else {
            $attributes[$code]['column'] = 'e.' . $attribute['column'];
        }
    }

    // Prepare statement
    $stmt = $db->prepare(
        db_select($db, $attributes)
        . db_from($db, $metadata['table'], 'e')
        . (!empty($joins) ? implode(' ', $joins) : '')
        . db_where($db, $criteria, $attributes, null, false, $index === 'search')
        . db_order($db, (array) $order, $attributes)
        . db_limit($limit)
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue(
            $param,
            $attributes[$code]['id'],
            db_type($valueMetadata['attributes']['attribute_id'], $attributes[$code]['id'])
        );
    }

    // Execute statement
    $stmt->execute();

    // Result
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
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = db_meta($item['_metadata']);
    $db = db($metadata['db']);
    $contentMetadata = db_meta('eav_content');
    $attributes = $metadata['attributes'];
    $contentAttributes = $contentMetadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentAttributes);
    $valueModel = metadata_skeleton('eav_value');

    // Entity
    $item['entity_id'] = $metadata['id'];

    // Columns
    $columns = $params = $sets = [];
    db_columns($contentAttributes, $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'INSERT INTO ' . $metadata['table']
        . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')'
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attributes[$code], $item[$code]));
    }

    // Execute statement
    $stmt->execute();

    // Add DB generated id
    if (!empty($attributes['id']['auto'])) {
        $item['id'] = (int) $db->lastInsertId($metadata['sequence']);
    }

    // Values
    if ($valueAttributes) {
        $save = [];
        $i = 0;

        foreach ($valueAttributes as $code => $attribute) {
            if (!array_key_exists($code, $item)) {
                continue;
            }

            $valueCode = 'value_' . $attribute['backend'];
            $save[--$i] = array_replace(
                $valueModel,
                [
                    'entity_id' => $item['entity_id'],
                    'attribute_id' => $attribute['id'],
                    'content_id' => $item['id'],
                    $valueCode => $item[$code]
                ]
            );
        }

        // Create Values
        if (count($save) > 0 && !model_save('eav_value', $save)) {
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
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = db_meta($item['_metadata']);
    $db = db($metadata['db']);
    $contentMetadata = db_meta('eav_content');
    $attributes = $metadata['attributes'];
    $contentAttributes = $contentMetadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentAttributes);
    $valueModel = metadata_skeleton('eav_value');

    if ($valueAttributes) {
        $values = model_load('eav_value', ['content_id' => $item['_original']['id']], 'attribute_id');
    } else {
        $values = [];
    }

    // Entity
    $item['entity_id'] = $metadata['id'];

    // Columns
    $columns = $params = $sets = [];
    db_columns($contentAttributes, $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'UPDATE ' . $metadata['table']
        . ' SET ' . implode(', ', $sets)
        . ' WHERE ' . $attributes['id']['column'] . '  = :id'
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attributes[$code], $item[$code]));
    }

    $stmt->bindValue(':id', $item['_original']['id'], db_type($attributes['id'], $item['_original']['id']));

    // Execute statement
    $stmt->execute();

    // Values
    if ($valueAttributes) {
        $save = [];
        $i = 0;

        foreach ($valueAttributes as $code => $attribute) {
            if (!array_key_exists($code, $item)) {
                continue;
            }

            $valueCode = 'value_' . $attribute['backend'];
            $valueItem = [
                'entity_id' => $item['entity_id'],
                'attribute_id' => $attribute['id'],
                'content_id' => $item['id'],
                $valueCode => $item[$code]
            ];

            if (!empty($values[$code])) {
                $save[$values[$code]['id']] = array_replace($values[$code], $valueItem);
            } else {
                $save[--$i] = array_replace($valueModel, $valueItem);
            }
        }

        // Save Values
        if (!model_save('eav_value', $save)) {
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
    // No metadata provided
    if (empty($item['_metadata']) || $item['_metadata']['id'] !== $item['entity_id']) {
        return false;
    }

    return sql_delete($item);
}
