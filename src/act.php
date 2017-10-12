<?php
declare(strict_types = 1);

namespace act;

use const ent\CRIT;
use function app\i18n;
use function http\{redirect, req};
use function layout\vars;
use account;
use arr;
use app;
use ent;
use file;
use session;

/**
 * Denied Action
 */
function denied(): void
{
    if (account\guest()) {
        redirect(app\url('account/login'));
    }

    session\msg(i18n('Access denied'));
    redirect();
}

/**
 * Error Action
 */
function error(): void
{
    header('HTTP/1.1 404 Not Found');
    session\msg(i18n('Page not found'));
    vars('head', ['title' => i18n('Page not found')]);
}

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
    $act = req('act');
    $attrs = ent\attr($ent, $act);
    $opts = ['limit' => app\cfg('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && !empty($ent['attr']['active'])) {
        $crit[] = ['active', true];
    }

    $p = ['page' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $sessKey = 'param/' . $ent['id'] . '/' . $act;
    $rp = req('param') ?: (array) session\get($sessKey);
    $p = array_intersect_key($rp, $p) + $p;

    if ($p['q'] && ($q = array_filter(explode(' ', $p['q'])))) {
        $searchable = array_keys(arr\filter($ent['attr'], [['searchable', true]])) ?: ['name'];
        $c = [];

        foreach ($searchable as $s) {
            $c[] = [$s, $q, CRIT['~']];
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
    vars('content', ['attr' => $attrs, 'data' => ent\all($ent['id'], $crit, $opts), 'params' => $p, 'title' => $ent['name']]);
    vars('pager', ['limit' => $opts['limit'], 'params' => $p, 'size' => $size]);
    vars('search', ['q' => $p['q'] ?? '']);
    vars('head', ['title' => $ent['name']]);
}

/**
 * Edit Action
 */
function edit(array $ent): void
{
    $data = req('data');
    $id = req('id');

    if ($data) {
        $data['id'] = $id;

        if (ent\save($ent['id'], $data)) {
            redirect(app\url('*/admin'));
        }
    } elseif ($id) {
        $data = ent\one($ent['id'], [['id', $id]]);
    } else {
        $data = ent\data($ent['id']);
    }

    vars('content', ['data' => $data, 'attr' => ent\attr($ent, 'edit'), 'title' => $ent['name']]);
    vars('head', ['title' => $ent['name']]);
}

/**
 * Form Action
 */
function form(array $ent): void
{
    $data = req('data');

    if ($data) {
        $data['active'] = true;

        if (ent\save($ent['id'], $data)) {
            redirect();
        }
    } else {
        $data = ent\data($ent['id']);
    }

    vars('content', ['data' => $data, 'attr' => ent\attr($ent, 'form'), 'title' => $ent['name']]);
    vars('head', ['title' => $ent['name']]);
}

/**
 * Delete Action
 */
function delete(array $ent): void
{
    if ($id = req('id')) {
        ent\delete($ent['id'], [['id', $id]]);
    } else {
        session\msg(i18n('Nothing selected for deletion'));
    }

    redirect(app\url('*/admin'));
}

/**
 * View Action
 */
function view(array $ent): void
{
    $data = ent\one($ent['id'], [['id', req('id')]]);

    if (!$data || !empty($ent['attr']['active']) && empty($data['active']) && !account\allowed('*/edit')) {
        error();
        return;
    }

    vars('content', ['data' => $data, 'attr' => ent\attr($ent, 'view')]);
    vars('head', ['title' => $data['name']]);
}

/**
 * Media Browser Action
 */
function media_browser(array $ent): void
{
    $exts = app\cfg('file');
    $data = [];

    foreach (ent\all($ent['id'], [], ['order' => ['name' => 'asc']]) as $file) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!empty($exts[$ext]) && in_array('image', $exts[$ext])) {
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
    if (!$data = ent\one($ent['id'], [['id', req('id')]])) {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    header('X-Accel-Redirect: ' . app\asset($data['id']));
    header('X-Accel-Buffering: no');
    header('HTTP/1.1 200 OK');
    header('Content-Type: ', true);
    exit;
}

/**
 * Media Import Action
 */
function media_import(): void
{
    $data = req('data')['import'] ?? [];

    foreach ($data as $key => $name) {
        if (is_file(app\path('data', $name))) {
            session\msg(i18n('File %s already exists', $name));
        } elseif (!file\upload(req('file')['import'][$key]['tmp_name'], $name)) {
            session\msg(i18n('File upload failed for %s', $name));
        }
    }

    redirect(app\url('*/admin'));
}

/**
 * Account Password Action
 */
function account_password(): void
{
    if ($data = req('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            session\msg(i18n('Password and password confirmation must be identical'));
        } else {
            $data = array_replace(account\data(), ['password' => $data['password']]);

            if (!ent\save('account', $data)) {
                session\msg($data['_error']['password'] ?? i18n('Could not save %s', $data['name']));
            }
        }
    }

    vars('head', ['title' => i18n('Password')]);
}

/**
 * Account Login Action
 */
function account_login(): void
{
    if (account\user()) {
        redirect();
    }

    if ($data = req('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account\login($data['name'], $data['password']))) {
            session\regenerate();
            session\set('account', $data['id']);
            session\msg(i18n('Welcome %s', $data['name']));
            redirect();
        }

        session\msg(i18n('Invalid name and password combination'));
    }

    vars('head', ['title' => i18n('Login')]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    redirect();
}
