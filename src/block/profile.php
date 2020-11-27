<?php
declare(strict_types=1);

namespace block;

use app;
use arr;
use entity;
use request;

/**
 * Profile Form
 */
function profile(array $block): string
{
    $account = app\data('account');

    if (!$account || !($attrs = arr\extract($account['_entity']['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    $request = app\data('request');

    if ($data = $request['post']) {
        if (!empty($data['password']) && (empty($data['confirmation']) || $data['password'] !== $data['confirmation'])) {
            $data['_error']['password'][] = app\i18n('Password and password confirmation must be identical');
            $data['_error']['confirmation'][] = app\i18n('Password and password confirmation must be identical');
        } else {
            unset($data['confirmation']);
            $data = ['id' => $account['id']] + $data;

            if (entity\save('account', $data)) {
                request\redirect($request['url']);
                return '';
            }
        }
    }

    $data = $data ? arr\replace($account, $data) : $account;

    return app\tpl(
        $block['tpl'],
        ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]
    );
}
