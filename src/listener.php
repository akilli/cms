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
        $item['attributes'] = data_order($item['attributes'], 'sort');
        $data[$id] = $item;
    }

    $meta = entity_load('meta', null, ['entity_id', 'attribute_id'], ['entity_id' => 'ASC', 'sort' => 'ASC']);
    $attrs = entity_load('attribute');
    $types = data('attribute');

    foreach (entity_load('entity') as $id => $item) {
        $item = array_replace($data['content'], $item);
        $item['type'] = 'eav';

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
        $item['attributes'] = data_order($item['attributes'], 'sort');
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
            'name' => $item['name'],
            'active' => true,
            'sort' => 1000,
            'class' => ['group'],
        ];

        foreach ($actions as $action) {
            if (in_array($action, $config)) {
                $data[$entity . '.' . $action] = [
                    'name' => $item['name'] . ' ' . ucwords($action),
                    'active' => true,
                    'sort' => 1000,
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
            $data[$meta['toolbar']]['children'][$entity]['sort'] = (int) $meta['sort'];
        }
    }

    foreach ($data as $key => $item) {
        $data[$key]['children'] = data_order($item['children'], 'sort');
    }
}

/**
 * EAV entity load listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_entity_eav(array & $data)
{
    $data['_meta'] = data('meta', $data['entity_id']);
}

/**
 * Entity save listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_entity_save(array & $data)
{
    if ($data['_meta']['id'] === 'entity' && !empty($data['_old'])) {
        $criteria = ['target' => $data['_old']['id'] . '/view/id/'];

        if (!meta_action('view', $data)) {
            entity_delete('rewrite', $criteria, 'search', true);
        } elseif (meta_action('view', $data)
            && $data['id'] !== $data['_old']['id']
            && ($rewrites = entity_load('rewrite', $criteria, 'search'))
        ) {
            foreach ($rewrites as $rewriteId => $rewrite) {
                $rewrites[$rewriteId]['target'] = preg_replace(
                    '#^' . $data['_old']['id'] . '/#',
                    $data['id'] . '/',
                    $rewrite['target']
                );
            }

            entity_save('rewrite', $rewrites);
        }
    }

    if ($data['_meta']['id'] !== 'rewrite' && meta_action('view', $data['_meta'])) {
        $target = $data['_meta']['id'] . '/view/id/' . $data['id'];
        $rewrite = ['id' => $data['name'], 'target' => $target, 'system' => true];
        $old = entity_load('rewrite', ['target' => $target, 'system' => true], false);
        $rewrites = $old ? [$old['id'] => $rewrite] : [-1 => $rewrite];
       entity_save('rewrite', $rewrites);
    }
}

/**
 * Entity delete listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_entity_delete(array & $data)
{
    if ($data['_meta']['id'] === 'entity') {
        entity_delete('rewrite', ['target' => $data['id'] . '/view/id/'], 'search', true);
    }

    entity_delete('rewrite', ['target' => $data['_meta']['id'] . '/view/id/' . $data['id']], null, true);
}
