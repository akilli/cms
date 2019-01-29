<?php
declare(strict_types = 1);

namespace opt;

use app;
use arr;
use attr;
use entity;

/**
 * Entity options
 */
function entity(array $data, array $attr): array
{
    if (($opt = & app\registry('opt.entity.' . $attr['ref'])) === null) {
        $opt = array_column(entity\all($attr['ref'], [], ['select' => ['id', 'name'], 'order' => ['name' => 'asc']]), 'name', 'id');
    }

    return $opt;
}

/**
 * Page entity options
 */
function page(): array
{
    if (($opt = & app\registry('opt.page')) === null) {
        $opt = [];

        foreach (entity\all('page_content', [], ['select' => ['id', 'name', 'menu_name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
            $opt[$item['id']] = attr\viewer($item, $item['_entity']['attr']['pos']) . ' ' . ($item['menu_name'] ?: $item['name']);
        }
    }

    return $opt;
}

/**
 * Parent entity options
 */
function parent(array $data): array
{
    if ($data['_entity']['parent_id']) {
        return [$data['_entity']['id'] => $data['_entity']['name']];
    }

    if (($opt = & app\registry('opt.parent.' . $data['_entity']['id'])) === null) {
        $opt = array_column(arr\crit(app\cfg('entity'), [['parent_id', $data['_entity']['id']]]), 'name', 'id');
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
