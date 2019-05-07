<?php
declare(strict_types = 1);

namespace action;

use app;
use cfg;
use entity;
use layout;
use request;
use session;

/**
 * Delete
 */
function delete(array $entity): void
{
    entity\delete($entity['id'], [['id', app\data('id')]]);
    request\redirect(app\url($entity['id'] . '/admin'));
}

/**
 * App Config
 */
function app_cfg(): void
{
    header('Content-Type: application/json', true);
    die(json_encode(['i18n' => cfg\data('i18n')]));
}

/**
 * Account Logout
 */
function account_logout(): void
{
    session\regenerate();
    request\redirect(app\url('account/login'));
}

/**
 * Account Logout
 */
function block_api(): void
{
    die(app\data('id') ? layout\db_render(app\data('id')) : '');
}
