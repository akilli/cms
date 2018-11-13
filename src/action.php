<?php
declare(strict_types = 1);

namespace action;

use account;
use app;
use entity;
use request;
use session;

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
function account_login(): void
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
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    request\redirect();
}
