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
 * Index Action
 */
function index(array $ent): void
{
    $act = http\req('act');
    $attrs = ent\attr($ent, $act);
    $opt = ['limit' => app\cfg('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && $ent['version']) {
        $crit[] = ['status', 'published'];
    }

    $p = ['cur' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $p += $act === 'browser' ? ['CKEditorFuncNum' => null] : [];
    $sessKey = 'param/' . $ent['id'] . '/' . $act;
    $rp = http\req('param') ?: (array) session\get($sessKey);
    $p = arr\replace($p, $rp);

    if ($p['q'] && ($q = array_filter(explode(' ', (string) $p['q'])))) {
        $searchable = array_keys(arr\crit($ent['attr'], [['searchable', true]])) ?: ['name'];
        $c = [];

        foreach ($searchable as $s) {
            $c[] = [$s, $q, APP['crit']['~']];
        }

        $crit[] = $c;
    } else {
        unset($p['q']);
    }

    $size = ent\size($ent['id'], $crit);
    $pages = (int) ceil($size / $opt['limit']) ?: 1;
    $p['cur'] = min(max($p['cur'], 1), $pages);
    $opt['offset'] = ($p['cur'] - 1) * $opt['limit'];

    if ($p['sort'] && !empty($attrs[$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        unset($p['sort'], $p['dir']);
    }

    session\set($sessKey, $p);
    app\layout('content', ['attr' => $attrs, 'data' => ent\all($ent['id'], $crit, $opt), 'params' => $p, 'title' => $ent['name'], 'act' => $act]);
    app\layout('pager', ['limit' => $opt['limit'], 'params' => $p, 'size' => $size]);
    app\layout('search', ['q' => $p['q'] ?? '']);
    app\layout('meta', ['title' => $ent['name']]);
}

/**
 * Admin Action
 */
function admin(array $ent): void
{
    index($ent);
}

/**
 * Browser Action
 */
function browser(array $ent): void
{
    index($ent);
}

/**
 * Asset Action
 */
function asset(array $ent): void
{
    if (!($id = http\req('id')) || !is_file(app\path('asset', $ent['id'] . '/' . $id))) {
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
 * Edit Action
 */
function edit(array $ent): void
{
    $id = http\req('id');
    $data = http\req('data');
    $data += $data && $id ? ['id' => $id] : [];
    $act = http\req('act');

    if ($data && ent\save($ent['id'], $data) && $act === 'edit') {
        $id = ($id ?: $data['id']);
        http\redirect(app\url('*/*/' . $id));
    }

    if ($id) {
        $base = ent\one($ent['id'], [['id', $id]]);

        if ($act === 'edit' && $ent['version']) {
            $version = ent\one('version', [['ent', $ent['id']], ['ent_id', $id]], ['order' => ['date' => 'desc']]);
            $base = arr\replace($base, $version['data'] ?? []);
        }
    } else {
        $base = ent\data($ent['id']);
    }

    $data = array_replace($base, $data);

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, $act), 'title' => $ent['name']]);
    app\layout('meta', ['title' => $ent['name']]);
}

/**
 * Form Action
 */
function form(array $ent): void
{
    edit($ent);
}

/**
 * Delete Action
 */
function delete(array $ent): void
{
    if ($id = http\req('id')) {
        ent\delete($ent['id'], [['id', $id]]);
    } else {
        app\msg(app\i18n('Nothing to delete'));
    }

    http\redirect(app\url('*/admin'));
}

/**
 * View Action
 */
function view(array $ent): void
{
    $crit = [['id', http\req('id')]];

    if (!app\allowed('*/edit') && $ent['version']) {
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
 * App Error Action
 */
function app_error(): void
{
    http_response_code(404);
    app\layout('content', ['message' => app\i18n('Page not found')]);
    app\layout('meta', ['title' => app\i18n('Page not found')]);
}

/**
 * App Home Action
 */
function app_home(): void
{
    app\layout('meta', ['title' => app\cfg('app', 'name')]);
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
    if ($data = http\req('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            app\msg(app\i18n('Password and password confirmation must be identical'));
        } else {
            $data = ['id' => account\data('id'), 'password' => $data['password']];
            ent\save('account', $data);
        }
    }

    app\layout('content', ['error' => $data['_error']['password'] ?? null]);
    app\layout('meta', ['title' => app\i18n('Password')]);
}

/**
 * Account Login Action
 */
function account_login(): void
{
    if ($data = http\req('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account\login($data['name'], $data['password']))) {
            session\regenerate();
            session\set('account', $data['id']);
            http\redirect();
        }

        app\msg(app\i18n('Invalid name and password combination'));
    }

    app\layout('meta', ['title' => app\i18n('Login')]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    http\redirect();
}
