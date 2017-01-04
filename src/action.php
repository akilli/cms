<?php
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
 * Create Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_create(array $entity): void
{
    $data = http_post('data');

    if ($data) {
        // Perform save callback and redirect to admin on success
        if (save($entity['id'], $data)) {
            redirect(url('*/admin'));
        }

        $data = array_filter(
            $data,
            function ($item) {
                return empty($item['_success']);
            }
        );
    } else {
        // Initial create action call
        $data = entity($entity['id'], (int) http_post('create'));
    }

    layout_load();
    vars('content', ['data' => $data, 'title' => $entity['name']]);
    vars('head', ['title' => $entity['name']]);
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
        if (save($entity['id'], $data)) {
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
        $data = all($entity['id'], ['id' => array_keys(http_post('edit'))]);
    } elseif (request('id') !== null) {
        // We just clicked on an edit link, p.e. on the admin page
        $data = all($entity['id'], ['id' => request('id')]);
    }

    if (!$data) {
        message(_('You did not select anything to edit'));
        redirect(url('*/admin'));
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
        delete($entity['id'], ['id' => array_keys($data)]);
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
    $attrs = entity_attr($entity['id'], $action);
    $crit = empty($entity['attr']['active']) || $action === 'admin' ? [] : ['active' => true];
    $p = [];
    $q = http_post('q') ? filter_var(http_post('q'), FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR) : null;

    if ($q || ($q = http_get('q'))) {
        if (($s = array_filter(explode(' ', $q))) && ($all = all($entity['id'], ['name' => $s], ['search' => true]))) {
            $crit['id'] = array_keys($all);
            $p['q'] = urlencode(implode(' ', $s));
        } else {
            message(_('No results for provided query %s', $q));
        }
    }

    $opts = ['limit' => abs((int) data('limit', $action)) ?: 10];
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
    vars('content', ['data' => all($entity['id'], $crit, $opts), 'title' => $entity['name'], 'attr' => $attrs, 'params' => $p]);
    vars('pager', ['size' => $size, 'limit' => $opts['limit'], 'params' => $p]);
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
    // Item does not exist or is inactive
    if (!($item = one($entity['id'], ['id' => request('id')]))
        || !empty($entity['attr']['active']) && empty($item['active']) && !allowed('edit')
    ) {
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
    } elseif (in_array($file['ext'], ['html', 'odt'])) {
        $path = path('tmp', uniqid($file['name'], true));
        file_copy($file['tmp_name'], $path . '/' . $file['name']);
        import_page($path . '/' . $file['name']);
        file_delete($path);
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
 * Account Profile Action
 *
 * @return void
 */
function action_account_profile(): void
{
    $account = account();

    if ($item = http_post('data')) {
        $data = [$account['id'] => array_replace($account, $item)];
        save('account', $data);
    }

    if (!$item = account()) {
        redirect();
    }

    layout_load();
    vars('content', ['item' => $item]);
    vars('head', ['title' => _('Profile')]);
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
        if (!empty($data['username']) && !empty($data['password']) && ($item = account_login($data['username'], $data['password']))) {
            message(_('Welcome %s', $item['name']));
            session_regenerate_id(true);
            session('account', $item['id']);
            redirect(url('account/dashboard'));
        }

        message(_('Invalid username and password combination'));
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
    session_regenerate_id(true);
    session_destroy();
    redirect();
}
