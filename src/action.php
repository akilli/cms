<?php
declare(strict_types = 1);

namespace cms;

/**
 * Denied Action
 *
 * @return void
 */
function action_denied(): void
{
    if (account_guest()) {
        redirect(url('account/login'));
    }

    header('HTTP/1.1 403 Forbidden');
    message(_('Access denied'));
    redirect();
}

/**
 * Error Action
 *
 * @return void
 */
function action_error(): void
{
    header('HTTP/1.1 404 Not Found');
    message(_('Page not found'));
    layout_load();
    layout_vars('head', ['title' => _('Page not found')]);
}

/**
 * Admin Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_admin(array $entity): void
{
    action_index($entity);
}

/**
 * Index Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_index(array $entity): void
{
    $act = request('action');
    $attrs = entity_attr($entity, $act);
    $opts = ['limit' => data('app', 'limit')];
    $crit = [];

    if ($act !== 'admin' && !empty($entity['attr']['active'])) {
        $crit[] = ['active', true];
    }

    // Params
    $p = ['page' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $sessKey = 'param/' . $act . '/' . $entity['id'];
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
    layout_load();
    layout_vars('content', ['attr' => $attrs, 'data' => all($entity['id'], $crit, $opts), 'params' => $p, 'title' => $entity['name']]);
    layout_vars('pager', ['limit' => $opts['limit'], 'params' => $p, 'size' => $size]);
    layout_vars('search', ['q' => $p['q'] ?? '']);
    layout_vars('head', ['title' => $entity['name']]);
}

/**
 * Edit Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_edit(array $entity): void
{
    $data = request('data');
    $id = request('id');

    if ($data) {
        $data['id'] = $id;

        // Perform save callback and redirect to admin on success
        if (save($entity['id'], $data)) {
            redirect(url('*/admin'));
        }
    } elseif ($id) {
        // We just clicked on an edit link, p.e. on the admin page
        $data = one($entity['id'], [['id', $id]]);
    } else {
        // Initial create action call
        $data = entity($entity['id']);
    }

    layout_load();
    layout_vars('content', ['data' => $data, 'attr' => entity_attr($entity, 'edit'), 'title' => $entity['name']]);
    layout_vars('head', ['title' => $entity['name']]);
}

/**
 * Delete Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_delete(array $entity): void
{
    if ($id = request('id')) {
        delete($entity['id'], [['id', $id]]);
    } else {
        message(_('Nothing selected for deletion'));
    }

    redirect(url('*/admin'));
}

/**
 * View Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_view(array $entity): void
{
    $data = one($entity['id'], [['id', request('id')]]);

    // Item does not exist or is inactive
    if (!$data || !empty($entity['attr']['active']) && empty($data['active']) && !allowed('*/edit')) {
        action_error();
        return;
    }

    layout_load();
    layout_vars('content', ['data' => $data, 'attr' => entity_attr($entity, 'view')]);
    layout_vars('head', ['title' => $data['name']]);
}

/**
 * App Home Action
 *
 * @return void
 */
function action_app_home(): void
{
    layout_load();
}

/**
 * Media Browser Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_media_browser(array $entity): void
{
    $exts = data('file');
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
 *
 * @param array $entity
 *
 * @return void
 */
function action_media_view(array $entity): void
{
    if ($data = one($entity['id'], [['id', request('id')]])) {
        header('X-Accel-Redirect: ' . url_asset($data['id']));
        header('X-Accel-Buffering: no');
        header('HTTP/1.1 200 OK');
        header('Content-Type: ', true);
        exit;
    }

    header('HTTP/1.1 404 Not Found');
    exit;
}

/**
 * Account Password Action
 *
 * @return void
 */
function action_account_password(): void
{
    if ($data = request('data')) {
        if (empty($data['password']) || empty($data['confirmation']) || $data['password'] !== $data['confirmation']) {
            message(_('Password and password confirmation must be identical'));
        } else {
            $data = array_replace(account(), ['password' => $data['password']]);

            if (!save('account', $data)) {
                message($data['_error']['password'] ?? _('Could not save %s', account('name')));
            }
        }
    }

    layout_load();
    layout_vars('head', ['title' => _('Password')]);
}

/**
 * Account Login Action
 *
 * @return void
 */
function action_account_login(): void
{
    if (account_user()) {
        redirect();
    }

    if ($data = request('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account_login($data['name'], $data['password']))) {
            message(_('Welcome %s', $data['name']));
            session_regenerate();
            session_set('account', $data['id']);
            redirect();
        }

        message(_('Invalid name and password combination'));
    }

    layout_load();
    layout_vars('head', ['title' => _('Login')]);
}

/**
 * Account Logout Action
 *
 * @return void
 */
function action_account_logout(): void
{
    session_regenerate();
    redirect();
}
