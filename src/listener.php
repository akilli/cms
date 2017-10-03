<?php
declare(strict_types = 1);

namespace cms;

use RuntimeException;

/**
 * Account data listener
 *
 * @param array $data
 *
 * @return array
 */
function listener_data_account(array $data): array
{
    $id = (int) session_get('account');

    if ($id && ($account = one('account', [['id', $id], ['active', true]]))) {
        $role = one('role', [['id', $account['role_id']], ['active', true]]);
        $data = $account;
        $data['privilege'] = $role ? $role['privilege'] : [];
        $data['admin'] = in_array(ALL, $data['privilege']);
        unset($data['_old'], $data['_entity']);
    } else {
        session_set('account', null);
    }

    return $data;
}

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
    $cfg = data('attr');

    foreach ($data as $eId => $entity) {
        $entity = array_replace(ENTITY, $entity);

        if (!$entity['name'] || !$entity['model'] || !$entity['attr']) {
            throw new RuntimeException(_('Invalid entity configuration'));
        }

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
            $attr = array_replace(ATTR, $backend, $frontend, $type, $attr);
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
    }

    foreach (data('entity') as $eId => $entity) {
        foreach ($entity['actions'] as $act) {
            $data[$eId . '/' . $act]['name'] = $entity['name'] . ' ' . _(ucwords($act));
        }
    }

    return arr_order($data, ['sort' => 'asc', 'name' => 'asc']);
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
        if (allowed_url($item['url'])) {
            $data[$key]['name'] = _($item['name']);
        } else {
            unset($data[$key]);
        }
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
    if ($data['name'] !== ($data['_old']['name'] ?? null)) {
        $base = filter_id($data['name']);
        $data['url'] = url($base . URL['page']);

        for ($i = 1; one('page', [['url', $data['url']]]); $i++) {
            $data['url'] = url($base . '-' . $i . URL['page']);
        }
    }

    return $data;
}
