<?php
declare(strict_types=1);

namespace event\response;

use app;
use entity;
use layout;
use session;

function html(array $data): array
{
    if (!$data['body'] && !$data['redirect']) {
        $data['body'] = layout\render_id('html');
    }

    return $data;
}

function html_delete(array $data): array
{
    $app = app\data('app');
    entity\delete($app['entity_id'], [['id', $app['id']]]);
    $data['redirect'] = app\action($app['entity_id'], 'index');
    $data['_stop'] = true;

    return $data;
}

function html_account_logout(array $data): array
{
    session\regenerate();
    $data['redirect'] = app\url();
    $data['_stop'] = true;

    return $data;
}

function html_block_api(array $data): array
{
    $data['body'] = ($id = app\data('app', 'id')) ? layout\render_entity($id) : '';
    $data['_stop'] = true;

    return $data;
}

function json(array $data): array
{
    header('content-type: application/json');
    $app = app\data('app');

    if ($app['invalid']) {
        return $data;
    }

    $filter = function (array $item): array {
        foreach ($item['_entity']['attr'] as $attrId => $attr) {
            if ($attr['type'] === 'password') {
                unset($item[$attrId]);
            }
        }

        return entity\uninit($item);
    };

    if ($app['id']) {
        $result = $filter(entity\one($app['entity_id'], crit: [['id', $app['id']]]));
    } else {
        $result = array_map($filter, entity\all($app['entity_id']));
    }

    $data['body'] = json_encode($result);

    return $data;
}
