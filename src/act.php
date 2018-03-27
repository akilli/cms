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
 * Edit Action
 */
function edit(array $ent): void
{
    if (!($id = app\get('id')) || !($old = ent\one($ent['id'], [['id', $id]]))) {
        app\msg('Nothing to edit');
        app\redirect(app\url($ent['id'] . '/admin'));
        return;
    }

    if ($data = req\get('data')) {
        $data += ['id' => $id];

        if (ent\save($ent['id'], $data)) {
            app\redirect(app\url($ent['id'] . '/edit/' . $data['id']));
            return;
        }
    }

    $p = [$old];

    if ($id && in_array('page', [$ent['id'], $ent['parent']])) {
        $v = ent\one('version', [['page', $id]], ['select' => APP['version'], 'order' => ['date' => 'desc']]);
        unset($v['_old'], $v['_ent']);
        $p[] = $v;
    }

    $p[] = $data;
    $data = arr\replace(ent\item($ent), ...$p);
    app\layout('content', ['vars' => ['data' => $data, 'ent' => $ent, 'title' => $ent['name']]]);
}

/**
 * View Action
 */
function view(array $ent): void
{
    $id = app\get('id');
    $crit = [['id', $id]];

    if (!app\allowed($ent['id'] . '/edit') && in_array('page', [$ent['id'], $ent['parent']])) {
        $crit[] = ['status', 'published'];
    }

    if (!$id || !($data = ent\one($ent['id'], $crit))) {
        app_error();
        return;
    }

    app\layout('content', ['vars' => ['data' => $data, 'ent' => $ent]]);
    app\layout('head', ['vars' => ['desc' => $data['meta'] ?? null, 'title' => $data['name']]]);
}

/**
 * Delete Action
 */
function delete(array $ent): void
{
    if ($id = app\get('id')) {
        ent\delete($ent['id'], [['id', $id]]);
    } else {
        app\msg('Nothing to delete');
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
    if ($data = req\get('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            app\msg('Password and password confirmation must be identical');
        } else {
            $data = ['id' => account\get('id'), 'password' => $data['password']];
            ent\save('account', $data);
        }
    }

    app\layout('content', ['vars' => ['ent' => $ent, 'title' => app\i18n('Password')]]);
}

/**
 * Account Login Action
 */
function account_login(array $ent): void
{
    if ($data = req\get('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account\login($data['name'], $data['password']))) {
            session\regenerate();
            session\set('account', $data['id']);
            app\redirect();
            return;
        }

        app\msg('Invalid name and password combination');
    }

    app\layout('content', ['vars' => ['data' => ['_ent' => $ent], 'title' => app\i18n('Login')]]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    app\redirect();
}
