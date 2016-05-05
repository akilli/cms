<?php
namespace qnd;

use RuntimeException;

/**
 * Config data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_config(array & $data)
{
    // Set auto values
    $data['file.all'] = array_merge(
        $data['file.audio'],
        $data['file.embed'],
        $data['file.image'],
        $data['file.misc'],
        $data['file.video']
    );

    // Configure PHP
    ini_set('default_charset', $data['i18n.charset']);
    ini_set('intl.default_locale', $data['i18n.locale']);
    ini_set('date.timezone', $data['i18n.timezone']);
}

/**
 * Meta data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_meta(array & $data)
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
 * Privilege data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_privilege(array & $data)
{
    $meta = data('meta');
    $config = config('action.entity');
    unset($config['all']);

    foreach ($meta as $entity => $item) {
        $actions = in_array('all', $item) ? $config : $item['actions'];

        if (!$actions) {
            continue;
        }

        $data[$entity . '.all'] = [
            'id' => $entity . '.all',
            'name' => $item['name'],
            'active' => true,
            'sort_order' => 1000,
            'class' => 'group',
        ];

        foreach ($actions as $action) {
            if (in_array($action, $config)) {
                $data[$entity . '.' . $action] = [
                    'id' => $entity . '.' . $action,
                    'name' => $item['name'] . ' ' . ucwords($action),
                    'active' => true,
                    'sort_order' => 1000,
                ];
            }
        }
    }
}

/**
 * Toolbar data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_toolbar(array & $data)
{
    foreach (data('meta') as $entity => $meta) {
        if (meta_action('index', $meta) && !empty($meta['toolbar']) && !empty($data[$meta['toolbar']])) {
            $data[$meta['toolbar']]['children'][$entity]['name'] = $meta['name'];
            $data[$meta['toolbar']]['children'][$entity]['url'] = $entity . '/index';
            $data[$meta['toolbar']]['children'][$entity]['privilege'] = $entity . '.index';
            $data[$meta['toolbar']]['children'][$entity]['sort_order'] = (int) $meta['sort_order'];
        }
    }

    foreach ($data as $key => $item) {
        $data[$key]['children'] = data_order($item['children'], 'sort_order');
    }
}

/**
 * EAV model load listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_model_eav(array & $data)
{
    $data['_meta'] = data('meta', $data['entity_id']);
}

/**
 * Model save listener
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
        $rewrite = ['id' => $data['name'], 'target' => $target, 'system' => true];
        $old = model_load('rewrite', ['target' => $target, 'system' => true], false);
        $rewrites = $old ? [$old['id'] => $rewrite] : [-1 => $rewrite];
       model_save('rewrite', $rewrites);
    }
}

/**
 * Model delete listener
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
