<?php
namespace akilli;

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
    $data['_meta'] = data('meta', $data['entity_id']);
}

/**
 * Metadata listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_meta(array & $data)
{
    foreach ($data as $id => $item) {
        if (empty($item['id'])) {
            continue;
        }

        $item = meta_entity($item);
        $item['attributes'] = data_order($item['attributes'], 'sort_order');
        $data[$id] = $item;
    }

    $meta = model_load('meta', null, ['entity_id', 'attribute_id'], ['entity_id' => 'ASC', 'sort_order' => 'ASC']);
    $attrs = model_load('attribute');
    $types = data('attribute');

    foreach (model_load('entity') as $id => $item) {
        $item = array_replace($data['content'], $item);
        $item['model'] = 'eav';

        if (!empty($meta[$id])) {
            foreach ($meta[$id] as $code => $attr) {
                if (empty($attrs[$code])) {
                    continue;
                }

                $attr = array_replace($attr, $attrs[$code]);
                unset($attr['attribute_id']);

                if (empty($item['attributes'][$code])) {
                    $type = 'value_' . $types[$attr['type']]['backend'];

                    if (empty($data['eav']['attributes'][$type]['column'])) {
                        throw new RuntimeException(
                            _('Entity %s: Invalid value type %s for attribute %s', $id, $type, $code)
                        );
                    }

                    $attr['column'] = $data['eav']['attributes'][$type]['column'];
                    $item['attributes'][$code] = $attr;
                }
            }
        }

        $item = meta_entity($item);
        $item['attributes'] = data_order($item['attributes'], 'sort_order');
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
    if ($data['_meta']['id'] === 'entity' && !empty($data['_old'])) {
        $criteria = ['target' => $data['_old']['id'] . '/view/id/'];

        if (!meta_action('view', $data)) {
            model_delete('rewrite', $criteria, 'search', true);
        } elseif (meta_action('view', $data)
            && $data['id'] !== $data['_old']['id']
            && ($rewrites = model_load('rewrite', $criteria, 'search'))
        ) {
            foreach ($rewrites as $rewriteId => $rewrite) {
                $rewrites[$rewriteId]['target'] = preg_replace(
                    '#^' . $data['_old']['id'] . '/#',
                    $data['id'] . '/',
                    $rewrite['target']
                );
            }

            model_save('rewrite', $rewrites);
        }
    }

    if ($data['_meta']['id'] !== 'rewrite' && meta_action('view', $data['_meta'])) {
        $target = $data['_meta']['id'] . '/view/id/' . $data['id'];
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
    if ($data['_meta']['id'] === 'entity') {
        model_delete('rewrite', ['target' => $data['id'] . '/view/id/'], 'search', true);
    }

    model_delete('rewrite', ['target' => $data['_meta']['id'] . '/view/id/' . $data['id']], null, true);
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
    $meta = data('meta');
    $config = config('action.entity');
    $key = array_search('all', $config);

    if ($key) {
        unset($config[$key]);
    }

    foreach ($meta as $entity => $item) {
        $actions = in_array('all', $item) ? $config : $item['actions'];

        if (!$actions) {
            continue;
        }

        $data[$entity . '.all'] = [
            'id' => $entity . '.all',
            'name' => $item['name'],
            'is_active' => true,
            'sort_order' => 1000,
            'class' => 'group',
        ];

        foreach ($actions as $action) {
            if (in_array($action, $config)) {
                $data[$entity . '.' . $action] = [
                    'id' => $entity . '.' . $action,
                    'name' => $item['name'] . ' ' . ucwords($action),
                    'is_active' => true,
                    'sort_order' => 1000,
                ];
            }
        }
    }
}
