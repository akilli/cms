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
    message(_('The page %s does not exist', request('path')));
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
    $p = [];
    $q = http_post('q') ? filter_var(http_post('q'), FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR) : http_get('q');

    if ($q && ($s = array_filter(explode(' ', $q)))) {
        $searchable = array_keys(arr_filter($entity['attr'], [['searchable', true]])) ?: ['name'];
        $crit[] = array_map(
            function (string $k) use ($s): array {
                return [$k, $s, CRIT['~']];
            },
            $searchable
        );
        $p['q'] = urlencode(implode(' ', $s));
    }

    $size = size($entity['id'], $crit);
    $pages = (int) ceil($size / $opts['limit']);
    $p['page'] = min(max(http_get('page'), 1), $pages ?: 1);
    $opts['offset'] = ($p['page'] - 1) * $opts['limit'];

    if (($sort = http_get('sort')) && !empty($attrs[$sort])) {
        $p['sort'] = $sort;
        $p['dir'] = http_get('dir') === 'desc' ? 'desc' : 'asc';
        $opts['order'] = [$p['sort'] => $p['dir']];
    }

    layout_load();
    layout_vars('content', ['data' => all($entity['id'], $crit, $opts), 'title' => $entity['name'], 'attr' => $attrs, 'params' => $p]);
    layout_vars('pager', ['size' => $size, 'limit' => $opts['limit'], 'params' => $p]);
    layout_vars('search', ['q' => $q]);
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
    $data = http_post('data');
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
                message(_('File upload failed'));
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
            if (in_array($file['ext'], ['html', 'odt'])) {
                $path = path('tmp', uniqid('import', true));

                if (file_dir($path) && move_uploaded_file($file['tmp_name'], $path . '/' . $file['name'])) {
                    $data = [
                        'name' => pathinfo($file['name'], PATHINFO_FILENAME),
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
            if ($file['ext'] === 'zip') {
                import_project(pathinfo($file['name'], PATHINFO_FILENAME), $file['tmp_name']);
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
        $file = export((int) request('id'));
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
    $id = http_post('id');

    if ($id && size('project', [['id', $id], ['active', true]])) {
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
    if ($data = http_post('data')) {
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

    if ($data = http_post('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($data = account_login($data['name'], $data['password']))) {
            message(_('Welcome %s', $data['name']));
            session_regenerate();
            session('account', $data['id']);
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
