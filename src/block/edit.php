<?php
declare(strict_types=1);

namespace block\edit;

use app;
use arr;
use entity;
use request;

function render(array $block): string
{
    $app = app\data('app');

    if (!($entity = $app['entity']) || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    if ($data = app\data('request', 'post')) {
        if ($app['id']) {
            $data = ['id' => $app['id']] + $data;
        }

        if (entity\save($entity['id'], $data)) {
            request\redirect(app\action($entity['id'], 'edit', $data['id']));
            return '';
        }
    }

    $args = $app['id'] ? [entity\one($entity['id'], crit: [['id', $app['id']]]), $data] : [$data];
    $data = arr\replace(entity\item($entity['id']), ...$args);

    return app\tpl($block['cfg']['tpl'], ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]);
}
