<?php
declare(strict_types = 1);

namespace ent;

use function app\i18n;
use function session\msg;
use function sql\trans;
use attr;
use app;
use Exception;
use RuntimeException;

const ENT = [
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
    $ent = app\cfg('ent', $eId);
    $opts = ['mode' => 'size'] + OPTS;

    try {
        return ($ent['model'] . '\load')($ent, $crit, $opts)[0];
    } catch (Exception $e) {
        app\log((string) $e);
        msg(i18n('Could not load data'));
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
        if ($data = ($ent['model'] . '\load')($ent, $crit, $opts)) {
            $data = load($ent, $data);
        }
    } catch (Exception $e) {
        app\log((string) $e);
        msg(i18n('Could not load data'));
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
        $data = ($ent['model'] . '\load')($ent, $crit, $opts);

        foreach ($data as $id => $item) {
            $data[$id] = load($ent, $item);
        }

        return array_column($data, null, $opts['index']);
    } catch (Exception $e) {
        app\log((string) $e);
        msg(i18n('Could not load data'));
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
            $tmp = attr\validator($tmp['_ent']['attr'][$aId], $tmp);
        } catch (Exception $e) {
            $data['_error'][$aId] = $e->getMessage();
        }
    }

    if (!empty($data['_error'])) {
        msg(i18n('Could not save %s', $name));
        return false;
    }

    $trans = trans(
        function () use (& $tmp, $aIds): void {
            $tmp = app\event('ent.presave', $tmp);
            $tmp = app\event('model.presave.' . $tmp['_ent']['model'], $tmp);
            $tmp = app\event('ent.presave.' . $tmp['_ent']['id'], $tmp);
            $tmp = ($tmp['_ent']['model'] . '\save')($tmp);
            app\event('ent.postsave', $tmp);
            app\event('model.postsave.' . $tmp['_ent']['model'], $tmp);
            app\event('ent.postsave.' . $tmp['_ent']['id'], $tmp);
        }
    );

    if ($trans) {
        msg(i18n('Successfully saved %s', $name));
        $data = $tmp;
    } else {
        msg(i18n('Could not save %s', $name));
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
            msg(i18n('System items must not be deleted! Therefore skipped ID %s', (string) $id));
            continue;
        }

        $trans = trans(
            function () use ($data): void {
                $data = app\event('ent.predelete', $data);
                $data = app\event('model.predelete.' . $data['_ent']['model'], $data);
                $data = app\event('ent.predelete.' . $data['_ent']['id'], $data);
                ($data['_ent']['model'] . '\delete')($data);
                app\event('ent.postdelete', $data);
                app\event('model.postdelete.' . $data['_ent']['model'], $data);
                app\event('ent.postdelete.' . $data['_ent']['id'], $data);
            }
        );

        if ($trans) {
            $success[] = $data['name'];
        } else {
            $error[] = $data['name'];
        }
    }

    if ($success) {
        msg(i18n('Successfully deleted %s', implode(', ', $success)));
    }

    if ($error) {
        msg(i18n('Could not delete %s', implode(', ', $error)));
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
        throw new RuntimeException(i18n('Invalid entity %s', $eId));
    }

    $item = array_fill_keys(array_keys(attr($ent, 'edit')), null);

    return $bare ? $item : $item + ['_old' => null, '_ent' => $ent];
}

/**
 * Retrieve entity attributes filtered by given action
 */
function attr(array $ent, string $act): array
{
    foreach ($ent['attr'] as $aId => $attr) {
        if (!in_array($act, $attr['act'])) {
            unset($ent['attr'][$aId]);
        }
    }

    return $ent['attr'];
}

/**
 * Internal entity loader
 */
function load(array $ent, array $data): array
{
    foreach ($data as $aId => $val) {
        if (!empty($ent['attr'][$aId])) {
            $data[$aId] = attr\loader($ent['attr'][$aId], $data);
        }
    }

    $data['_old'] = $data;
    $data['_ent'] = $ent;
    $data = app\event('ent.load', $data);
    $data = app\event('model.load.' . $ent['model'], $data);
    $data = app\event('ent.load.' . $ent['id'], $data);

    return $data;
}
