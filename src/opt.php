<?php
declare(strict_types = 1);

namespace opt;

use app;
use arr;

/**
 * Child entity options
 */
function child(array $data): array
{
    if ($data['_entity']['parent']) {
        return [$data['_entity']['id'] => $data['_entity']['name']];
    }

    if (($opt = & app\registry('opt.child.' . $data['_entity']['id'])) === null) {
        $opt = array_column(arr\crit(app\cfg('entity'), [['parent', $data['_entity']['id']]]), 'name', 'id');
        asort($opt);
    }

    return $opt;
}

/**
 * Privilege options
 */
function priv(): array
{
    if (($opt = & app\registry('opt.priv')) === null) {
        $opt = [];

        foreach (app\cfg('priv') as $key => $priv) {
            if ($priv['active'] && !$priv['priv'] && !$priv['auto'] && app\allowed($key)) {
                $opt[$key] = $priv['name'];
            }
        }

        asort($opt);
    }

    return $opt;
}

/**
 * Status options
 */
function status(array $data, array $attr): array
{
    $opt = ['draft' => 'Draft', 'pending' => 'Pending'];

    if (app\allowed($data['_entity']['id'] . '-publish')) {
        $opt['published'] = 'Published';
        $old = $data['_old'][$attr['id']] ?? null;

        if (in_array($old, ['published', 'archived'])) {
            $opt['archived'] = 'Archived';
        }
    }

    return array_map('app\i18n', $opt);
}
