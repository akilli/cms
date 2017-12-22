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
        $order = $attr['opt'] === 'page' ? ['pos' => 'asc'] : ['name' => 'asc'];
        $opt = [];

        foreach (ent\all($attr['opt'], [], ['order' => $order]) as $item) {
            $pre = $attr['opt'] === 'page' ? str_repeat('&nbsp;', (max($item['level'], 1) - 1) * 4) : '';
            $opt[$item['id']] = $pre . $item['name'];
        }
    }

    return $opt;
}

/**
 * Entity config options
 */
function ent_cfg(): array
{
    $opt = array_column(app\cfg('ent'), 'name', 'id');
    asort($opt);

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
