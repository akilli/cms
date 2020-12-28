<?php
declare(strict_types=1);

namespace block\profile;

use app;
use arr;
use entity;
use request;

function render(array $block): string
{
    if (!$account = app\data('account')) {
        return '';
    }

    $pId = 'password';
    $cId = 'password-confirmation';

    // Add password-confirmation to form if password is among the configured attributes
    if (($pKey = array_search($pId, $block['cfg']['attr_id'], true)) !== false) {
        $account['_old'][$cId] = $account['_old'][$pId];
        $account['_entity']['attr'][$cId] = array_replace(
            $account['_entity']['attr'][$pId],
            ['id' => $cId, 'name' => app\i18n('Password Confirmation')]
        );
        array_splice($block['cfg']['attr_id'], ++$pKey, 0, $cId);
    }

    if (!$attrs = arr\extract($account['_entity']['attr'], $block['cfg']['attr_id'])) {
        return '';
    }

    $request = app\data('request');

    if ($data = $request['post']) {
        if (!empty($data[$pId]) && (empty($data[$cId]) || $data[$pId] !== $data[$cId])) {
            $data['_error'][$pId][] = app\i18n('Password and password confirmation must be identical');
            $data['_error'][$cId][] = app\i18n('Password and password confirmation must be identical');
        } else {
            unset($data[$cId]);
            $data = ['id' => $account['id']] + $data;

            if (entity\save('account', $data)) {
                request\redirect($request['url']);
                return '';
            }
        }
    }

    $data = $data ? arr\replace($account, $data) : $account;

    return app\tpl($block['cfg']['tpl'], ['attr' => $attrs, 'data' => $data, 'multipart' => !!arr\filter($attrs, 'uploadable', true)]);
}
