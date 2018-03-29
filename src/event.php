<?php
declare(strict_types = 1);

namespace event;

use app;
use arr;
use ent;
use file;
use req;
use smtp;
use DomainException;

/**
 * Block config
 *
 * @throws DomainException
 */
function cfg_block(array $data): array
{
    foreach ($data as $val) {
        if (empty($val['call']) || !is_callable($val['call'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }
    }

    return $data;
}

/**
 * Entity config
 *
 * @throws DomainException
 */
function cfg_ent(array $data): array
{
    $cfg = app\cfg('attr');

    foreach ($data as $eId => $ent) {
        $ent = arr\replace(APP['ent'], $ent, ['id' => $eId]);
        $ent['name'] = app\i18n((string) $ent['name']);
        $p = $ent['parent'] && !empty($data[$ent['parent']]) ? $data[$ent['parent']] : null;
        $a = ['id' => null, 'name' => null];

        if (!$ent['name'] || !$ent['type'] || !$ent['parent'] && array_intersect_key($a, $ent['attr']) !== $a || $ent['parent'] && (!$p || $p['parent'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($ent['parent']) {
            $ent['attr'] = array_replace_recursive($p['attr'], $ent['attr']);
        }

        foreach ($ent['attr'] as $aId => $attr) {
            if (empty($attr['name']) || empty($attr['type']) || empty($cfg[$attr['type']]) || $attr['type'] === 'ent' && empty($attr['ent'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $attr = arr\replace(APP['attr'], $cfg[$attr['type']], $attr, ['id' => $aId, 'name' => app\i18n($attr['name'])]);

            if (!in_array($attr['backend'], APP['backend'])) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $ent['attr'][$aId] = $attr;
        }

        $data[$eId] = $ent;
    }

    return $data;
}

/**
 * I18n config
 */
function cfg_i18n(array $data): array
{
    return $data + app\load('i18n/' . app\get('lang'));
}

/**
 * Option config
 */
function cfg_opt(array $data): array
{
    foreach ($data as $key => $opt) {
        $data[$key] = array_map('app\i18n', $opt);
    }

    return $data;
}

/**
 * Privilege config
 */
function cfg_priv(array $data): array
{
    foreach ($data as $id => $item) {
        $item = arr\replace(APP['priv'], $item);
        $item['name'] = $item['name'] ? app\i18n($item['name']) : '';
        $item['assignable'] = !$item['priv'] && $item['active'] && $item['assignable'];
        $data[$id] = $item;
    }

    foreach (app\cfg('ent') as $ent) {
        if (array_intersect(['create', 'edit'], $ent['act']) && in_array('page', [$ent['id'], $ent['parent']])) {
            $id = $ent['id'] . '-publish';
            $data[$id]['name'] = $ent['name'] . ' ' . app\i18n(ucwords('Publish'));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }

        foreach ($ent['act'] as $act) {
            $id = $ent['id'] . '/' . $act;
            $data[$id]['name'] = $ent['name'] . ' ' . app\i18n(ucwords($act));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }
    }

    return $data;
}

/**
 * Toolbar config
 *
 * @throws DomainException
 */
function cfg_toolbar(array $data): array
{
    foreach ($data as $id => $item) {
        if (empty($item['name']) || !empty($item['parent']) && empty($data[$item['parent']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $item = arr\replace(APP['toolbar'], $item);
        $item['name'] = app\i18n($item['name']);
        $item['level'] = 1;
        $item['sort'] = str_pad((string) $item['sort'], 5, '0', STR_PAD_LEFT) . '-' . $id;

        if ($item['parent']) {
            $item['level'] = $data[$item['parent']]['level'] + 1;
            $item['sort'] = $data[$item['parent']]['sort'] . '/' . $item['sort'];
        }

        $data[$id] = $item;
    }

    return arr\order($data, ['sort' => 'asc']);
}

/**
 * Layout
 *
 * @throws DomainException
 */
function layout(array $data): array
{
    $cfg = app\cfg('layout');
    $type = app\cfg('block');
    $keys = [APP['all'], app\get('area')];

    if (app\get('error')) {
        $keys[] = APP['error'];
    } else {
        $eId = app\get('ent');
        $act = app\get('act');
        $keys[] = $act;

        if ($parent = app\get('parent')) {
            $keys[] = $parent . '/' . $act;
        }

        $keys[] = $eId . '/' . $act;

        if ($layout = app\get('layout')) {
            $keys[] = 'page-' . $layout;
        }

        if ($id = app\get('id')) {
            $keys[] = $eId . '/' . $act . '/' . $id;
        }
    }

    foreach ($keys as $key) {
        if (!empty($cfg[$key])) {
            $data = array_replace_recursive($data, $cfg[$key]);
        }
    }

    foreach ($data as $id => $§) {
        if (empty($§['type']) || empty($type[$§['type']])) {
            throw new DomainException(app\i18n('Invalid block %s', $id));
        }

        $data[$id] = arr\replace(APP['block'], $type[$§['type']], $§, ['id' => $id]);
    }

    return $data;
}

/**
 * Entity postfilter
 */
function ent_postfilter(array $data): array
{
    $attrs = $data['_ent']['attr'];

    foreach (array_intersect_key($data, $data['_ent']['attr']) as $aId => $val) {
        if ($attrs[$aId]['type'] === 'password' && $val && !($data[$aId] = password_hash($val, PASSWORD_DEFAULT))) {
            $data['_error'][$aId] = app\i18n('Invalid password');
        }
    }

    return $data;
}

/**
 * File entity prefilter
 */
function ent_prefilter_file(array $data): array
{
    if (!empty($data['name'])) {
        $data['type'] = pathinfo($data['name'], PATHINFO_EXTENSION);
    }

    if (!empty($data['type']) && !empty($data['_old']['type']) && $data['type'] !== $data['_old']['type']) {
        $data['_error']['name'] = app\i18n('Cannot change filetype anymore');
    }

    return $data;
}

/**
 * Entity postsave
 */
function ent_postsave(array $data): array
{
    if (!$data['_ent']['mail'] || app\get('area') !== APP['area.public']) {
        return $data;
    }

    $cfg = app\cfg('mail');
    $file = req\get('file');
    $attrs = $data['_ent']['attr'];
    $text = '';
    $attach = [];

    foreach ($data as $aId => $val) {
        if ($attrs[$aId]['type'] !== 'file') {
            $text .= $attrs[$aId]['name'] . ':' . APP['crlf'] . $val . APP['crlf'] .APP['crlf'];
        } elseif (!empty($file[$aId])) {
            $attach[] = ['name' => $file[$aId]['name'], 'path' => $file[$aId]['tmp_name'], 'type' => $file[$aId]['type']];
        }
    }

    smtp\mail($cfg['email'], $cfg['email'], null, $cfg['subject'], $text, $attach);

    return $data;
}

/**
 * File entity postsave
 *
 * @throws DomainException
 */
function ent_postsave_file(array $data): array
{
    $item = req\get('file')['name'] ?? null;

    if ($item && !file\upload($item['tmp_name'], app\path('file', $data['id'] . '.' . $data['type']))) {
        throw new DomainException(app\i18n('File upload failed for %s', $item['name']));
    }

    return $data;
}

/**
 * File entity postdelete
 *
 * @throws DomainException
 */
function ent_postdelete_file(array $data): array
{
    if (!file\delete(app\path('file', $data['id'] . '.' . $data['type']))) {
        throw new DomainException(app\i18n('Could not delete %s', $data['name']));
    }

    return $data;
}

/**
 * Page entity postfilter
 */
function ent_postfilter_page(array $data): array
{
    if (empty($data['parent'])) {
        return $data;
    }

    $parent = ent\one('page', [['id', $data['parent']]]);

    if (!empty($data['_old']['id']) && in_array($data['_old']['id'], $parent['path'])) {
        $data['_error']['parent'] = app\i18n('Cannot assign the page itself or a child page as parent');
    }

    if ($parent['status'] === 'archived' && (!$data['_old'] || $data['parent'] !== $data['_old']['parent'])) {
        $data['_error']['parent'] = app\i18n('Cannot assign archived page as parent');
    } elseif (in_array($parent['status'], ['draft', 'pending']) && !empty($data['status']) && $data['status'] !== 'draft') {
        $data['_error']['status'] = app\i18n('Status must be draft, because parent was not published yet');
    }

    return $data;
}
