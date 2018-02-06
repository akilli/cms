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
    if (($opt = & app\reg('opt.ent.' . $attr['ent'])) === null) {
        $opt = array_column(ent\all($attr['ent'], [], ['order' => ['name' => 'asc']]), 'name', 'id');
    }

    return $opt;
}

/**
 * Page options
 */
function page(): array
{
    if (($opt = & app\reg('opt.page')) === null) {
        $attr = app\cfg('ent', 'page')['attr']['pos'];
        $opt = [];

        foreach (ent\all('content', [], ['order' => ['pos' => 'asc']]) as $item) {
            $opt[$item['id']] = attr\viewer($attr, $item) . ' ' . $item['name'];
        }
    }

    return $opt;
}

/**
 * Child entity options
 */
function child(array $attr): array
{
    if (($opt = & app\reg('opt.child.' . $attr['ent'])) === null) {
        $opt = array_column(arr\crit(app\cfg('ent'), [['parent', $attr['ent']]]), 'name', 'id');
        asort($opt);
    }

    return $opt;
}

/**
 * Privilege options
 */
function priv(): array
{
    if (($opt = & app\reg('opt.priv')) === null) {
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
