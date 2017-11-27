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
    $opt = ['limit' => app\cfg('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && !empty($ent['attr']['active'])) {
        $crit[] = ['active', true];
    }

    $p = ['page' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $sessKey = 'param/' . $ent['id'] . '/' . $act;
    $rp = http\req('param') ?: (array) session\get($sessKey);
    $p = arr\replace($p, $rp);

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
    $pages = (int) ceil($size / $opt['limit']) ?: 1;
    $p['page'] = min(max($p['page'], 1), $pages);
    $opt['offset'] = ($p['page'] - 1) * $opt['limit'];

    if ($p['sort'] && !empty($attrs[$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opt['order'] = [$p['sort'] => $p['dir']];
    } else {
        unset($p['sort'], $p['dir']);
    }

    session\set($sessKey, $p);
    app\layout('content', ['attr' => $attrs, 'data' => ent\all($ent['id'], $crit, $opt), 'params' => $p, 'title' => $ent['name']]);
    app\layout('pager', ['limit' => $opt['limit'], 'params' => $p, 'size' => $size]);
    app\layout('search', ['q' => $p['q'] ?? '']);
    app\layout('meta', ['title' => $ent['name']]);
}

/**
 * Create Action
 */
function create(array $ent): void
{
    if (($data = http\req('data')) && ent\create($ent['id'], $data)) {
        http\redirect(app\url('*/*/' . $data['id']));
    }

    $data = $data ?: ent\data($ent['id'], 'create');

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, 'create'), 'title' => $ent['name']]);
    app\layout('meta', ['title' => $ent['name']]);
}

/**
 * Update Action
 */
function update(array $ent): void
{
    if (!$id = http\req('id')) {
        app\msg(app\i18n('Nothing selected'));
        http\redirect(app\url('*/admin'));
    }

    if ($data = http\req('data')) {
        $data['_id'] = $id;
        ent\update($ent['id'], $data);
    } else {
        $data = ent\one($ent['id'], [['id', $id]]);
    }

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, 'update'), 'title' => $ent['name']]);
    app\layout('meta', ['title' => $ent['name']]);
}

/**
 * Delete Action
 */
function delete(array $ent): void
{
    if ($id = http\req('id')) {
        ent\delete($ent['id'], [['id', $id]]);
    } else {
        app\msg(app\i18n('Nothing selected'));
    }

    http\redirect(app\url('*/admin'));
}

/**
 * View Action
 */
function view(array $ent): void
{
    $data = ent\one($ent['id'], [['id', http\req('id')]]);

    if (!$data || !empty($ent['attr']['active']) && empty($data['active']) && !app\allowed('*/create') && !app\allowed('*/update')) {
        app_error();
        return;
    }

    app\layout('content', ['data' => $data, 'attr' => ent\attr($ent, 'view')]);
    app\layout('meta', ['title' => $data['name']]);
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
 * Browser Action
 */
function browser(array $ent): void
{
    index($ent);
    $p = ['rte' => http\req('param')['CKEditorFuncNum'] ?? null];
    app\layout('content', ['params' => array_replace(app\layout('content')['vars']['params'], $p)]);
    app\layout('pager', ['params' => array_replace(app\layout('pager')['vars']['params'], $p)]);
}

/**
 * App Denied Action
 */
function app_denied(): void
{
    http_response_code(403);
    app\layout('meta', ['title' => app\i18n('Access denied')]);
    app\layout('content', ['title' => app\i18n('Error'), 'message' => app\i18n('Access denied')]);
}

/**
 * App Error Action
 */
function app_error(): void
{
    http_response_code(404);
    app\layout('meta', ['title' => app\i18n('Page not found')]);
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
 * Account Password Action
 */
function account_password(): void
{
    if ($data = http\req('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            app\msg(app\i18n('Password and password confirmation must be identical'));
        } else {
            $data = array_replace(account\data(), ['password' => $data['password']]);
            ent\update('account', $data);
        }
    }

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
            app\msg(app\i18n('Welcome %s', $data['name']));
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
