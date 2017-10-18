<?php
declare(strict_types = 1);

namespace act;

use account;
use arr;
use app;
use ent;
use file;
use http;
use session;

/**
 * Admin Action
 */
function admin(array $ent): void
{
    index($ent);
}

/**
 * Index Action
 */
function index(array $ent): void
{
    $act = http\req('act');
    $attrs = ent\attr($ent, $act);
    $opts = ['limit' => app\cfg('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && !empty($ent['attr']['active'])) {
        $crit[] = ['active', true];
    }

    $p = ['page' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $sessKey = 'param/' . $ent['id'] . '/' . $act;
    $rp = http\req('param') ?: (array) session\get($sessKey);
    $p = array_intersect_key($rp, $p) + $p;

    if ($p['q'] && ($q = array_filter(explode(' ', $p['q'])))) {
        $searchable = array_keys(arr\filter($ent['attr'], [['searchable', true]])) ?: ['name'];
        $c = [];

        foreach ($searchable as $s) {
            $c[] = [$s, $q, APP['crit']['~']];
        }

        $crit[] = $c;
    } else {
        unset($p['q']);
    }

    $size = ent\size($ent['id'], $crit);
    $pages = (int) ceil($size / $opts['limit']) ?: 1;
    $p['page'] = min(max($p['page'], 1), $pages);
    $opts['offset'] = ($p['page'] - 1) * $opts['limit'];

    if ($p['sort'] && !empty($attrs[$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opts['order'] = [$p['sort'] => $p['dir']];
    } else {
        unset($p['sort'], $p['dir']);
    }

    session\set($sessKey, $p);
    app\layout('content', ['attr' => $attrs, 'data' => ent\all($ent['id'], $crit, $opts), 'params' => $p, 'title' => $ent['name']]);
    app\layout('pager', ['limit' => $opts['limit'], 'params' => $p, 'size' => $size]);
    app\layout('search', ['q' => $p['q'] ?? '']);
    app\layout('head', ['title' => $ent['name']]);
}

/**
 * Edit Action
 */
function edit(array $ent): void
{
    $data = http\req('data');
    $id = http\req('id');

    if ($data) {
        $data['id'] = $id;

        if (ent\save($ent['id'], $data)) {
            http\redirect(app\url('*/admin'));
        }
    } elseif ($id) {
        $data = ent\one($ent['id'], [['id', $id]]);
    } else {
        $data = ent\data($ent['id']);
    }

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, 'edit'), 'title' => $ent['name']]);
    app\layout('head', ['title' => $ent['name']]);
}

/**
 * Form Action
 */
function form(array $ent): void
{
    $data = http\req('data');

    if ($data) {
        $data['active'] = true;

        if (ent\save($ent['id'], $data)) {
            http\redirect();
        }
    } else {
        $data = ent\data($ent['id']);
    }

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, 'form'), 'title' => $ent['name']]);
    app\layout('head', ['title' => $ent['name']]);
}

/**
 * Delete Action
 */
function delete(array $ent): void
{
    if ($id = http\req('id')) {
        ent\delete($ent['id'], [['id', $id]]);
    } else {
        app\msg(app\i18n('Nothing selected for deletion'));
    }

    http\redirect(app\url('*/admin'));
}

/**
 * View Action
 */
function view(array $ent): void
{
    $data = ent\one($ent['id'], [['id', http\req('id')]]);

    if (!$data || !empty($ent['attr']['active']) && empty($data['active']) && !app\allowed('*/edit')) {
        app_error();
        return;
    }

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, 'view')]);
    app\layout('head', ['title' => $data['name']]);
}

/**
 * App Denied Action
 */
function app_denied(): void
{
    http_response_code(403);
    app\layout('head', ['title' => app\i18n('Access denied')]);
    app\layout('content', ['title' => app\i18n('Error'), 'message' => app\i18n('Access denied')]);
}

/**
 * App Error Action
 */
function app_error(): void
{
    http_response_code(404);
    app\layout('head', ['title' => app\i18n('Page not found')]);
    app\layout('content', ['title' => app\i18n('Error'), 'message' => app\i18n('Page not found')]);
}

/**
 * App JavaScript Action
 */
function app_js(): void
{
    header('Content-Type: text/javascript', true);
}

/**
 * Media Browser Action
 */
function media_browser(array $ent): void
{
    $data = [];

    foreach (ent\all($ent['id'], [], ['order' => ['name' => 'asc']]) as $file) {
        if (file\type($file['name'], 'image')) {
            $data[] = ['name' => $file['name'], 'url' => app\media($file['id'])];
        }
    }

    header('Content-Type: application/json', true);
    die(json_encode($data));
}

/**
 * Media View Action
 */
function media_view(array $ent): void
{
    if (!$data = ent\one($ent['id'], [['id', http\req('id')]])) {
        http_response_code(404);
        exit;
    }

    http_response_code(200);
    header('X-Accel-Redirect: ' . app\asset($data['id']));
    header('X-Accel-Buffering: no');
    header('Content-Type: ', true);
    exit;
}

/**
 * Media Import Action
 */
function media_import(): void
{
    $data = http\req('data')['import'] ?? [];

    foreach ($data as $key => $name) {
        if (is_file(app\path('data', $name))) {
            app\msg(app\i18n('File %s already exists', $name));
        } elseif (!file\upload(http\req('file')['import'][$key]['tmp_name'], $name)) {
            app\msg(app\i18n('File upload failed for %s', $name));
        }
    }

    http\redirect(app\url('*/admin'));
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
            $data = array_replace(account\data(), ['password' => $data['password']]);
            ent\save('account', $data);
        }
    }

    app\layout('head', ['title' => app\i18n('Password')]);
}

/**
 * Account Login Action
 */
function account_login(): void
{
    if (account\user()) {
        http\redirect();
    }

    if ($data = http\req('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account\login($data['name'], $data['password']))) {
            session\regenerate();
            session\set('account', $data['id']);
            app\msg(app\i18n('Welcome %s', $data['name']));
            http\redirect();
        }

        app\msg(app\i18n('Invalid name and password combination'));
    }

    app\layout('head', ['title' => app\i18n('Login')]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    http\redirect();
}
