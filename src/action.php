<?php
declare(strict_types = 1);

namespace action;

use account;
use app;
use entity;
use request;
use session;

/**
 * Delete Action
 */
function delete(array $entity): void
{
    entity\delete($entity['id'], [['id', app\get('id')]]);
    request\redirect(app\url($entity['id'] . '/admin'));
}

/**
 * App Config Action
 */
function app_cfg(): void
{
    header('Content-Type: application/json', true);
    die(json_encode(['i18n' => app\cfg('i18n')]));
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
