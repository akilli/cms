<?php
declare(strict_types = 1);

namespace qnd;

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
 * Edit Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_edit(array $entity): void
{
    $data = http_post('data');

    if ($data) {
        // Perform save callback and redirect to admin on success
        if (save($entity['uid'], $data)) {
            redirect(url('*/admin'));
        }

        $data = array_filter(
            $data,
            function ($item) {
                return empty($item['_success']);
            }
        );
    } elseif (is_array(http_post('edit'))) {
        // We just selected multiple items to edit on the admin page
        $data = all($entity['uid'], ['id' => array_keys(http_post('edit'))]);
    } elseif (request('id') !== null) {
        // We just clicked on an edit link, p.e. on the admin page
        $data = all($entity['uid'], ['id' => request('id')]);
    } else {
        // Initial create action call
        $data = entity($entity['uid'], (int) http_post('create'));
    }

    layout_load();
    vars('content', ['data' => $data, 'title' => $entity['name']]);
    vars('head', ['title' => $entity['name']]);
}

/**
 * Form Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_form(array $entity): void
{
    $data = http_post('data');

    if ($data) {
        // Perform save callback and redirect to homepage on success
        if (save($entity['uid'], $data)) {
            redirect();
        }

        $data = array_filter(
            $data,
            function ($item) {
                return empty($item['_success']);
            }
        );
    } else {
        // Initial action call
        $data = entity($entity['uid'], 1);
    }

    layout_load();
    vars('content', ['data' => $data, 'title' => $entity['name']]);
    vars('head', ['title' => $entity['name']]);
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
    $data = http_post('edit');

    if ($data) {
        delete($entity['uid'], ['id' => array_keys($data)]);
    } else {
        message(_('You did not select anything to delete'));
    }

    redirect(url('*/admin'));
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
    $action = request('action');
    $attrs = entity_attr($entity['uid'], $action);
    $crit = empty($entity['attr']['active']) || $action === 'admin' ? [] : ['active' => true];
    $opts = [];
    $p = [];
    $q = http_post('q') ? filter_var(http_post('q'), FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR) : http_get('q');

    if ($q && ($s = array_filter(explode(' ', $q)))) {
        $crit['name'] = $s;
        $opts['search'] = ['name'];
        $p['q'] = urlencode(implode(' ', $s));
    }

    $opts['limit'] = data('limit', $action);
    $size = size($entity['uid'], $crit, $opts);
    $pages = (int) ceil($size / $opts['limit']);
    $p['page'] = min(max(http_get('page'), 1), $pages ?: 1);
    $opts['offset'] = ($p['page'] - 1) * $opts['limit'];

    if (($sort = http_get('sort')) && !empty($attrs[$sort])) {
        $p['sort'] = $sort;
        $p['dir'] = http_get('dir') === 'desc' ? 'desc' : 'asc';
        $opts['order'] = [$p['sort'] => $p['dir']];
    }

    layout_load();
    vars('content', ['data' => all($entity['uid'], $crit, $opts), 'title' => $entity['name'], 'attr' => $attrs, 'params' => $p]);
    vars('pager', ['size' => $size, 'limit' => $opts['limit'], 'params' => $p]);
    vars('search', ['q' => $q]);
    vars('head', ['title' => $entity['name']]);
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
    $item = one($entity['uid'], ['id' => request('id')]);

    // Item does not exist or is inactive
    if (!$item || !empty($entity['attr']['active']) && empty($item['active']) && !allowed('edit')) {
        action_error();
        return;
    }

    // Preview
    if (!empty($entity['attr']['active']) && empty($item['active'])) {
        message(_('Preview'));
    }

    layout_load();
    vars('content', ['item' => $item]);
    vars('head', ['title' => $item['name']]);
}

/**
 * Denied Action
 *
 * @return void
 */
function action_denied(): void
{
    message(_('Access denied'));
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
 * Project Import Action
 *
 * @return void
 */
function action_project_import(): void
{
    if (!$file = http_files('import')) {
        message(_('No file to import'));
    } elseif ($file['ext'] === 'zip') {
        import_zip($file['tmp_name']);
        file_delete($file['tmp_name']);
    } elseif (in_array($file['ext'], ['html', 'odt'])) {
        import_page($file['tmp_name']);
        file_delete($file['tmp_name']);
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

    if ($id && size('project', ['id' => $id, 'active' => true])) {
        session('project', $id);
    }

    redirect();
}

/**
 * Account Dashboard Action
 *
 * @return void
 */
function action_account_dashboard(): void
{
    layout_load();
    vars('head', ['title' => _('Dashboard')]);
}

/**
 * Account Password Action
 *
 * @return void
 */
function action_account_password(): void
{
    if ($item = http_post('data')) {
        if (empty($item['password']) || empty($item['confirmation']) || $item['password'] !== $item['confirmation']) {
            message(_('Password and password confirmation must be identical'));
        } else {
            $data = [account('id') => array_replace(account(), ['password' => $item['password']])];

            if (!save('account', $data)) {
                message($data[account('id')]['_error']['password'] ?? _('Could not save %s', account('name')));
            }
        }
    }

    layout_load();
    vars('head', ['title' => _('Password')]);
}

/**
 * Account Login Action
 *
 * @return void
 */
function action_account_login(): void
{
    if (registered()) {
        redirect(url('account/dashboard'));
    }

    if ($data = http_post('data')) {
        if (!empty($data['name']) && !empty($data['password']) && ($item = account_login($data['name'], $data['password']))) {
            message(_('Welcome %s', $item['name']));
            session_regenerate();
            session('account', $item['id']);
            redirect(url('account/dashboard'));
        }

        message(_('Invalid name and password combination'));
    }

    layout_load();
    vars('head', ['title' => _('Login')]);
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
