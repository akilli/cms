<?php
declare(strict_types = 1);

namespace entity;

use function app\_;
use function session\msg;
use function sql\trans;
use attr;
use app;
use Exception;
use RuntimeException;

const ENTITY = [
    'id' => null,
    'name' => null,
    'tab' => null,
    'model' => 'db',
    'act' => [],
    'attr' => []
];
const CRIT = [
    '=' => '=',
    '!=' => '!=',
    '>' => '>',
    '>=' => '>=',
    '<' => '>',
    '<=' => '<=',
    '~' => '~',
    '!~' => '!~',
    '~^' => '~^',
    '!~^' => '!~^',
    '~$' => '~$',
    '!~$' => '!~$',
];
const OPTS = [
    'mode' => 'all',
    'index' => 'id',
    'select' => [],
    'order' => [],
    'limit' => 0,
    'offset' => 0
];

/**
 * Size entity
 */
function size(string $eId, array $crit = []): int
{
    $entity = app\cfg('entity', $eId);
    $opts = ['mode' => 'size'] + OPTS;

    try {
        return ($entity['model'] . '\load')($entity, $crit, $opts)[0];
    } catch (Exception $e) {
        app\logger((string) $e);
        msg(_('Could not load data'));
    }

    return 0;
}

/**
 * Load one entity
 */
function one(string $eId, array $crit = [], array $opts = []): array
{
    $entity = app\cfg('entity', $eId);
    $data = [];
    $opts = array_replace(OPTS, array_intersect_key($opts, OPTS), ['mode' => 'one', 'limit' => 1]);

    try {
        if ($data = ($entity['model'] . '\load')($entity, $crit, $opts)) {
            $data = load($entity, $data);
        }
    } catch (Exception $e) {
        app\logger((string) $e);
        msg(_('Could not load data'));
    }

    return $data;
}

/**
 * Load entity collection
 */
function all(string $eId, array $crit = [], array $opts = []): array
{
    $entity = app\cfg('entity', $eId);
    $opts = array_replace(OPTS, array_intersect_key($opts, OPTS), ['mode' => 'all']);

    if ($opts['select']) {
        foreach (array_unique(['id', $opts['index']]) as $k) {
            if (!in_array($k, $opts['select'])) {
                $opts['select'][] = $k;
            }
        }
    }

    try {
        $data = ($entity['model'] . '\load')($entity, $crit, $opts);

        foreach ($data as $id => $item) {
            $data[$id] = load($entity, $item);
        }

        return array_column($data, null, $opts['index']);
    } catch (Exception $e) {
        app\logger((string) $e);
        msg(_('Could not load data'));
    }

    return [];
}

/**
 * Save entity
 */
function save(string $eId, array & $data): bool
{
    $tmp = $data;
    $edit = data($eId);

    if (!empty($tmp['id']) && ($base = one($eId, [['id', $tmp['id']]]))) {
        $tmp['_old'] = $base;
        unset($tmp['_old']['_entity'], $tmp['_old']['_old']);
    }

    $tmp = array_replace($edit, $tmp);
    $attrs = $tmp['_entity']['attr'];
    $aIds = [];

    foreach ($tmp as $aId => $val) {
        if (($val === null || $val === '') && !empty($attrs[$aId]) && attr\ignorable($attrs[$aId], $tmp)) {
            unset($tmp[$aId]);
        } elseif (!empty($attrs[$aId]) && array_key_exists($aId, $edit)) {
            $aIds[] = $aId;
        }
    }

    if (!$aIds) {
        return true;
    }

    $name = $tmp['name'] ?? $tmp['_old']['name'] ?? '';

    foreach ($aIds as $aId) {
        try {
            $tmp = attr\validator($tmp['_entity']['attr'][$aId], $tmp);
        } catch (Exception $e) {
            $data['_error'][$aId] = $e->getMessage();
        }
    }

    if (!empty($data['_error'])) {
        msg(_('Could not save %s', $name));
        return false;
    }

    $trans = trans(
        function () use (& $tmp, $aIds): void {
            $tmp = app\event('entity.presave', $tmp);
            $tmp = app\event('model.presave.' . $tmp['_entity']['model'], $tmp);
            $tmp = app\event('entity.presave.' . $tmp['_entity']['id'], $tmp);
            $tmp = ($tmp['_entity']['model'] . '\save')($tmp);
            app\event('entity.postsave', $tmp);
            app\event('model.postsave.' . $tmp['_entity']['model'], $tmp);
            app\event('entity.postsave.' . $tmp['_entity']['id'], $tmp);
        }
    );

    if ($trans) {
        msg(_('Successfully saved %s', $name));
        $data = $tmp;
    } else {
        msg(_('Could not save %s', $name));
    }

    return $trans;
}

/**
 * Delete entity
 */
function delete(string $eId, array $crit = [], array $opts = []): bool
{
    $success = [];
    $error = [];

    foreach (all($eId, $crit, $opts) as $id => $data) {
        if (!empty($data['system'])) {
            msg(_('System items must not be deleted! Therefore skipped ID %s', (string) $id));
            continue;
        }

        $trans = trans(
            function () use ($data): void {
                $data = app\event('entity.predelete', $data);
                $data = app\event('model.predelete.' . $data['_entity']['model'], $data);
                $data = app\event('entity.predelete.' . $data['_entity']['id'], $data);
                ($data['_entity']['model'] . '\delete')($data);
                app\event('entity.postdelete', $data);
                app\event('model.postdelete.' . $data['_entity']['model'], $data);
                app\event('entity.postdelete.' . $data['_entity']['id'], $data);
            }
        );

        if ($trans) {
            $success[] = $data['name'];
        } else {
            $error[] = $data['name'];
        }
    }

    if ($success) {
        msg(_('Successfully deleted %s', implode(', ', $success)));
    }

    if ($error) {
        msg(_('Could not delete %s', implode(', ', $error)));
    }

    return !$error;
}

/**
 * Retrieve empty entity
 *
 * @throws RuntimeException
 */
function data(string $eId, bool $bare = false): array
{
    if (!$entity = app\cfg('entity', $eId)) {
        throw new RuntimeException(_('Invalid entity %s', $eId));
    }

    $item = array_fill_keys(array_keys(attr($entity, 'edit')), null);

    return $bare ? $item : $item + ['_old' => null, '_entity' => $entity];
}

/**
 * Retrieve entity attributes filtered by given action
 */
function attr(array $entity, string $act): array
{
    foreach ($entity['attr'] as $aId => $attr) {
        if (!in_array($act, $attr['act'])) {
            unset($entity['attr'][$aId]);
        }
    }

    return $entity['attr'];
}

/**
 * Internal entity loader
 */
function load(array $entity, array $data): array
{
    foreach ($data as $aId => $val) {
        if (!empty($entity['attr'][$aId])) {
            $data[$aId] = attr\loader($entity['attr'][$aId], $data);
        }
    }

    $data['_old'] = $data;
    $data['_entity'] = $entity;
    $data = app\event('entity.load', $data);
    $data = app\event('model.load.' . $entity['model'], $data);
    $data = app\event('entity.load.' . $entity['id'], $data);

    return $data;
}
