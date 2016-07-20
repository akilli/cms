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
    $data['ext.file'] = $data['ext.file'] + $data['ext.audio'] + $data['ext.embed'] + $data['ext.image'] + $data['ext.video'];
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

    foreach (data('entity') as $eId => $entity) {
        foreach ($entity['actions'] as $action) {
            $data[$eId . '.' . $action] = [
                'name' => $entity['name'] . ' ' . ucwords($action),
                'active' => true,
            ];
        }
    }

    $data = data_order($data, ['sort' => 'asc', 'name' => 'asc']);
}

/**
 * Request data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_request(array & $data)
{
    $data['host'] = $_SERVER['HTTP_HOST'];
    $data['scheme'] = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $data['secure'] = $data['scheme'] === 'https';
    $data['get'] = $_GET;
    $data['post'] = !empty($_POST['token']) && http_post_validate($_POST['token']) ? $_POST : [];
    $data['files'] = $_FILES ? http_files_convert($_FILES) : [];
    $url = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $data['url'] = preg_replace('#^' . $_SERVER['SCRIPT_NAME'] . '#', '', $url);
    $data['path'] = url_rewrite($data['url'], true);

    if (!$p = trim($data['path'], '/')) {
        return;
    }

    $parts = explode('/', $p);
    $data['entity'] = array_shift($parts);
    $data['action'] = array_shift($parts) ?? $data['action'];
    $data['id'] = $data['entity'] . '.' . $data['action'];
    $count = count($parts);

    for ($i = 0; $i < $count; $i += 2) {
        if (!empty($parts[$i]) && isset($parts[$i + 1])) {
            $data['params'][$parts[$i]] = $parts[$i + 1];
        }
    }
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
    if ($data['_entity']['id'] === 'url' || !in_array('view', $data['_entity']['actions'])) {
        return;
    }

    $target = sprintf('%s%s/view/id/%s', url(), $data['_entity']['id'], $data['id']);
    $old = one('url', ['target' => $target, 'system' => true]);
    $id = $old['id'] ?? -1;
    $all = all('url', [], ['index' => 'name']);
    $name = generator_url($data['name'], array_column($all, 'name', 'id'), $id);
    $url = [$id => ['name' => $name, 'target' => $target, 'system' => true]];
    save('url', $url);
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
    delete('url', ['target' => sprintf('%s%s/view/id/%s', url(), $data['_entity']['id'], $data['id'])], ['system' => true]);
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

    $base = url();
    $crit = ['target' => sprintf('%s%s/view/id/', $base, $data['_old']['id'])];

    if (!in_array('view', $data['actions'])) {
        delete('url', $crit, ['search' => true, 'system' => true]);
    } elseif ($data['id'] !== $data['_old']['id'] && ($url = all('url', $crit, ['search' => true]))) {
        foreach ($url as $id => $u) {
            $from = sprintf('#^%s%s/#', $base, $data['_old']['id']);
            $to = sprintf('%s%s/', $base, $data['id']);
            $url[$id]['target'] = preg_replace($from, $to, $u['target']);
        }

        save('url', $url);
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
    delete('url', ['target' => sprintf('%s%s/view/id/', url(), $data['id'])], ['search' => true, 'system' => true]);
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

    $old = path('asset', $data['_old']['id']);
    $new = path('asset', $data['id']);

    if (file_exists($old) && !rename($old, $new)) {
        message(_('Could not move directory %s to %s', $old, $new));
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
}
