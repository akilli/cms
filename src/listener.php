<?php
declare(strict_types = 1);

namespace qnd;

use RuntimeException;

/**
 * App config listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_config_app(array $data): array
{
    ini_set('default_charset', $data['charset']);
    ini_set('intl.default_locale', $data['locale']);
    ini_set('date.timezone', $data['timezone']);

    return $data;
}

/**
 * Entity config listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_config_entity(array $data): array
{
    $defEnt = config('default', 'entity');
    $defAttr = config('default', 'attr');
    $cfg = config('attr');

    foreach ($data as $eId => $entity) {
        if (empty($entity['name']) || empty($entity['attr'])) {
            throw new RuntimeException(_('Invalid entity configuration'));
        }

        $entity = array_replace($defEnt, $entity);
        $entity['id'] = $eId;
        $entity['name'] = _($entity['name']);
        $entity['tab'] = $entity['tab'] ?: $entity['id'];
        $sort = 0;

        foreach ($entity['attr'] as $id => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || !($type = $cfg['type'][$attr['type']] ?? null)) {
                throw new RuntimeException(_('Invalid attribute configuration'));
            }

            $backend = $cfg['backend'][$attr['backend'] ?? $type['backend']];
            $frontend = $cfg['frontend'][$attr['frontend'] ?? $type['frontend']];
            $attr = array_replace($defAttr, $backend, $frontend, $type, $attr);
            $attr['id'] = $id;
            $attr['name'] = _($attr['name']);
            $attr['entity'] = $entity['id'];

            if ($attr['col'] === false) {
                $attr['col'] = null;
            } elseif (!$attr['col']) {
                $attr['col'] = $attr['id'];
            }

            if (!is_numeric($attr['sort'])) {
                $attr['sort'] = $sort;
                $sort += 100;
            }

            $entity['attr'][$id] = $attr;
        }

        $entity['attr'] = arr_order($entity['attr'], ['sort' => 'asc']);
        $data[$eId] = $entity;
    }

    return $data;
}

/**
 * I18n config listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_config_i18n(array $data): array
{
    return $data + config('i18n.' . config('app', 'lang'));
}

/**
 * Privilege config listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_config_privilege(array $data): array
{
    foreach ($data as $id => $item) {
        $data[$id]['name'] = !empty($item['name']) ? _($item['name']) : '';
        $data[$id]['callback'] = !empty($item['callback']) ? fqn($item['callback']) : null;
    }

    foreach (config('entity') as $eId => $entity) {
        foreach ($entity['actions'] as $act) {
            $data[$eId . '/' . $act]['name'] = $entity['name'] . ' ' . _(ucwords($act));
        }
    }

    return arr_order($data, ['sort' => 'asc', 'name' => 'asc']);
}

/**
 * Request config listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_config_request(array $data): array
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
 * Toolbar config listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_config_toolbar(array $data): array
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
