<?php
namespace akilli;

use data;
use RuntimeException;

/**
 * Data
 *
 * @param array $data
 *
 * @return void
 */
function listener_config(array & $data)
{
    // Set auto values
    $data['i18n.language'] = locale_get_primary_language($data['i18n.locale']);
    $data['file.all'] = array_merge(
        $data['file.audio'],
        $data['file.embed'],
        $data['file.image'],
        $data['file.misc'],
        $data['file.video']
    );

    // Configure PHP, @todo Remove dynamic configuration?
    ini_set('default_charset', $data['i18n.charset']);
    ini_set('intl.default_locale', $data['i18n.locale']);
    ini_set('date.timezone', $data['i18n.timezone']);
}

/**
 * EAV Model load listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_eav(array & $data)
{
    $data['_metadata'] = data('metadata', $data['entity_id']);
}

/**
 * Metadata
 *
 * @param array $data
 *
 * @return void
 */
function listener_metadata(array & $data)
{
    foreach ($data as $id => $item) {
        if (empty($item['id'])) {
            continue;
        }

        $item = metadata_entity($item);
        $item['attributes'] = data\order($item['attributes'], 'sort_order');
        $data[$id] = $item;
    }

    // EAV
    $meta = model_load(
        'metadata',
        null,
        ['entity_id', 'attribute_id'],
        ['entity_id' => 'ASC', 'sort_order' => 'ASC']
    );
    $attributes = model_load('attribute');
    $types = data('type');

    foreach (model_load('entity') as $id => $item) {
        $item = array_replace($data['eav_content'], $item);
        $item['model'] = 'eav';

        if (!empty($meta[$id])) {
            foreach ($meta[$id] as $code => $attribute) {
                if (empty($attributes[$code])) {
                    continue;
                }

                $attribute = array_replace($attribute, $attributes[$code]);
                unset($attribute['attribute_id']);

                if (empty($item['attributes'][$code])) {
                    $type = 'value_' . $types[$attribute['type']]['backend'];

                    if (empty($data['eav_value']['attributes'][$type]['column'])) {
                        throw new RuntimeException(
                            _('Entity %s: Invalid value type %s for attribute %s', $id, $type, $code)
                        );
                    }

                    $attribute['column'] = $data['eav_value']['attributes'][$type]['column'];
                    $item['attributes'][$code] = $attribute;
                }
            }
        }

        $item = metadata_entity($item);
        $item['attributes'] = data\order($item['attributes'], 'sort_order');
        $data[$id] = $item;
    }
}

/**
 * Save listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_model_save(array & $data)
{
    // Entity
    if ($data['_metadata']['id'] === 'entity' && !empty($data['_original'])) {
        // Search
        if (!metadata_action(['view', 'index', 'list'], $data)) {
           model_delete('search', ['entity_id' => $data['_original']['id']], null, null, null, true);
        } elseif ($data['id'] !== $data['_original']['id']
            && ($searchItems = model_load('search', ['entity_id' => $data['_original']['id']]))
        ) {
            foreach ($searchItems as $searchItemId => $searchItem) {
                $searchItems[$searchItemId]['entity_id'] = $data['id'];
            }

            model_save('search', $searchItems);
        }

        // Rewrite
        $criteria = ['target' => $data['_original']['id'] . '/view/id/'];

        if (!metadata_action('view', $data)) {
            model_delete('rewrite', $criteria, 'search', null, null, true);
        } elseif (metadata_action('view', $data)
            && $data['id'] !== $data['_original']['id']
            && ($rewrites = model_load('rewrite', $criteria, 'search'))
        ) {
            foreach ($rewrites as $rewriteId => $rewrite) {
                $rewrites[$rewriteId]['target'] = preg_replace(
                    '#^' . $data['_original']['id'] . '/#',
                    $data['id'] . '/',
                    $rewrite['target']
                );
            }

            model_save('rewrite', $rewrites);
        }
    }

    // Search
    if ($data['_metadata']['id'] !== 'search' && metadata_action(['view', 'index', 'list'], $data['_metadata'])) {
        $content = '';

        foreach ($data as $code => $value) {
            if (!$value || empty($data['_metadata']['attributes'][$code]['is_searchable'])) {
                continue;
            }

            $data['_metadata']['attributes'][$code]['action'] = 'system';
            $content .= ' ' . str_replace(
                "\n",
                '',
                strip_tags(
                    $data['_metadata']['attributes'][$code]['view']($data['_metadata']['attributes'][$code], $data)
                )
            );
        }

        $searchItem = ['entity_id' => $data['_metadata']['id'], 'content_id' => $data['id'], 'content' => $content];
        $old = model_load('search', ['entity_id' => $data['_metadata']['id'], 'content_id' => $data['id']], false);
        $searchItems = $old ? [$old['id'] => $searchItem] : [-1 => $searchItem];
        model_save('search', $searchItems);
    }

    // Rewrite
    if ($data['_metadata']['id'] !== 'rewrite' && metadata_action('view', $data['_metadata'])) {
        $target = $data['_metadata']['id'] . '/view/id/' . $data['id'];
        $rewrite = ['id' => $data['name'], 'target' => $target, 'is_system' => true];
        $old = model_load('rewrite', ['target' => $target, 'is_system' => true], false);
        $rewrites = $old ? [$old['id'] => $rewrite] : [-1 => $rewrite];
       model_save('rewrite', $rewrites);
    }
}

/**
 * Model delete
 *
 * @param array $data
 *
 * @return void
 */
function listener_model_delete(array & $data)
{
    // Entity
    if ($data['_metadata']['id'] === 'entity') {
        model_delete('search', ['entity_id' => $data['id']], null, null, null, true);
        model_delete('rewrite', ['target' => $data['id'] . '/view/id/'], 'search', null, null, true);
    }

    model_delete(
        'search',
        ['entity_id' => $data['_metadata']['id'], 'content_id' => $data['id']],
        null,
        null,
        null,
        true
    );
    model_delete(
        'rewrite',
        ['target' => $data['_metadata']['id'] . '/view/id/' . $data['id']],
        null,
        null,
        null,
        true
    );
}

/**
 * Auto-generate privileges
 *
 * @param array $data
 *
 * @return void
 */
function listener_privilege(array & $data)
{
    $metadata = data('metadata');
    $config = config('action.entity');
    $key = array_search('all', $config);

    if ($key) {
        unset($config[$key]);
    }

    foreach ($metadata as $entity => $meta) {
        $actions = in_array('all', $meta) ? $config : $meta['actions'];

        if (!$actions) {
            continue;
        }

        $data[$entity . '.all'] = [
            'id' => $entity . '.all',
            'name' => $meta['name'],
            'is_active' => true,
            'sort_order' => 1000,
            'class' => 'group',
        ];

        foreach ($actions as $action) {
            if (in_array($action, $config)) {
                $data[$entity . '.' . $action] = [
                    'id' => $entity . '.' . $action,
                    'name' => $meta['name'] . ' ' . ucwords($action),
                    'is_active' => true,
                    'sort_order' => 1000,
                ];
            }
        }
    }
}
