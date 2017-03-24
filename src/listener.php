<?php
declare(strict_types = 1);

namespace qnd;

/**
 * App data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_app(array $data): array
{
    ini_set('default_charset', $data['charset']);
    ini_set('intl.default_locale', $data['locale']);
    ini_set('date.timezone', $data['timezone']);

    return $data;
}

/**
 * Attribute data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_attr(array $data): array
{
    $data = array_map(
        function ($item) {
            $item['name'] = _($item['name']);
            return $item;
        },
        $data
    );

    return data_order($data, ['name' => 'asc']);
}

/**
 * Entity data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_entity(array $data): array
{
    foreach ($data as $eId => $item) {
        $item['id'] = $eId;
        $item = data_entity($item);
        $item['attr'] = data_order($item['attr'], ['sort' => 'asc']);
        $data[$eId] = $item;
    }

    return $data;
}

/**
 * I18n data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_i18n(array $data): array
{
    return $data + data('i18n.' . data('app', 'lang'));
}

/**
 * Option data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_opt(array $data): array
{
    foreach ($data as $key => $value) {
        foreach ($value as $k => $v) {
            $data[$key][$k] = _($v);
        }

        asort($data[$key]);
    }

    return $data;
}

/**
 * Privilege data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_privilege(array $data): array
{
    foreach ($data as $id => $item) {
        if (!empty($item['callback'])) {
            $data[$id]['callback'] = fqn($item['callback']);
        }

        $data[$id]['name'] = _($item['name']);
    }

    foreach (data('entity') as $eId => $entity) {
        foreach ($entity['actions'] as $action) {
            $data[$eId . '.' . $action]['name'] = $entity['name'] . ' ' . _(ucwords($action));
        }
    }

    return data_order($data, ['sort' => 'asc', 'name' => 'asc']);
}

/**
 * Request data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_request(array $data): array
{
    $data['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
    $data['get'] = http_filter($_GET);
    $data['post'] = !empty($_POST['token']) && http_post_validate($_POST['token']) ? $_POST : [];
    $data['files'] = $_FILES ? http_files_convert($_FILES) : [];
    $url = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $data['url'] = preg_replace('#^' . $_SERVER['SCRIPT_NAME'] . '#', '', $url);
    $parts = explode('/', trim($data['url'], '/'));
    $data['entity'] = $parts[0] ?: $data['entity'];
    $data['action'] = $parts[1] ?? $data['action'];
    $data['id'] = $parts[2] ?? $data['id'];
    $data['path'] = $data['entity'] . '/' . $data['action'];

    return $data;
}

/**
 * Toolbar data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_toolbar(array $data): array
{
    foreach ($data as $key => $item) {
        if (allowed(privilege_url($item['url']))) {
            $data[$key]['name'] = _($item['name']);
        } else {
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Project save listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_project_save(array $data): array
{
    if (empty($data['_old']['uid']) || $data['uid'] === $data['_old']['uid']) {
        return $data;
    }

    $old = path('asset', $data['_old']['uid']);
    $new = path('asset', $data['uid']);

    if (file_exists($old) && !rename($old, $new)) {
        message(_('Could not move directory %s to %s', $old, $new));
    }

    return $data;
}

/**
 * Project delete listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_project_delete(array $data): array
{
    if (!file_delete(path('asset', $data['uid']))) {
        message(_('Could not delete directory %s', path('asset', $data['uid'])));
    }

    return $data;
}

/**
 * Page save listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_page_save(array $data): array
{
    $data['search'] = '';

    foreach ($data['_entity']['attr'] as $attr) {
        if ($attr['searchable']) {
            $data['search'] .= ' ' . str_replace("\n", ' ', strip_tags($data[$attr['id']]));
        }
    }

    if ($data['name'] !== ($data['_old']['name'] ?? null)) {
        $base = filter_id($data['name']);
        $data['uid'] = $base;

        for ($i = 1; one('page', ['uid' => $data['uid']]); $i++) {
            $data['uid'] = $base . '-' . $i;
        }
    }

    return $data;
}
