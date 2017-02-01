<?php
namespace qnd;

/**
 * App data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_app(array & $data): void
{
    ini_set('default_charset', $data['charset']);
    ini_set('intl.default_locale', $data['locale']);
    ini_set('date.timezone', $data['timezone']);
}

/**
 * Entity data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_entity(array & $data): void
{
    foreach ($data as $eUid => $item) {
        $item['uid'] = $eUid;
        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], ['sort' => 'asc']);
        $data[$eUid] = $item;
    }

    if (!$entities = all('entity', ['project_id' => project('ids')])) {
        return;
    }

    $attrs = all('attr', ['project_id' => project('ids')], ['index' => ['entity_id', 'uid']]);

    foreach ($entities as $id => $item) {
        if (!empty($data[$item['uid']])) {
            message(_('Skipping data for Entity %s because UID %s is already in use', $item['name'], $item['uid']));
            continue;
        }

        $item = array_replace($data['content'], $item, ['model' => 'eav']);

        if (!empty($attrs[$id])) {
            foreach ($attrs[$id] as $uid => $attr) {
                if (empty($item['attr'][$uid])) {
                    unset($attr['_old'], $attr['_entity'], $attr['_id'], $attr['project_id'], $attr['entity_id']);
                    $item['attr'][$uid] = $attr;
                }
            }
        }

        unset($item['_old'], $item['_entity'], $item['_id'], $item['project_id']);
        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], ['sort' => 'asc']);
        $data[$item['uid']] = $item;
    }
}

/**
 * Privilege data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_privilege(array & $data): void
{
    foreach ($data as $id => $item) {
        if (!empty($item['callback'])) {
            $data[$id]['callback'] = fqn($item['callback']);
        }
    }

    foreach (data('entity') as $eUid => $entity) {
        foreach ($entity['actions'] as $action) {
            $data[$eUid . '.' . $action] = [
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
function listener_data_request(array & $data): void
{
    $data['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
    $data['ssl'] = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) === 'https' || ($_SERVER['HTTPS'] ?? null) === 'on';
    $data['scheme'] = $data['ssl'] ? 'https' : 'http';
    $data['get'] = http_filter($_GET);
    $data['post'] = !empty($_POST['token']) && http_post_validate($_POST['token']) ? $_POST : [];
    $data['files'] = $_FILES ? http_files_convert($_FILES) : [];
    $url = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $data['url'] = preg_replace('#^' . $_SERVER['SCRIPT_NAME'] . '#', '', $url);
    $parts = explode('/', trim(url_rewrite($data['url']), '/'));
    $data['entity'] = $parts[0] ?: $data['entity'];
    $data['action'] = $parts[1] ?? $data['action'];
    $data['id'] = $parts[2] ?? $data['id'];
    $data['path'] = $data['entity'] . '/' . $data['action'];
}

/**
 * Toolbar data listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_data_toolbar(array & $data): void
{
    $entities = array_filter(
        data('entity'),
        function ($item) {
            return !empty($item['id']);
        }
    );
    foreach ($entities as $entity) {
        if (in_array('admin', $entity['actions']) && allowed($entity['uid'] . '.admin')) {
            $data['content']['children'][] = ['name' => $entity['name'], 'url' => url($entity['uid'] . '/admin')];
        }
    }

    $filter = function (array $item) use (& $filter) {
        $item['children'] = !empty($item['children']) ? array_filter($item['children'], $filter) : [];

        return !$item['url'] && $item['children'] || $item['url'] && allowed(privilege_url($item['url']));
    };
    $data = array_filter($data, $filter);
}

/**
 * Save listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_save(array & $data): void
{
    if ($data['_entity']['uid'] === 'url'
        || !in_array('view', $data['_entity']['actions'])
        || $data['name'] === ($data['_old']['name'] ?? null)
    ) {
        return;
    }

    $target = '/' . $data['_entity']['uid'] . '/view/' . $data['id'];
    $old = one('url', ['target' => $target, 'system' => true]);
    $id = $old['id'] ?? -1;
    $base = filter_url($data['name']);
    $ext = data('app', 'url');
    $name = '/' . $base . $ext;

    for ($i = 1; ($url = one('url', ['name' => $name])) && $url['target'] !== $target; $i++) {
        $name = '/' . $base . '-' . $i . $ext;
    }

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
function listener_delete(array & $data): void
{
    delete('url', ['target' => '/' . $data['_entity']['uid'] . '/view/' . $data['id']], ['system' => true]);
}


/**
 * EAV save listener
 *
 * @param array $data
 *
 * @return void
 */
function listener_eav_save(array & $data): void
{
    $data['search'] = '';

    foreach ($data['_entity']['attr'] as $attr) {
        if ($attr['searchable']) {
            $data['search'] .= ' ' . str_replace("\n", ' ', strip_tags($data[$attr['uid']]));
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
function listener_project_save(array & $data): void
{
    if (empty($data['_old']['uid']) || $data['uid'] === $data['_old']['uid']) {
        return;
    }

    $old = path('asset', $data['_old']['uid']);
    $new = path('asset', $data['uid']);

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
function listener_project_delete(array & $data): void
{
    if (!file_delete(path('asset', $data['uid']))) {
        message(_('Could not delete directory %s', path('asset', $data['uid'])));
    }
}
