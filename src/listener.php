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

    $projects = [PROJECT_DEFAULT, project('id')];
    $attrs = all('attr', ['project_id' => $projects], ['index' => ['entity_id', 'uid']]);

    foreach (all('entity', ['model' => ['content', 'eav', 'joined'], 'project_id' => $projects]) as $id => $item) {
        $base = $item['model'] === 'joined' && !empty($data[$id]) ? $data[$id] : $data['content'];
        $item = array_replace($base, $item);

        if ($item['model'] === 'eav' && !empty($attrs[$id])) {
            foreach ($attrs[$id] as $uid => $attr) {
                if (empty($item['attr'][$uid])) {
                    $attr['col'] = 'value';
                    $attr['eav_id'] = $attr['id'];
                    unset($attr['id'], $attr['uid'], $attr['project_id']);
                    $item['attr'][$uid] = $attr;
                }
            }
        }

        unset($item['project_id']);
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
            delete('rewrite', $crit, ['search' => true, 'system' => true]);
        } elseif (data_action('view', $data)
            && $data['id'] !== $data['_old']['id']
            && ($rw = all('rewrite', $crit, ['search' => true]))
        ) {
            foreach ($rw as $rId => $r) {
                $rw[$rId]['target'] = preg_replace('#^' . $data['_old']['id'] . '/#', $data['id'] . '/', $r['target']);
            }

            save('rewrite', $rw);
        }
    }

    if ($data['_entity']['id'] !== 'rewrite' && data_action('view', $data['_entity'])) {
        $target = $data['_entity']['id'] . '/view/id/' . $data['id'];
        $old = one('rewrite', ['target' => $target, 'system' => true]);
        $rId = $old['id'] ?? -1;
        $all = all('rewrite', [], ['index' => 'name']);
        $name = generator_id($data['name'], array_column($all, 'name', 'id'), $rId);
        $rw = [$rId => ['name' => $name, 'target' => $target, 'system' => true]];
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
        delete('rewrite', ['target' => $data['id'] . '/view/id/'], ['search' => true, 'system' => true]);
    }

    delete('rewrite', ['target' => $data['_entity']['id'] . '/view/id/' . $data['id']], ['system' => true]);
}
