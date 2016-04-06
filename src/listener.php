<?php
namespace listener;

use akilli;
use config;
use data;
use i18n;
use metadata;
use model;
use RuntimeException;
use url;

/**
 * Data
 *
 * @param array $data
 *
 * @return void
 */
function config(array & $data)
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
function eav(array & $data)
{
    $data['_metadata'] = akilli\data('metadata', $data['entity_id']);
}

/**
 * Metadata
 *
 * @param array $data
 *
 * @return void
 */
function metadata(array & $data)
{
    foreach ($data as $id => $item) {
        if (empty($item['id'])) {
            continue;
        }

        $item = metadata\entity($item);
        $item['attributes'] = data\order($item['attributes'], 'sort_order');
        $data[$id] = $item;
    }

    // EAV
    $meta = model\load(
        'metadata',
        null,
        ['entity_id', 'attribute_id'],
        ['entity_id' => 'ASC', 'sort_order' => 'ASC']
    );
    $attributes = model\load('attribute');
    $types = akilli\data('type');

    foreach (model\load('entity') as $id => $item) {
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
                            i18n\translate('Entity %s: Invalid value type %s for attribute %s', $id, $type, $code)
                        );
                    }

                    $attribute['column'] = $data['eav_value']['attributes'][$type]['column'];
                    $item['attributes'][$code] = $attribute;
                }
            }
        }

        $item = metadata\entity($item);
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
function model_save(array & $data)
{
    // Entity
    if ($data['_metadata']['id'] === 'entity' && !empty($data['_original'])) {
        // Search
        if (!metadata\action(['view', 'index', 'list'], $data)) {
            model\delete('search', ['entity_id' => $data['_original']['id']], null, null, null, true);
        } elseif ($data['id'] !== $data['_original']['id']
            && ($searchItems = model\load('search', ['entity_id' => $data['_original']['id']]))
        ) {
            foreach ($searchItems as $searchItemId => $searchItem) {
                $searchItems[$searchItemId]['entity_id'] = $data['id'];
            }

            model\save('search', $searchItems);
        }

        // Rewrite
        $criteria = ['target' => $data['_original']['id'] . '/view/id/'];

        if (!metadata\action('view', $data)) {
            model\delete('rewrite', $criteria, 'search', null, null, true);
        } elseif (metadata\action('view', $data)
            && $data['id'] !== $data['_original']['id']
            && ($rewrites = model\load('rewrite', $criteria, 'search'))
        ) {
            foreach ($rewrites as $rewriteId => $rewrite) {
                $rewrites[$rewriteId]['target'] = preg_replace(
                    '#^' . $data['_original']['id'] . '/#',
                    $data['id'] . '/',
                    $rewrite['target']
                );
            }

            model\save('rewrite', $rewrites);
        }
    }

    // Search
    if ($data['_metadata']['id'] !== 'search' && metadata\action(['view', 'index', 'list'], $data['_metadata'])) {
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
        $old = model\load('search', ['entity_id' => $data['_metadata']['id'], 'content_id' => $data['id']], false);
        $searchItems = $old ? [$old['id'] => $searchItem] : [-1 => $searchItem];
        model\save('search', $searchItems);
    }

    // Rewrite
    if ($data['_metadata']['id'] !== 'rewrite' && metadata\action('view', $data['_metadata'])) {
        $target = $data['_metadata']['id'] . '/view/id/' . $data['id'];
        $rewrite = ['id' => $data['name'], 'target' => $target, 'is_system' => true];
        $old = model\load('rewrite', ['target' => $target, 'is_system' => true], false);
        $rewrites = $old ? [$old['id'] => $rewrite] : [-1 => $rewrite];
        model\save('rewrite', $rewrites);
    }
}

/**
 * Model delete
 *
 * @param array $data
 *
 * @return void
 */
function model_delete(array & $data)
{
    // Entity
    if ($data['_metadata']['id'] === 'entity') {
        model\delete('search', ['entity_id' => $data['id']], null, null, null, true);
        model\delete('rewrite', ['target' => $data['id'] . '/view/id/'], 'search', null, null, true);
    }

    model\delete(
        'search',
        ['entity_id' => $data['_metadata']['id'], 'content_id' => $data['id']],
        null,
        null,
        null,
        true
    );
    model\delete(
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
function privilege(array & $data)
{
    $metadata = akilli\data('metadata');
    $config = config\value('action.entity');
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
