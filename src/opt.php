<?php
declare(strict_types = 1);

namespace opt;

use app;
use ent;

/**
 * Entity options
 */
function ent(array $data, array $attr): array
{
    if (($opt = & app\data('opt.ent.' . $attr['opt'])) === null) {
        $opt = [];

        foreach (ent\all($attr['opt']) as $item) {
            if (!empty($data['_ent']['attr']['level'])) {
                $opt[$item['id']] = str_repeat('&nbsp;', (max($item['level'], 1) - 1) * 4) . $item['name'];
            } else {
                $opt[$item['id']] = $item['name'];
            }
        }
    }

    return $opt;
}

/**
 * Privilege options
 */
function priv(): array
{
    $opt = [];

    foreach (app\cfg('priv') as $key => $priv) {
        if ($priv['assignable'] && app\allowed($key)) {
            $opt[$key] = $priv['name'];
        }
    }

    asort($opt);

    return $opt;
}

/**
 * Status options
 */
function status(array $data, array $attr): array
{
    $opt = ['draft' => 'Draft', 'pending' => 'Pending'];

    if (app\allowed($data['_ent']['id'] . '-publish')) {
        $opt['published'] = 'Published';
        $old = $data['_old'][$attr['id']] ?? null;

        if (in_array($old, ['published', 'archived'])) {
            $opt['archived'] = 'Archived';
        }
    }

    return array_map('app\i18n', $opt);
}
