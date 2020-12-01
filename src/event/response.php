<?php
declare(strict_types=1);

namespace event\response;

use app;
use entity;
use layout;
use session;

function all(array $data): array
{
    if (!$data['body'] && !$data['redirect']) {
        $data['body'] = layout\render_id('html');
    }

    return $data;
}

function delete(array $data): array
{
    $app = app\data('app');
    entity\delete($app['entity_id'], [['id', $app['id']]]);
    $data['redirect'] = app\url($app['entity_id'] . '/admin');
    $data['_stop'] = true;

    return $data;
}

function account_logout(array $data): array
{
    session\regenerate();
    $data['redirect'] = app\url();
    $data['_stop'] = true;

    return $data;
}

function block_api(array $data): array
{
    $data['body'] = ($id = app\data('app', 'id')) ? layout\render_entity($id) : '';
    $data['_stop'] = true;

    return $data;
}
