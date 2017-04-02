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
 * Privilege data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_privilege(array $data): array
{
    foreach ($data as $id => $item) {
        $data[$id]['name'] = !empty($item['name']) ? _($item['name']) : '';
        $data[$id]['callback'] = !empty($item['callback']) ? fqn($item['callback']) : null;
    }

    foreach (data('entity') as $eId => $entity) {
        foreach ($entity['actions'] as $act) {
            $data[$eId . '/' . $act]['name'] = $entity['name'] . ' ' . _(ucwords($act));
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
    $data['host'] = $_SERVER['HTTP_HOST'];
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
 * Project post-delete listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_project_postdelete(array $data): array
{
    $asset = path('asset', (string) $data['id']);

    if (!file_delete($asset)) {
        message(_('Could not delete directory %s', $asset));
    }

    return $data;
}

/**
 * Page pre-save listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_page_presave(array $data): array
{
    $data['search'] = '';

    foreach ($data['_entity']['attr'] as $attr) {
        if ($attr['searchable']) {
            $data['search'] .= ' ' . str_replace("\n", ' ', strip_tags($data[$attr['id']]));
        }
    }

    if ($data['name'] !== ($data['_old']['name'] ?? null)) {
        $base = filter_id($data['name']);
        $data['url'] = url($base . URL);

        for ($i = 1; one('page', [['url', $data['url']], ['project_id', $data['project_id']]]); $i++) {
            $data['url'] = url($base . '-' . $i . URL);
        }
    }

    return $data;
}
