<?php
namespace qnd;

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
 * Entity data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_entity(array & $data)
{
    foreach ($data as $id => $item) {
        if (!empty($item['type']) && $item['type'] === 'joined') {
            $item = array_replace_recursive($data['content'], $item);
        }

        $item['id'] = $id;
        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], 'sort');
        $data[$id] = $item;
    }

    $attrs = load('attr', [], ['entity_id', 'id'], ['entity_id' => 'ASC', 'sort' => 'ASC']);

    foreach (load('entity', ['type' => ['content', 'eav', 'joined']]) as $id => $item) {
        $base = $item['type'] === 'joined' && !empty($data[$id]) ? $data[$id] : $data['content'];
        $item = array_replace($base, $item);

        if ($item['type'] === 'eav' && !empty($attrs[$id])) {
            foreach ($attrs[$id] as $code => $attr) {
                if (empty($item['attr'][$code])) {
                    $attr['col'] = 'value';
                    $item['attr'][$code] = $attr;
                }
            }
        }

        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], 'sort');
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
    $data['_entity'] = data('entity', $data['entity_id']);
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
        $criteria = ['target' => $data['_old']['id'] . '/view/id/'];

        if (!data_action('view', $data)) {
            delete('rewrite', $criteria, 'search', true);
        } elseif (data_action('view', $data) && $data['id'] !== $data['_old']['id'] && ($rw = load('rewrite', $criteria, 'search'))) {
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
