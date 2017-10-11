<?php
declare(strict_types = 1);

namespace act;

use const entity\CRIT;
use function app\_;
use function http\{redirect, req};
use function layout\vars;
use account;
use arr;
use app;
use entity;
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

    session\msg(_('Access denied'));
    redirect();
}

/**
 * Error Action
 */
function error(): void
{
    header('HTTP/1.1 404 Not Found');
    session\msg(_('Page not found'));
    vars('head', ['title' => _('Page not found')]);
}

/**
 * Admin Action
 */
function admin(array $entity): void
{
    index($entity);
}

/**
 * Index Action
 */
function index(array $entity): void
{
    $act = req('act');
    $attrs = entity\attr($entity, $act);
    $opts = ['limit' => app\cfg('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && !empty($entity['attr']['active'])) {
        $crit[] = ['active', true];
    }

    $p = ['page' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $sessKey = 'param/' . $entity['id'] . '/' . $act;
    $rp = req('param') ?: (array) session\get($sessKey);
    $p = array_intersect_key($rp, $p) + $p;

    if ($p['q'] && ($q = array_filter(explode(' ', $p['q'])))) {
        $searchable = array_keys(arr\filter($entity['attr'], [['searchable', true]])) ?: ['name'];
        $c = [];

        foreach ($searchable as $s) {
            $c[] = [$s, $q, CRIT['~']];
        }

        $crit[] = $c;
    } else {
        unset($p['q']);
    }

    $size = entity\size($entity['id'], $crit);
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
    vars('content', ['attr' => $attrs, 'data' => entity\all($entity['id'], $crit, $opts), 'params' => $p, 'title' => $entity['name']]);
    vars('pager', ['limit' => $opts['limit'], 'params' => $p, 'size' => $size]);
    vars('search', ['q' => $p['q'] ?? '']);
    vars('head', ['title' => $entity['name']]);
}

/**
 * Edit Action
 */
function edit(array $entity): void
{
    $data = req('data');
    $id = req('id');

    if ($data) {
        $data['id'] = $id;

        if (entity\save($entity['id'], $data)) {
            redirect(app\url('*/admin'));
        }
    } elseif ($id) {
        $data = entity\one($entity['id'], [['id', $id]]);
    } else {
        $data = entity\data($entity['id']);
    }

    vars('content', ['data' => $data, 'attr' => entity\attr($entity, 'edit'), 'title' => $entity['name']]);
    vars('head', ['title' => $entity['name']]);
}

/**
 * Form Action
 */
function form(array $entity): void
{
    $data = req('data');

    if ($data) {
        $data['active'] = true;

        if (entity\save($entity['id'], $data)) {
            redirect();
        }
    } else {
        $data = entity\data($entity['id']);
    }

    vars('content', ['data' => $data, 'attr' => entity\attr($entity, 'form'), 'title' => $entity['name']]);
    vars('head', ['title' => $entity['name']]);
}

/**
 * Delete Action
 */
function delete(array $entity): void
{
    if ($id = req('id')) {
        entity\delete($entity['id'], [['id', $id]]);
    } else {
        session\msg(_('Nothing selected for deletion'));
    }

    redirect(app\url('*/admin'));
}

/**
 * View Action
 */
function view(array $entity): void
{
    $data = entity\one($entity['id'], [['id', req('id')]]);

    if (!$data || !empty($entity['attr']['active']) && empty($data['active']) && !account\allowed('*/edit')) {
        error();
        return;
    }

    vars('content', ['data' => $data, 'attr' => entity\attr($entity, 'view')]);
    vars('head', ['title' => $data['name']]);
}

/**
 * Media Browser Action
 */
function media_browser(array $entity): void
{
    $exts = app\cfg('file');
    $data = [];

    foreach (entity\all($entity['id'], [], ['order' => ['name' => 'asc']]) as $file) {
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
function media_view(array $entity): void
{
    if (!$data = entity\one($entity['id'], [['id', req('id')]])) {
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
            session\msg(_('File %s already exists', $name));
        } elseif (!file\upload(req('file')['import'][$key]['tmp_name'], $name)) {
            session\msg(_('File upload failed for %s', $name));
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
            session\msg(_('Password and password confirmation must be identical'));
        } else {
            $data = array_replace(account\data(), ['password' => $data['password']]);

            if (!entity\save('account', $data)) {
                session\msg($data['_error']['password'] ?? _('Could not save %s', $data['name']));
            }
        }
    }

    vars('head', ['title' => _('Password')]);
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
            session\msg(_('Welcome %s', $data['name']));
            redirect();
        }

        session\msg(_('Invalid name and password combination'));
    }

    vars('head', ['title' => _('Login')]);
}

/**
 * Account Logout Action
 */
function account_logout(): void
{
    session\regenerate();
    redirect();
}
