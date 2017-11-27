<?php
declare(strict_types = 1);

namespace ent;

use arr;
use attr;
use app;
use sql;
use DomainException;
use Throwable;

/**
 * Size entity
 */
function size(string $eId, array $crit = []): int
{
    $ent = app\cfg('ent', $eId);
    $opt = arr\replace(APP['ent.opt'], ['mode' => 'size']);

    try {
        return ($ent['type'] . '\load')($ent, $crit, $opt)[0];
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not load data'));
    }

    return 0;
}

/**
 * Load one entity
 */
function one(string $eId, array $crit = [], array $opt = []): array
{
    $ent = app\cfg('ent', $eId);
    $data = [];
    $opt = arr\replace(APP['ent.opt'], $opt, ['mode' => 'one', 'limit' => 1]);

    try {
        if ($data = ($ent['type'] . '\load')($ent, $crit, $opt)) {
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
function all(string $eId, array $crit = [], array $opt = []): array
{
    $ent = app\cfg('ent', $eId);
    $opt = arr\replace(APP['ent.opt'], $opt, ['mode' => 'all']);

    if ($opt['select']) {
        foreach (array_unique(['id', $opt['index']]) as $k) {
            if (!in_array($k, $opt['select'])) {
                $opt['select'][] = $k;
            }
        }
    }

    try {
        $data = ($ent['type'] . '\load')($ent, $crit, $opt);

        foreach ($data as $key => $item) {
            $data[$key] = load($ent, $item);
        }

        return array_column($data, null, $opt['index']);
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
    $id = $data['_id'] ?? $data['id'] ?? null;
    unset($data['_id']);
    $tmp = $data;
    $edit = data($eId, 'edit');

    if ($id && ($base = one($eId, [['id', $id]]))) {
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

    $tmp = event('prefilter', $tmp);

    foreach ($aIds as $aId) {
        try {
            $tmp = attr\filter($tmp, $tmp['_ent']['attr'][$aId]);
        } catch (Throwable $e) {
            $tmp['_error'][$aId] = $e->getMessage();
        }
    }

    $tmp = event('postfilter', $tmp);
    $name = $tmp['name'] ?? $tmp['_old']['name'] ?? '';

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
function delete(string $eId, array $crit = [], array $opt = []): bool
{
    $success = [];
    $error = [];

    foreach (all($eId, $crit, $opt) as $id => $data) {
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
 * @throws DomainException
 */
function data(string $eId, string $act): array
{
    if (!$ent = app\cfg('ent', $eId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $eId));
    }

    return array_fill_keys($ent['act'][$act] ?? [], null) + ['_old' => null, '_ent' => $ent];
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

    $data += ['_old' => $data, '_ent' => $ent];

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
