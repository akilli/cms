<?php
declare(strict_types = 1);

namespace opt;

use app;
use arr;
use attr;
use ent;

/**
 * Entity options
 */
function ent(array $attr): array
{
    if (($opt = & app\data('opt.ent.' . $attr['ent'])) === null) {
        $opt = array_column(ent\all($attr['ent'], [], ['order' => ['name' => 'asc']]), 'name', 'id');
    }

    return $opt;
}

/**
 * Menu options
 */
function menu(): array
{
    if (($opt = & app\data('opt.menu')) === null) {
        $attr = app\cfg('ent', 'page')['attr']['pos'];
        $opt = [];

        foreach (ent\all('page', [['menu', true]], ['order' => ['pos' => 'asc']]) as $item) {
            $opt[$item['id']] = attr\viewer($attr, $item) . ' ' . $item['name'];
        }
    }

    return $opt;
}

/**
 * Page type options
 */
function pagetype(): array
{
    if (($opt = & app\data('opt.pagetype')) === null) {
        $opt = array_column(arr\crit(app\cfg('ent'), [['parent', 'page']]), 'name', 'id');
        asort($opt);
    }

    return $opt;
}

/**
 * Privilege options
 */
function priv(): array
{
    if (($opt = & app\data('opt.priv')) === null) {
        $opt = [];

        foreach (app\cfg('priv') as $key => $priv) {
            if ($priv['assignable'] && app\allowed($key)) {
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
function status(array $attr, array $data): array
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
