<?php
declare(strict_types=1);

namespace event;

use app;
use entity;
use layout;
use session;

/**
 * Response
 */
function response(array $data): array
{
    if (!$data['body'] && !$data['redirect']) {
        $data['body'] = layout\block('html');
    }

    return $data;
}

/**
 * Delete response
 */
function response_delete(array $data): array
{
    $app = app\data('app');
    entity\delete($app['entity_id'], [['id', $app['id']]]);
    $data['redirect'] = app\url($app['entity_id'] . '/admin');
    $data['_stop'] = true;

    return $data;
}

/**
 * Account logout response
 */
function response_account_logout(array $data): array
{
    session\regenerate();
    $data['redirect'] = app\url();
    $data['_stop'] = true;

    return $data;
}

/**
 * Block API response
 */
function response_block_api(array $data): array
{
    $data['body'] = ($id = app\data('app', 'id')) ? layout\db_block($id) : '';
    $data['_stop'] = true;

    return $data;
}
