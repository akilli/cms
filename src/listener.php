<?php
namespace qnd;

/**
 * Config data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_config(array & $data)
{
    // Set language from locale
    $data['i18n.lang'] = locale_get_primary_language($data['i18n.locale']);
    // Add allowed media extensions to allowed file extensions
    $data['ext.file'] = array_merge(
        $data['ext.file'],
        $data['ext.audio'],
        $data['ext.embed'],
        $data['ext.image'],
        $data['ext.video']
    );
    // Configure PHP
    ini_set('default_charset', $data['i18n.charset']);
    ini_set('intl.default_locale', $data['i18n.locale']);
    ini_set('date.timezone', $data['i18n.timezone']);
}

/**
 * Entity data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_entity(array & $data)
{
    foreach ($data as $id => $item) {
        if (!empty($item['model']) && $item['model'] === 'joined') {
            $item = array_replace_recursive($data['content'], $item);
        }

        $item['id'] = $id;
        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], ['sort' => 'asc']);
        $data[$id] = $item;
    }

    $attrs = load('attr', [], ['entity_id', 'id'], ['entity_id' => 'asc', 'sort' => 'asc']);

    foreach (load('entity', ['model' => ['content', 'eav', 'joined']]) as $id => $item) {
        $base = $item['model'] === 'joined' && !empty($data[$id]) ? $data[$id] : $data['content'];
        $item = array_replace($base, $item);

        if ($item['model'] === 'eav' && !empty($attrs[$id])) {
            foreach ($attrs[$id] as $code => $attr) {
                if (empty($item['attr'][$code])) {
                    $attr['col'] = 'value';
                    $item['attr'][$code] = $attr;
                }
            }
        }

        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], ['sort' => 'asc']);
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
    $config = config('action.entity');
    unset($config['all']);

    foreach (data('entity') as $eId => $entity) {
        $actions = in_array('all', $entity['actions']) ? $config : $entity['actions'];

        if (!$actions) {
            continue;
        }

        $data[$eId . '.all'] = [
            'name' => $entity['name'],
            'active' => true,
            'sort' => 1000,
            'class' => 'group',
        ];

        foreach ($actions as $action) {
            if (in_array($action, $config)) {
                $data[$eId . '.' . $action] = [
                    'name' => $entity['name'] . ' ' . ucwords($action),
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
    foreach (data('entity') as $eId => $entity) {
        if (data_action('index', $entity) && !empty($entity['toolbar']) && !empty($data[$entity['toolbar']])) {
            $data[$entity['toolbar']]['children'][$eId]['name'] = $entity['name'];
            $data[$entity['toolbar']]['children'][$eId]['url'] = $eId . '/index';
            $data[$entity['toolbar']]['children'][$eId]['privilege'] = $eId . '.index';
            $data[$entity['toolbar']]['children'][$eId]['sort'] = (int) $entity['sort'];
        }
    }

    foreach ($data as $key => $item) {
        $data[$key]['children'] = data_order($item['children'], ['sort' => 'asc']);
    }
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
    if ($data['_entity']['id'] === 'entity' && !empty($data['_old'])) {
        $crit = ['target' => $data['_old']['id'] . '/view/id/'];

        if (!data_action('view', $data)) {
            delete('rewrite', $crit, 'search', true);
        } elseif (data_action('view', $data) && $data['id'] !== $data['_old']['id'] && ($rw = load('rewrite', $crit, 'search'))) {
            foreach ($rw as $rId => $r) {
                $rw[$rId]['target'] = preg_replace('#^' . $data['_old']['id'] . '/#', $data['id'] . '/', $r['target']);
            }

            save('rewrite', $rw);
        }
    }

    if ($data['_entity']['id'] !== 'rewrite' && data_action('view', $data['_entity'])) {
        $target = $data['_entity']['id'] . '/view/id/' . $data['id'];
        $r = ['name' => $data['name'], 'target' => $target, 'system' => true];
        $old = load('rewrite', ['target' => $target, 'system' => true], false);
        $rw = $old ? [$old['id'] => $r] : [-1 => $r];
       save('rewrite', $rw);
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
    if ($data['_entity']['id'] === 'entity') {
        delete('rewrite', ['target' => $data['id'] . '/view/id/'], 'search', true);
    }

    delete('rewrite', ['target' => $data['_entity']['id'] . '/view/id/' . $data['id']], null, true);
}
