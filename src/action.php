<?php
declare(strict_types = 1);

namespace action;

use account;
use arr;
use app;
use entity;
use request;
use session;

/**
 * Edit Action
 */
function edit(array $entity): void
{
    if (!($id = app\get('id')) || !($old = entity\one($entity['id'], [['id', $id]]))) {
        app\msg('Nothing to edit');
        request\redirect(app\url($entity['id'] . '/admin'));
        return;
    }

    if ($data = request\get('data')) {
        $data += ['id' => $id];

        if (entity\save($entity['id'], $data)) {
            request\redirect(app\url($entity['id'] . '/edit/' . $data['id']));
            return;
        }
    }

    $p = [$old];

    if ($id && in_array('page', [$entity['id'], $entity['parent']])) {
        $v = entity\one('version', [['page_id', $id]], ['select' => APP['version'], 'order' => ['timestamp' => 'desc']]);
        unset($v['_old'], $v['_entity']);
        $p[] = $v;
    }

    $p[] = $data;
    $data = arr\replace(['_error' => null] + entity\item($entity), ...$p);
    app\layout('content', ['vars' => ['data' => $data, 'entity' => $entity, 'title' => $entity['name']]]);
}

/**
 * View Action
 */
function view(array $entity): void
{
    $id = app\get('id');
    $crit = [['id', $id]];

    if (!app\allowed($entity['id'] . '/edit') && in_array('page', [$entity['id'], $entity['parent']])) {
        $crit[] = ['status', 'published'];
    }

    if (!$id || !($data = entity\one($entity['id'], $crit))) {
        app\error();
        return;
    }

    app\layout('content', ['vars' => ['data' => $data, 'entity' => $entity]]);
}

/**
 * Delete Action
 */
function delete(array $entity): void
{
    if ($id = app\get('id')) {
        entity\delete($entity['id'], [['id', $id]]);
    } else {
        app\msg('Nothing to delete');
    }

    request\redirect(app\url($entity['id'] . '/admin'));
}

/**
 * App JavaScript Action
 */
function app_js(): void
{
    header('Content-Type: text/javascript', true);
    die(json_encode(['i18n' => app\cfg('i18n')]));
}

/**
 * Account Password Action
 */
function account_password(array $entity): void
{
    if ($data = request\get('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            app\msg('Password and password confirmation must be identical');
        } else {
            $data = ['id' => account\get('id'), 'password' => $data['password']];
            entity\save('account', $data);
        }
    }

    app\layout('content', ['vars' => ['entity' => $entity, 'title' => app\i18n('Password')]]);
}

/**
 * Account Login Action
 */
function account_login(array $entity): void
{
    if ($data = request\get('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account\login($data['name'], $data['password']))) {
            session\regenerate();
            session\set('account', $data['id']);
            request\redirect();
            return;
        }

        app\msg('Invalid name and password combination');
    }

    app\layout('content', ['vars' => ['data' => ['_entity' => $entity], 'title' => app\i18n('Login')]]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    request\redirect();
}
