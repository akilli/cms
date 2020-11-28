<?php
declare(strict_types=1);

namespace event\file;

use app;
use entity;
use file;
use DomainException;

function prevalidate(array $data): array
{
    if ($data['_entity']['id'] === 'file_iframe') {
        $data['mime'] = 'text/html';

        if ($data['_old'] && !empty($data['url']) && $data['url'] !== $data['_old']['url']) {
            $data['_error']['url'][] = app\i18n('URL must not change');
        }
    } elseif ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url'])) {
        if (!$item = app\data('request', 'file')['url'] ?? null) {
            $data['_error']['url'][] = app\i18n('No upload file');
        } elseif ($data['_old'] && $item['type'] !== $data['_old']['mime']) {
            $data['_error']['url'][] = app\i18n('MIME-Type must not change');
        } elseif ($data['_old']) {
            $data['url'] = $data['_old']['url'];
        } else {
            $data['url'] = app\file($item['name']);
            $data['mime'] = $item['type'];

            if (entity\size('file', [[['url', $data['url']], ['thumb', $data['url']]]])) {
                $data['_error']['url'][] = app\i18n('Please change filename to generate an unique URL');
            }
        }
    }

    if (!empty($data['thumb']) && ($item = app\data('request', 'file')['thumb'] ?? null)) {
        $data['thumb'] = app\file($item['name']);
        $crit = array_merge(
            [[['url', $data['thumb']], ['thumb', $data['thumb']]]],
            $data['_old'] ? [['id', $data['_old']['id'], APP['op']['!=']]] : []
        );

        if (entity\size('file', $crit)) {
            $data['_error']['thumb'][] = app\i18n('Please change filename to generate an unique URL');
        }
    }

    return $data;
}

/**
 * @throws DomainException
 */
function postsave(array $data): array
{
    $upload = function (string $attrId) use ($data): ?string {
        $item = app\data('request', 'file')[$attrId] ?? null;
        return $item && !file\upload($item['tmp_name'], app\filepath($data[$attrId])) ? $item['name'] : null;
    };

    if ($data['_entity']['attr']['url']['uploadable'] && !empty($data['url']) && ($name = $upload('url'))
        || !empty($data['thumb']) && ($name = $upload('thumb'))
    ) {
        throw new DomainException(app\i18n('Could not upload %s', $name));
    }

    if (array_key_exists('thumb', $data)
        && !$data['thumb']
        && $data['_old']['thumb']
        && !file\delete(app\filepath($data['_old']['thumb']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}

/**
 * @throws DomainException
 */
function postdelete(array $data): array
{
    if ($data['_entity']['attr']['url']['uploadable'] && !file\delete(app\filepath($data['_old']['url']))
        || $data['_old']['thumb'] && !file\delete(app\filepath($data['_old']['thumb']))
    ) {
        throw new DomainException(app\i18n('Could not delete file'));
    }

    return $data;
}
