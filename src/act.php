<?php
declare(strict_types = 1);

namespace act;

use account;
use arr;
use app;
use ent;
use req;
use session;

/**
 * Form Action
 */
function form(array $ent): void
{
    if (($data = req\data('post')) && ent\save($ent['id'], $data)) {
        $data = [];
    }

    $data = array_replace(ent\data($ent), $data);
    app\layout('content', ['data' => $data, 'title' => $ent['name']]);
}

/**
 * Create Action
 */
function create(array $ent): void
{
    if (($data = req\data('post')) && ent\save($ent['id'], $data)) {
        app\redirect(app\url($ent['id'] . '/edit/' . $data['id']));
        return;
    }

    $data = array_replace(ent\data($ent), $data);
    app\layout('content', ['data' => $data, 'title' => $ent['name']]);
}

/**
 * Edit Action
 */
function edit(array $ent): void
{
    if (!($id = app\data('id')) || !($old = ent\one($ent['id'], [['id', $id]]))) {
        app\msg(app\i18n('Nothing to edit'));
        app\redirect(app\url($ent['id'] . '/admin'));
        return;
    }

    if ($data = req\data('post')) {
        $data += ['id' => $id];

        if (ent\save($ent['id'], $data)) {
            app\redirect(app\url($ent['id'] . '/edit/' . $data['id']));
            return;
        }
    }

    $p = [$old];

    if ($id && in_array('page', [$ent['id'], $ent['parent']])) {
        $p[] = arr\replace(APP['version'], ent\one('version', [['page', $id]], ['order' => ['date' => 'desc']]));
    }

    $p[] = $data;
    $data = arr\replace(ent\data($ent), ...$p);
    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, $ent['act']['edit']), 'title' => $ent['name']]);
}

/**
 * View Action
 */
function view(array $ent): void
{
    $id = app\data('id');
    $crit = [['id', $id]];

    if (!app\allowed($ent['id'] . '/edit') && in_array('page', [$ent['id'], $ent['parent']])) {
        $crit[] = ['status', 'published'];
    }

    if (!$id || !($data = ent\one($ent['id'], $crit))) {
        app_error();
        return;
    }

    app\layout('meta', ['desc' => $data['meta'] ?? null, 'title' => $data['name']]);
}

/**
 * Delete Action
 */
function delete(array $ent): void
{
    if ($id = app\data('id')) {
        ent\delete($ent['id'], [['id', $id]]);
    } else {
        app\msg(app\i18n('Nothing to delete'));
    }

    app\redirect(app\url($ent['id'] . '/admin'));
}

/**
 * App Error Action
 */
function app_error(): void
{
    http_response_code(404);
}

/**
 * App JavaScript Action
 */
function app_js(): void
{
    header('Content-Type: text/javascript', true);
}

/**
 * Account Password Action
 */
function account_password(array $ent): void
{
    if ($data = req\data('post')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            app\msg(app\i18n('Password and password confirmation must be identical'));
        } else {
            $data = ['id' => account\data('id'), 'password' => $data['password']];
            ent\save('account', $data);
        }
    }

    app\layout('content', ['data' => ['_ent' => $ent], 'attr' => ent\attr($ent, $ent['act']['password']), 'title' => app\i18n('Password')]);
}

/**
 * Account Login Action
 */
function account_login(array $ent): void
{
    if ($data = req\data('post')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account\login($data['name'], $data['password']))) {
            session\regenerate();
            session\set('account', $data['id']);
            app\redirect();
            return;
        }

        app\msg(app\i18n('Invalid name and password combination'));
    }

    $attrs = ent\attr($ent, $ent['act']['login']);
    $attrs['name'] = array_replace($attrs['name'], ['unique' => false, 'minlength' => 0, 'maxlength' => 0]);
    $attrs['password'] = array_replace($attrs['password'], ['minlength' => 0, 'maxlength' => 0]);
    app\layout('content', ['data' => ['_ent' => $ent], 'attr' => $attrs, 'title' => app\i18n('Login')]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    app\redirect();
}
