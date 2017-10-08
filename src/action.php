<?php
declare(strict_types = 1);

namespace cms;

/**
 * Denied Action
 */
function action_denied(): void
{
    if (account_guest()) {
        redirect(url('account/login'));
    }

    msg(_('Access denied'));
    redirect();
}

/**
 * Error Action
 */
function action_error(): void
{
    header('HTTP/1.1 404 Not Found');
    msg(_('Page not found'));
    layout_vars('head', ['title' => _('Page not found')]);
}

/**
 * Admin Action
 */
function action_admin(array $entity): void
{
    action_index($entity);
}

/**
 * Index Action
 */
function action_index(array $entity): void
{
    $act = request('action');
    $attrs = entity_attr($entity, $act);
    $opts = ['limit' => cfg('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && !empty($entity['attr']['active'])) {
        $crit[] = ['active', true];
    }

    $p = ['page' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $sessKey = 'param/' . $entity['id'] . '/' . $act;
    $rp = request('param') ?: (array) session_get($sessKey);
    $p = array_intersect_key($rp, $p) + $p;

    if ($p['q'] && ($q = array_filter(explode(' ', $p['q'])))) {
        $searchable = array_keys(arr_filter($entity['attr'], [['searchable', true]])) ?: ['name'];
        $c = [];

        foreach ($searchable as $s) {
            $c[] = [$s, $q, CRIT['~']];
        }

        $crit[] = $c;
    } else {
        unset($p['q']);
    }

    $size = size($entity['id'], $crit);
    $pages = (int) ceil($size / $opts['limit']) ?: 1;
    $p['page'] = min(max($p['page'], 1), $pages);
    $opts['offset'] = ($p['page'] - 1) * $opts['limit'];

    if ($p['sort'] && !empty($attrs[$p['sort']])) {
        $p['dir'] = $p['dir'] === 'desc' ? 'desc' : 'asc';
        $opts['order'] = [$p['sort'] => $p['dir']];
    } else {
        unset($p['sort'], $p['dir']);
    }

    session_set($sessKey, $p);
    layout_vars('content', ['attr' => $attrs, 'data' => all($entity['id'], $crit, $opts), 'params' => $p, 'title' => $entity['name']]);
    layout_vars('pager', ['limit' => $opts['limit'], 'params' => $p, 'size' => $size]);
    layout_vars('search', ['q' => $p['q'] ?? '']);
    layout_vars('head', ['title' => $entity['name']]);
}

/**
 * Edit Action
 */
function action_edit(array $entity): void
{
    $data = request('data');
    $id = request('id');

    if ($data) {
        $data['id'] = $id;

        if (save($entity['id'], $data)) {
            redirect(url('*/admin'));
        }
    } elseif ($id) {
        $data = one($entity['id'], [['id', $id]]);
    } else {
        $data = entity($entity['id']);
    }

    layout_vars('content', ['data' => $data, 'attr' => entity_attr($entity, 'edit'), 'title' => $entity['name']]);
    layout_vars('head', ['title' => $entity['name']]);
}

/**
 * Form Action
 */
function action_form(array $entity): void
{
    $data = request('data');

    if ($data) {
        $data['active'] = true;

        if (save($entity['id'], $data)) {
            redirect();
        }
    } else {
        $data = entity($entity['id']);
    }

    layout_vars('content', ['data' => $data, 'attr' => entity_attr($entity, 'form'), 'title' => $entity['name']]);
    layout_vars('head', ['title' => $entity['name']]);
}

/**
 * Delete Action
 */
function action_delete(array $entity): void
{
    if ($id = request('id')) {
        delete($entity['id'], [['id', $id]]);
    } else {
        msg(_('Nothing selected for deletion'));
    }

    redirect(url('*/admin'));
}

/**
 * View Action
 */
function action_view(array $entity): void
{
    $data = one($entity['id'], [['id', request('id')]]);

    if (!$data || !empty($entity['attr']['active']) && empty($data['active']) && !allowed('*/edit')) {
        action_error();
        return;
    }

    layout_vars('content', ['data' => $data, 'attr' => entity_attr($entity, 'view')]);
    layout_vars('head', ['title' => $data['name']]);
}

/**
 * Media Browser Action
 */
function action_media_browser(array $entity): void
{
    $exts = cfg('file');
    $data = [];

    foreach (all($entity['id'], [], ['order' => ['name' => 'asc']]) as $file) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!empty($exts[$ext]) && in_array('image', $exts[$ext])) {
            $data[] = ['name' => $file['name'], 'url' => url_media($file['id'])];
        }
    }

    header('Content-Type: application/json', true);
    die(json_encode($data));
}

/**
 * Media View Action
 */
function action_media_view(array $entity): void
{
    if (!$data = one($entity['id'], [['id', request('id')]])) {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    header('X-Accel-Redirect: ' . url_data($data['id']));
    header('X-Accel-Buffering: no');
    header('HTTP/1.1 200 OK');
    header('Content-Type: ', true);
    exit;
}

/**
 * Media Import Action
 */
function action_media_import(): void
{
    $files = request('file')['import'] ?? null;

    if ($files) {
        foreach ($files as $file) {
            if (is_file(path('data', $file['name']))) {
                msg(_('File %s already exists', $file['name']));
            } elseif (!file_upload($file['tmp_name'], $file['name'])) {
                msg(_('File upload failed for %s', $file['name']));
            }
        }
    } else {
        msg(_('No files to import'));
    }

    redirect(url('*/admin'));
}

/**
 * Account Password Action
 */
function action_account_password(): void
{
    if ($data = request('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            msg(_('Password and password confirmation must be identical'));
        } else {
            $data = array_replace(account(), ['password' => $data['password']]);

            if (!save('account', $data)) {
                msg($data['_error']['password'] ?? _('Could not save %s', $data['name']));
            }
        }
    }

    layout_vars('head', ['title' => _('Password')]);
}

/**
 * Account Login Action
 */
function action_account_login(): void
{
    if (account_user()) {
        redirect();
    }

    if ($data = request('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account_login($data['name'], $data['password']))) {
            session_regenerate();
            session_set('account', $data['id']);
            msg(_('Welcome %s', $data['name']));
            redirect();
        }

        msg(_('Invalid name and password combination'));
    }

    layout_vars('head', ['title' => _('Login')]);
}

/**
 * Account Logout Action
 */
function action_account_logout(): void
{
    session_regenerate();
    redirect();
}
