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
    entity\delete(app\data('entity_id'), [['id', app\data('id')]]);
    request\redirect(app\url(app\data('entity_id') . '/admin'));
}

/**
 * App Config
 */
function app_cfg(): void
{
    header('Content-Type: application/json', true);
    die(json_encode(['i18n' => app\cfg('i18n')]));
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
