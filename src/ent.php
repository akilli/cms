<?php
declare(strict_types = 1);

namespace ent;

use attr;
use app;
use sql;
use RuntimeException;
use Throwable;

/**
 * Size entity
 */
function size(string $eId, array $crit = []): int
{
    $ent = app\cfg('ent', $eId);
    $opts = ['mode' => 'size'] + OPTS;

    try {
        return ($ent['type'] . '\load')($ent, $crit, $opts)[0];
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not load data'));
    }

    return 0;
}

/**
 * Load one entity
 */
function one(string $eId, array $crit = [], array $opts = []): array
{
    $ent = app\cfg('ent', $eId);
    $data = [];
    $opts = array_replace(OPTS, array_intersect_key($opts, OPTS), ['mode' => 'one', 'limit' => 1]);

    try {
        if ($data = ($ent['type'] . '\load')($ent, $crit, $opts)) {
            $data = load($ent, $data);
        }
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not load data'));
    }

    return $data;
}

/**
 * Load entity collection
 */
function all(string $eId, array $crit = [], array $opts = []): array
{
    $ent = app\cfg('ent', $eId);
    $opts = array_replace(OPTS, array_intersect_key($opts, OPTS), ['mode' => 'all']);

    if ($opts['select']) {
        foreach (array_unique(['id', $opts['index']]) as $k) {
            if (!in_array($k, $opts['select'])) {
                $opts['select'][] = $k;
            }
        }
    }

    try {
        $data = ($ent['type'] . '\load')($ent, $crit, $opts);

        foreach ($data as $id => $item) {
            $data[$id] = load($ent, $item);
        }

        return array_column($data, null, $opts['index']);
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not load data'));
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
        unset($tmp['_old']['_ent'], $tmp['_old']['_old']);
    }

    $tmp = array_replace($edit, $tmp);
    $attrs = $tmp['_ent']['attr'];
    $aIds = [];

    foreach ($tmp as $aId => $val) {
        if (($val === null || $val === '') && !empty($attrs[$aId]) && attr\ignorable($tmp, $attrs[$aId])) {
            unset($tmp[$aId]);
        } elseif (!empty($attrs[$aId]) && array_key_exists($aId, $edit)) {
            $aIds[] = $aId;
        }
    }

    if (!$aIds) {
        return true;
    }

    $tmp = event('prevalidate', $tmp);

    foreach ($aIds as $aId) {
        try {
            $tmp = attr\validator($tmp, $tmp['_ent']['attr'][$aId]);
        } catch (Throwable $e) {
            $tmp['_error'][$aId] = $e->getMessage();
        }
    }

    $name = $tmp['name'] ?? $tmp['_old']['name'] ?? '';
    $tmp = event('postvalidate', $tmp);

    if (!empty($tmp['_error'])) {
        $data['_error'] = $tmp['_error'];
        app\msg(app\i18n('Could not save %s', $name));
        return false;
    }

    try {
        sql\trans(
            function () use (& $tmp): void {
                $tmp = event('presave', $tmp);
                $tmp = ($tmp['_ent']['type'] . '\save')($tmp);
                $tmp = event('postsave', $tmp);
            }
        );
        app\msg(app\i18n('Successfully saved %s', $name));
        $data = $tmp;

        return true;
    } catch (Throwable $e) {
        app\msg(app\i18n('Could not save %s', $name));
    }

    return false;
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
            app\msg(app\i18n('System items must not be deleted! Therefore skipped ID %s', (string) $id));
            continue;
        }

        try {
            sql\trans(
                function () use ($data): void {
                    $data = event('predelete', $data);
                    ($data['_ent']['type'] . '\delete')($data);
                    event('postdelete', $data);
                }
            );
            $success[] = $data['name'];
        } catch (Throwable $e) {
            $error[] = $data['name'];
        }
    }

    if ($success) {
        app\msg(app\i18n('Successfully deleted %s', implode(', ', $success)));
    }

    if ($error) {
        app\msg(app\i18n('Could not delete %s', implode(', ', $error)));
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
    if (!$ent = app\cfg('ent', $eId)) {
        throw new RuntimeException(app\i18n('Invalid entity %s', $eId));
    }

    $item = array_fill_keys(array_keys(attr($ent, 'edit')), null);

    return $bare ? $item : $item + ['_old' => null, '_ent' => $ent];
}

/**
 * Retrieve entity attributes filtered by given action
 */
function attr(array $ent, string $act): array
{
    $aIds = $ent['act'][$act] ?? [];
    $attrs = [];

    foreach ($aIds as $aId) {
        $attrs[$aId] = $ent['attr'][$aId];
    }

    return $attrs;
}

/**
 * Load entity
 */
function load(array $ent, array $data): array
{
    foreach ($data as $aId => $val) {
        if (!empty($ent['attr'][$aId])) {
            $data[$aId] = attr\cast($val, $ent['attr'][$aId]);
        }
    }

    $data['_old'] = $data;
    $data['_ent'] = $ent;

    return event('load', $data);
}

/**
 * Dispatches multiple entity events
 */
function event(string $name, array $data): array
{
    $data = app\event('ent.' . $name, $data);
    $data = app\event('ent.type.' . $name . '.' . $data['_ent']['type'], $data);

    return app\event('ent.' . $name . '.' . $data['_ent']['id'], $data);
}
