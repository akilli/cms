<?php
declare(strict_types = 1);

namespace action;

use app;
use entity;
use layout;
use request;
use session;

/**
 * Delete
 */
function delete(): void
{
    $app = app\data('app');
    entity\delete($app['entity_id'], [['id', $app['id']]]);
    request\redirect(app\url($app['entity_id'] . '/admin'));
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
 * API Config
 */
function api_cfg(): void
{
    header('Content-Type: application/json', true);
    die(json_encode(['i18n' => app\cfg('i18n')]));
}

/**
 * Block API
 */
function block_api(): void
{
    die(($id = app\data('app', 'id')) ? layout\db_render($id) : '');
}
