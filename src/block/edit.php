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
    $old = null;
    $p = [];

    if (!($entity = $app['entity']) || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    if (($id = $app['id']) && !($old = entity\one($entity['id'], crit: [['id', $id]]))) {
        app\msg('Nothing to edit');
        request\redirect(app\action($entity['id'], 'index'));
        return '';
    }

    if ($data = app\data('request', 'post')) {
        if ($id) {
            $data = ['id' => $id] + $data;
        }

        if (entity\save($entity['id'], $data)) {
            request\redirect(app\action($entity['id'], 'edit', (string)$data['id']));
            return '';
        }
    }

    if ($id) {
        $p = [$old];
    }

    $p[] = $data;
    $data = arr\replace(entity\item($entity['id']), ...$p);

    return app\tpl(
        $block['tpl'],
        ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]
    );
}
