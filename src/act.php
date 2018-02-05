<?php
declare(strict_types = 1);

namespace act;

use account;
use arr;
use app;
use ent;
use http;
use session;

/**
 * Form Action
 */
function form(array $ent): void
{
    $id = app\data('id');
    $data = http\req('data');
    $data += $data && $id ? ['id' => $id] : [];
    $act = app\data('act');

    if ($data && ent\save($ent['id'], $data) && $act === 'edit') {
        $id = ($id ?: $data['id']);
        http\redirect(app\url($ent['id'] . '/edit/' . $id));
    }

    if ($id) {
        $base = ent\one($ent['id'], [['id', $id]]);

        if ($act === 'edit' && in_array('page', [$ent['id'], $ent['parent']])) {
            $v = ent\one('version', [['page', $id]], ['order' => ['date' => 'desc']]);
            $base = arr\replace($base, ['name' => $v['name'], 'teaser' => $v['teaser'], 'body' => $v['body'], 'status' => $v['status'], 'date' => $v['date']]);
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
    app\layout('meta', ['title' => $data['name']]);
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

    http\redirect(app\url($ent['id'] . '/admin'));
}

/**
 * Asset Action
 */
function asset(array $ent): void
{
    if (!($id = app\data('id')) || !is_file(app\path('asset', $ent['id'] . '/' . $id))) {
        http_response_code(404);
        exit;
    }

    http_response_code(200);
    header('X-Accel-Redirect: ' . APP['url.asset'] . $ent['id'] . '/' . $id);
    header('X-Accel-Buffering: no');
    header('Content-Type: ', true);
    exit;
}

/**
 * App Error Action
 */
function app_error(): void
{
    http_response_code(404);
}

/**
 * App Home Action
 */
function app_home(): void
{
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
function account_password(): void
{
    if (!$data = http\req('data')) {
        return;
    }

    if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
        app\msg(app\i18n('Password and password confirmation must be identical'));
        return;
    }

    $data = ['id' => account\data('id'), 'password' => $data['password']];
    ent\save('account', $data);
    app\layout('content', ['error' => $data['_error']['password'] ?? null]);
}

/**
 * Account Login Action
 */
function account_login(): void
{
    if (!$data = http\req('data')) {
        return;
    }

    if (!empty($data['name']) && !empty($data['password']) && ($data = account\login($data['name'], $data['password']))) {
        session\regenerate();
        session\set('account', $data['id']);
        http\redirect();
    }

    app\msg(app\i18n('Invalid name and password combination'));
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    http\redirect();
}
