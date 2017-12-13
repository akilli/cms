<?php
declare(strict_types = 1);

namespace opt;

use app;
use ent;

/**
 * Entity options
 */
function ent(string $eId): array
{
    if (($opt = & app\data('opt.ent.' . $eId)) === null) {
        $select = !empty(app\cfg('ent', $eId)['attr']['level']) ? ['id', 'name', 'level'] : ['id', 'name'];
        $opt = ent\all($eId, [], ['select' => $select]);
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
            $opt[$key] = $priv;
        }
    }

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
