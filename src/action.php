<?php
declare(strict_types = 1);

namespace qnd;

use Exception;

/**
 * Denied Action
 *
 * @return void
 */
function action_denied(): void
{
    if (account_user()) {
        message(_('Access denied'));
        redirect();
    }

    message(_('Please enter your credentials'));
    redirect(url('account/login'));
}

/**
 * Error Action
 *
 * @return void
 */
function action_error(): void
{
    message(_('Page not found'));
    layout_load();
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
    $crit = empty($entity['attr']['active']) || $act === 'admin' ? [] : [['active', true]];
    $opts = ['select' => array_keys($attrs), 'limit' => data('app', 'limit')];

    // Params
    $p = ['page' => 0, 'q' => '', 'sort' => null, 'dir' => 'asc'];
    $sessKey = 'param/' . $act . '/' . $entity['id'];
    $rp = request('param') ?: (array) session($sessKey);
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

    session($sessKey, $p);
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
    layout_vars('content', ['data' => $data, 'title' => $entity['name']]);
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
    layout_vars('content', ['data' => $data]);
    layout_vars('head', ['title' => $data['name']]);
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
    echo json_encode($data);
    exit;
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
 * Media Import Action
 *
 * @return void
 */
function action_media_import(): void
{
    if ($files = http_files('import')) {
        foreach ($files as $file) {
            $name = filter_file($file['name'], path('media'));

            if (!file_upload($file['tmp_name'], $name)) {
                message(_('File upload failed for %s', $name));
            }
        }
    } else {
        message(_('No files to import'));
    }

    redirect(url('*/admin'));
}

/**
 * Page Import Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_page_import(array $entity): void
{
    if ($files = http_files('import')) {
        foreach ($files as $file) {
            $info = pathinfo($file['name']);

            if (in_array($info['extension'], ['html', 'odt'])) {
                $path = path('tmp', uniqid('import', true));

                if (file_upload($file['tmp_name'], $path . '/' . $file['name'])) {
                    $data = [
                        'name' => $info['filename'],
                        'active' => true,
                        'content' => import_content($path . '/' . $file['name'], project('id')),
                        'project_id' => project('id')
                    ];
                    save($entity['id'], $data);
                } else {
                    message(_('Import error'));
                }

                file_delete($path);
            } else {
                message(_('Invalid file %s', $file['name']));
            }
        }
    } else {
        message(_('No files to import'));
    }

    redirect(url('*/admin'));
}

/**
 * Project Import Action
 *
 * @return void
 */
function action_project_import(): void
{
    if ($files = http_files('import')) {
        foreach ($files as $file) {
            $info = pathinfo($file['name']);

            if ($info['extension'] === 'zip') {
                import_project($info['filename'], $file['tmp_name']);
                file_delete($file['tmp_name']);
            } else {
                message(_('Invalid file %s', $file['name']));
            }
        }
    } else {
        message(_('No files to import'));
    }

    redirect(url('*/admin'));
}

/**
 * Project Export Action
 *
 * @return void
 */
function action_project_export(): void
{
    try {
        $file = export();
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . basename($file));
        header('Content-Length:' . filesize($file));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file);
        unlink($file);
        exit;
    } catch (Exception $e) {
        message($e->getMessage());
    }

    redirect(url('*/admin'));
}

/**
 * Project Switch Action
 *
 * @return void
 */
function action_project_switch(): void
{
    if (($id = http_data('id')) && size('project', [['id', $id], ['active', true]])) {
        session('project', $id);
    }

    redirect();
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
        logger('Redirected user ' . account('id'));
        redirect();
    }

    if ($data = request('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account_login($data['name'], $data['password']))) {
            message(_('Welcome %s', $data['name']));
            session_regenerate();
            session('account', $data['id']);
            redirect();
        }

        message(_('Invalid name and password combination'));
        logger('invalid user or password: ' . print_r($data, true));
    }

    logger('request: ' . print_r(registry('request'), true));

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
