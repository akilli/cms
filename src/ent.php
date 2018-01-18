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
    $id = $data['id'] ?? null;
    $tmp = $data;

    if ($id && ($old = one($eId, [['id', $id]]))) {
        $tmp['_old'] = $old;
        $tmp['_ent'] = $old['_ent'];
        unset($tmp['_old']['_ent'], $tmp['_old']['_old']);
    } else {
        $tmp['_old'] = [];
        $tmp['_ent'] = app\cfg('ent', $eId);
    }

    $aIds = [];

    foreach (array_intersect_key($tmp, $tmp['_ent']['attr']) as $aId => $val) {
        if (($val === null || $val === '') && attr\ignorable($tmp['_ent']['attr'][$aId], $tmp)) {
            unset($data[$aId], $tmp[$aId]);
        } else {
            $aIds[] = $aId;
        }
    }

    if (!$aIds) {
        app\msg(app\i18n('No changes'));
        return false;
    }

    $tmp = event('prefilter', $tmp);

    foreach ($aIds as $aId) {
        try {
            $tmp = attr\filter($tmp['_ent']['attr'][$aId], $tmp);
        } catch (Throwable $e) {
            $tmp['_error'][$aId] = $e->getMessage();
        }
    }

    $tmp = event('postfilter', $tmp);

    if (!empty($tmp['_error'])) {
        $data['_error'] = $tmp['_error'];
        app\msg(app\i18n('Could not save data'));
        return false;
    }

    foreach ($aIds as $key => $aId) {
        if (array_key_exists($aId, $tmp['_old']) && $tmp[$aId] === $tmp['_old'][$aId]) {
            unset($aIds[$key]);
        }
    }

    if (!$aIds) {
        app\msg(app\i18n('No changes'));
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
        app\msg(app\i18n('Successfully saved data'));
        $data = $tmp;

        return true;
    } catch (Throwable $e) {
        app\msg(app\i18n('Could not save data'));
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
function data(string $eId): array
{
    if (!$ent = app\cfg('ent', $eId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $eId));
    }

    return array_fill_keys(array_keys($ent['attr']), null) + ['_old' => [], '_ent' => $ent];
}

/**
 * Retrieve entity attributes filtered by given action
 *
 * @throws DomainException
 */
function attr(array $ent, string $act): array
{
    if (!array_key_exists($act, $ent['act'])) {
        throw new DomainException(app\i18n('Invalid action %s for entity %s', $act, $ent['id']));
    }

    $aIds = $ent['act'][$act] ?: array_keys($ent['attr']);
    $attrs = [];

    foreach ($aIds as $aId) {
        if (!in_array($act, ['edit', 'form']) || !$ent['attr'][$aId]['auto']) {
            $attrs[$aId] = $ent['attr'][$aId];
        }
    }

    return $attrs;
}

/**
 * Load entity
 */
function load(array $ent, array $data): array
{
    foreach (array_intersect_key($data, $ent['attr']) as $aId => $val) {
        $data[$aId] = attr\cast($ent['attr'][$aId], $val);
    }

    $data += ['_old' => $data, '_ent' => $ent];

    return event('load', $data);
}

/**
 * Dispatches multiple entity events
 */
function event(string $name, array $data): array
{
    return app\event(['ent.' . $name, 'ent.type.' . $name . '.' . $data['_ent']['type'], 'ent.' . $name . '.' . $data['_ent']['id']], $data);
}
