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
    $id = app\data('id');
    $data = req\data('post');
    $data += $data && $id ? ['id' => $id] : [];
    $act = app\data('act');

    if ($data && ent\save($ent['id'], $data) && $act === 'edit') {
        $id = ($id ?: $data['id']);
        app\redirect(app\url($ent['id'] . '/edit/' . $id));
        return;
    }

    if ($id) {
        $base = ent\one($ent['id'], [['id', $id]]);

        if ($act === 'edit' && in_array('page', [$ent['id'], $ent['parent']])) {
            $v = ent\one('version', [['page', $id]], ['order' => ['date' => 'desc']]);
            $base = arr\replace($base, arr\replace(APP['version'], $v));
        }
    } else {
        $base = ent\data($ent);
    }

    $data = array_replace($base, $data);
    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, $act), 'title' => $ent['name']]);
 }

/**
 * Edit Action
 */
function edit(array $ent): void
{
    form($ent);
}

/**
 * View Action
 */
function view(array $ent): void
{
    $crit = [['id', app\data('id')]];

    if (!app\allowed($ent['id'] . '/edit') && in_array('page', [$ent['id'], $ent['parent']])) {
        $crit[] = ['status', 'published'];
    }

    if (!$data = ent\one($ent['id'], $crit)) {
        app_error();
        return;
    }

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, 'view')]);
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

    $attr = array_intersect_key($ent['attr'], ['password' => null, 'confirmation' => null]);
    app\layout('content', ['data' => ['_ent' => $ent], 'attr' => $attr, 'title' => app\i18n('Password')]);
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

    $attr = array_intersect_key($ent['attr'], ['name' => null, 'password' => null]);
    $attr['name'] = array_replace($attr['name'], ['unique' => false, 'minlength' => 0, 'maxlength' => 0]);
    $attr['password'] = array_replace($attr['password'], ['minlength' => 0, 'maxlength' => 0]);
    app\layout('content', ['data' => ['_ent' => $ent], 'attr' => $attr, 'title' => app\i18n('Login')]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    app\redirect();
}
