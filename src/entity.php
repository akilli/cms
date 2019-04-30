<?php
declare(strict_types = 1);

namespace entity;

use account;
use arr;
use attr;
use app;
use file;
use request;
use DomainException;
use Throwable;

/**
 * Size entity
 *
 * @throws DomainException
 */
function size(string $entityId, array $crit = []): int
{
    if (!$entity = app\cfg('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    $opt = ['mode' => 'size'] + APP['entity.opt'];

    try {
        return ($entity['type'] . '\load')($entity, $crit, $opt)[0];
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not load data');
    }

    return 0;
}

/**
 * Load one entity
 *
 * @throws DomainException
 */
function one(string $entityId, array $crit = [], array $opt = []): array
{
    if (!$entity = app\cfg('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    $data = [];
    $opt = ['mode' => 'one', 'limit' => 1] + arr\replace(APP['entity.opt'], $opt);

    try {
        if ($data = ($entity['type'] . '\load')($entity, $crit, $opt)) {
            $data = load($entity, $data);
        }
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not load data');
    }

    return $data;
}

/**
 * Load entity collection
 *
 * @throws DomainException
 */
function all(string $entityId, array $crit = [], array $opt = []): array
{
    if (!$entity = app\cfg('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    $opt = ['mode' => 'all'] + arr\replace(APP['entity.opt'], $opt);

    if ($opt['select'] && ($keys = array_diff(array_unique(['id', $opt['index']]), $opt['select']))) {
        array_unshift($opt['select'], ...$keys);
    }

    try {
        $data = ($entity['type'] . '\load')($entity, $crit, $opt);

        foreach ($data as $key => $item) {
            $data[$key] = load($entity, $item);
        }

        return array_column($data, null, $opt['index']);
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not load data');
    }

    return [];
}

/**
 * Save entity
 *
 * @throws DomainException
 */
function save(string $entityId, array & $data): bool
{
    if (!$entity = app\cfg('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    } elseif ($entity['readonly']) {
        throw new DomainException(app\i18n('Entity %s is readonly', $entity['id']));
    }

    $id = $data['id'] ?? null;
    $tmp = $data;
    $tmp['_old'] = [];
    $tmp['_entity'] = $entity;

    if ($id && ($old = one($entity['id'], [['id', $id]]))) {
        $tmp['_old'] = $old;
        unset($tmp['entity_id'], $tmp['_old']['_entity'], $tmp['_old']['_old']);
    } elseif ($entity['parent_id']) {
        $tmp['entity_id'] = $entity['id'];
    }

    $attrIds = [];

    foreach (array_intersect(array_keys($tmp), array_keys($entity['attr'])) as $attrId) {
        $attr = $entity['attr'][$attrId];
        $tmp[$attrId] = attr\cast($tmp[$attrId], $attr);
        $ignorable = ($tmp[$attrId] === null || $tmp[$attrId] === '') && $attr['required'] && attr\ignorable($tmp, $attr);
        $unchanged = array_key_exists($attrId, $tmp['_old']) && $tmp[$attrId] === $tmp['_old'][$attrId];

        if ($ignorable || $unchanged) {
            unset($data[$attrId], $tmp[$attrId]);
        } else {
            $attrIds[] = $attrId;
        }
    }

    if (!$attrIds) {
        app\msg('No changes');
        return false;
    }

    $tmp = event('prevalidate', $tmp);

    foreach ($attrIds as $attrId) {
        try {
            $tmp[$attrId] = attr\validator($tmp, $entity['attr'][$attrId]);
        } catch (DomainException $e) {
            $tmp['_error'][$attrId][] = $e->getMessage();
        } catch (Throwable $e) {
            app\log($e);
            $tmp['_error'][$attrId][] = app\i18n('Could not validate value');
        }
    }

    $tmp = event('postvalidate', $tmp);

    if (!empty($tmp['_error'])) {
        $data['_error'] = $tmp['_error'];
        app\msg('Could not save data');
        return false;
    }

    foreach ($attrIds as $key => $attrId) {
        if (array_key_exists($attrId, $tmp['_old']) && $tmp[$attrId] === $tmp['_old'][$attrId]) {
            unset($data[$attrId], $tmp[$attrId], $attrIds[$key]);
        }
    }

    if (!$attrIds) {
        app\msg('No changes');
        return false;
    }

    try {
        ($entity['type'] . '\trans')(
            function () use (& $tmp): void {
                $tmp = event('presave', $tmp);
                $tmp = ($tmp['_entity']['type'] . '\save')($tmp);
                $tmp = event('postsave', $tmp);
            },
            $entity['db']
        );
        app\msg('Successfully saved data');
        $data = $tmp;

        if (empty($data['id']) && !empty($tmp['_old']['id'])) {
            $data['id'] = $tmp['_old']['id'];
        }

        return true;
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not save data');
    }

    return false;
}

/**
 * Delete entity
 *
 * @throws DomainException
 */
function delete(string $entityId, array $crit = [], array $opt = []): bool
{
    if (!$entity = app\cfg('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    } elseif ($entity['readonly']) {
        throw new DomainException(app\i18n('Entity %s is readonly', $entity['id']));
    }

    if (!$all = all($entity['id'], $crit, $opt)) {
        app\msg('Nothing to delete');
        return false;
    }

    try {
        ($entity['type'] . '\trans')(
            function () use ($all): void {
                foreach ($all as $data) {
                    $data = event('predelete', $data);
                    ($data['_entity']['type'] . '\delete')($data);
                    event('postdelete', $data);
                }
            },
            $entity['db']
        );
        app\msg('Successfully deleted data');

        return true;
    } catch (DomainException $e) {
        app\msg($e->getMessage());
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not delete data');
    }

    return false;
}

/**
 * Retrieve empty entity
 *
 * @throws DomainException
 */
function item(string $entityId): array
{
    if (!$entity = app\cfg('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    return array_fill_keys(array_keys($entity['attr']), null) + ['_old' => [], '_entity' => $entity, '_error' => []];
}

/**
 * Load entity
 */
function load(array $entity, array $data): array
{
    foreach (array_intersect_key($data, $entity['attr']) as $attrId => $val) {
        $data[$attrId] = attr\cast($val, $entity['attr'][$attrId]);
    }

    $data += ['_old' => $data, '_entity' => $entity];

    return event('load', $data);
}

/**
 * Dispatches multiple entity events
 */
function event(string $name, array $data): array
{
    $entity = $data['_entity'];
    $ev = ['entity.' . $name, 'entity.' . $name . '.type.' . $entity['type'], 'entity.' . $name . '.db.' . $entity['db']];

    if ($entity['parent_id']) {
        $ev[] = 'entity.' . $name . '.id.' . $entity['parent_id'];
    }

    $ev[] = 'entity.' . $name . '.id.' . $entity['id'];

    return app\event($ev, $data);
}

/**
 * Entity postvalidate
 */
function listener_postvalidate(array $data): array
{
    $attrs = $data['_entity']['attr'];

    foreach (array_intersect_key($data, $data['_entity']['attr']) as $attrId => $val) {
        if ($attrs[$attrId]['type'] === 'password' && $val && !($data[$attrId] = password_hash($val, PASSWORD_DEFAULT))) {
            $data['_error'][$attrId][] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * File entity prevalidate
 */
function listener_prevalidate_file(array $data): array
{
    if ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url'])) {
        if (!$item = request\file('url')) {
            $data['_error']['url'][] = app\i18n('No upload file');
        } else {
            $data['ext'] = pathinfo($data['url'], PATHINFO_EXTENSION);
            $data['mime'] = $item['type'];

            if ($data['_old'] && ($data['ext'] !== $data['_old']['ext'] || $data['mime'] !== $data['_old']['mime'])) {
                $data['_error']['url'][] = app\i18n('Cannot change filetype anymore');
            }
        }
    }

    if (!empty($data['thumb_url']) && ($item = request\file('thumb_url'))) {
        $data['thumb_ext'] = pathinfo($data['thumb_url'], PATHINFO_EXTENSION);
        $data['thumb_mime'] = $item['type'];
    }

    return $data;
}

/**
 * Page entity presave
 */
function listener_presave_page(array $data): array
{
    $data['account_id'] = account\data('id');

    return $data;
}

/**
 * File entity postsave
 *
 * @throws DomainException
 */
function listener_postsave_file(array $data): array
{
    $id = $data['id'] ?? $data['_old']['id'] ?? null;
    $uploadable = $data['_entity']['attr']['url']['uploadable'];

    if ($uploadable && ($item = request\file('url')) && (!$id || !file\upload($item['tmp_name'], app\path('file', $id . '.' . $data['ext'])))) {
        throw new DomainException(app\i18n('File upload failed for %s', $item['name']));
    } elseif (($item = request\file('thumb_url')) && (!$id || !file\upload($item['tmp_name'], app\path('file', $id . APP['file.thumb'] . $data['thumb_ext'])))) {
        throw new DomainException(app\i18n('File upload failed for %s', $item['name']));
    }

    if (array_key_exists('thumb_url', $data)
        && !$data['thumb_url']
        && $data['_old']['thumb_url']
        && !file\delete(app\path('file', $data['_old']['id'] . APP['file.thumb'] . $data['_old']['thumb_ext']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * File entity postdelete
 *
 * @throws DomainException
 */
function listener_postdelete_file(array $data): array
{
    if ($data['_entity']['attr']['url']['uploadable']
        && !file\delete(app\path('file', $data['_old']['id'] . '.' . $data['_old']['ext']))
        && !file\delete(app\path('file', $data['_old']['id'] . APP['file.thumb'] . $data['_old']['thumb_ext']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * Layout entity posvalidate
 */
function listener_postvalidate_layout(array $data): array
{
    if (empty($data['name']) || empty($data['page_id']) || empty($data['parent_id'])) {
        return $data;
    }

    $crit = [['name', $data['name']], ['page_id', $data['page_id']], ['parent_id', $data['parent_id']]];

    if (!empty($data['_old']['id'])) {
        $crit[] = ['id', $data['_old']['id'], APP['op']['!=']];
    }

    if (size('layout', $crit)) {
        $data['_error']['name'][] = app\i18n('Name must be unique for selected parent block and page');
    }

    return $data;
}

/**
 * Page entity postvalidate status
 */
function listener_postvalidate_page_status(array $data): array
{
    if (!empty($data['parent_id']) && ($parent = one('page', [['id', $data['parent_id']]], ['select' => ['status']]))) {
        if ($parent['status'] === 'archived' && (!$data['_old'] || $data['parent_id'] !== $data['_old']['parent_id'])) {
            $data['_error']['parent_id'][] = app\i18n('Cannot assign archived page as parent');
        } elseif (in_array($parent['status'], ['draft', 'pending']) && !empty($data['status']) && $data['status'] !== 'draft') {
            $data['_error']['status'][] = app\i18n('Status must be draft, because parent was not published yet');
        }
    }

    return $data;
}

/**
 * Page entity postvalidate menu
 */
function listener_postvalidate_page_menu(array $data): array
{
    if ($data['_old']
        && !empty($data['parent_id'])
        && ($parent = one('page', [['id', $data['parent_id']]], ['select' => ['path']]))
        && in_array($data['_old']['id'], $parent['path'])
    ) {
        $data['_error']['parent_id'][] = app\i18n('Cannot assign the page itself or a subpage as parent');
    }

    return $data;
}

/**
 * Page entity postvalidate URL
 */
function listener_postvalidate_page_url(array $data): array
{
    if ((!array_key_exists('slug', $data) || $data['_old'] && $data['slug'] === $data['_old']['slug'])
        && (!array_key_exists('parent_id', $data) || $data['_old'] && $data['parent_id'] === $data['_old']['parent_id'])
    ) {
        return $data;
    }

    if (array_key_exists('slug', $data)) {
        $slug = $data['slug'];
    } elseif (array_key_exists('slug', $data['_old'])) {
        $slug = $data['_old']['slug'];
    } else {
        $slug = null;
    }

    if (array_key_exists('parent_id', $data)) {
        $parentId = $data['parent_id'];
    } elseif (array_key_exists('parent_id', $data['_old'])) {
        $parentId = $data['_old']['parent_id'];
    } else {
        $parentId = null;
    }

    $root = one('page', [['url', '/']], ['select' => ['id']]);

    if ($parentId === null || $parentId === $root['id']) {
        $parentId = [null, $root['id']];
    }

    $crit = [['slug', $slug], ['parent_id', $parentId]];

    if ($data['_old']) {
        $crit[] = ['id', $data['_old']['id'], APP['op']['!=']];
    }

    if (size('page', $crit)) {
        $data['_error']['slug'][] = app\i18n('Please change slug to generate an unique URL');
    }

    return $data;
}

/**
 * Page entity load
 */
function listener_load_page(array $data): array
{
    if (array_key_exists('content', $data)) {
        $data['teaser'] = preg_match('#^(<p[^>]*>.*?</p>)#', trim($data['content']), $m) ? $m[1] : '';
    }

    return $data;
}

/**
 * Role entity predelete
 *
 * @throws DomainException
 */
function listener_predelete_role(array $data): array
{
    if (size('account', [['role_id', $data['id']]])) {
        throw new DomainException(app\i18n('Cannot delete used role'));
    }

    return $data;
}
