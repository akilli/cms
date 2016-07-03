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
        $item['id'] = $id;
        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], ['sort' => 'asc']);
        $data[$id] = $item;
    }

    if (!$entities = all('entity', ['project_id' => project('ids')])) {
        return;
    }

    $attrs = all('attr', ['project_id' => project('ids')], ['index' => ['entity_id', 'uid']]);

    foreach ($entities as $id => $item) {
        if (!empty($data[$id])) {
            message(_('Can not use reserved Id %s for Entity %s', $id, $item['name']));
            continue;
        }

        $item = array_replace($data['content'], $item);
        $item['model'] = 'eav';

        if (!empty($attrs[$id])) {
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
    foreach ($data as $id => $item) {
        if (!empty($item['callback'])) {
            $data[$id]['callback'] = fqn($item['callback']);
        }
    }

    $config = config('action.entity');

    foreach (data('entity') as $eId => $entity) {
        foreach ($entity['actions'] as $action) {
            if (in_array($action, $config)) {
                $data[$eId . '.' . $action] = [
                    'name' => $entity['name'] . ' ' . ucwords($action),
                    'active' => true,
                    'sort' => 1000,
                ];
            }
        }
    }

    $data = data_order($data, ['sort' => 'asc', 'name' => 'asc']);
}

/**
 * Save listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_save(array & $data)
{
    if ($data['_entity']['id'] === 'rewrite' || !data_action('view', $data['_entity'])) {
        return;
    }

    $target = $data['_entity']['id'] . '/view/id/' . $data['id'];
    $old = one('rewrite', ['target' => $target, 'system' => true]);
    $rId = $old['id'] ?? -1;
    $all = all('rewrite', [], ['index' => 'name']);
    $name = generator_id($data['name'], array_column($all, 'name', 'id'), $rId);
    $rw = [$rId => ['name' => $name, 'target' => $target, 'system' => true]];
    save('rewrite', $rw);
}

/**
 * Delete listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_delete(array & $data)
{
    delete('rewrite', ['target' => $data['_entity']['id'] . '/view/id/' . $data['id']], ['system' => true]);
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
    if (empty($data['_old'])) {
        return;
    }

    $crit = ['target' => $data['_old']['id'] . '/view/id/'];

    if (!data_action('view', $data)) {
        delete('rewrite', $crit, ['search' => true, 'system' => true]);
    } elseif ($data['id'] !== $data['_old']['id'] && ($rw = all('rewrite', $crit, ['search' => true]))) {
        foreach ($rw as $rId => $r) {
            $rw[$rId]['target'] = preg_replace('#^' . $data['_old']['id'] . '/#', $data['id'] . '/', $r['target']);
        }

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
    delete('rewrite', ['target' => $data['id'] . '/view/id/'], ['search' => true, 'system' => true]);
}

/**
 * Project save listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_project_save(array & $data)
{
    if (empty($data['_old']['id']) || $data['id'] === $data['_old']['id']) {
        return;
    }

    foreach (['asset', 'media'] as $dir) {
        $old = path($dir, $data['_old']['id']);
        $new = path($dir, $data['id']);

        if (file_exists($old) && !rename($old, $new)) {
            message(_('Could not move directory %s to %s', $old, $new));
        }
    }
}

/**
 * Project save listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_project_delete(array & $data)
{
    if (!file_delete(path('asset', $data['id']))) {
        message(_('Could not delete directory %s', path('asset', $data['id'])));
    }

    if (!file_delete(path('media', $data['id']))) {
        message(_('Could not delete directory %s', path('asset', $data['id'])));
    }
}
