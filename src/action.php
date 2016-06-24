<?php
namespace qnd;

/**
 * Create Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_create(array $entity)
{
    $data = http_post('data');

    if ($data && save($entity['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data) {
        // Initial create action call
        $data = skeleton($entity['id'], (int) http_post('create'));
    }

    layout_load();
    vars('content', ['data' => $data, 'title' => _($entity['name'])]);
    vars('head', ['title' => _($entity['name'])]);
}

/**
 * Edit Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_edit(array $entity)
{
    $data = http_post('data');

    if ($data && save($entity['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data && is_array(http_post('edit'))) {
        // We just selected multiple items to edit on the index page
        $data = all($entity['id'], ['id' => array_keys(http_post('edit'))]);
    } elseif (!$data && http_param('id') !== null) {
        // We just clicked on an edit link, p.e. on the index page
        $data = all($entity['id'], ['id' => http_param('id')]);
    }

    if (!$data) {
        message(_('You did not select anything to edit'));
        redirect(allowed('index') ? '*/index' : '');
    }

    layout_load();
    vars('content', ['data' => $data, 'title' => _($entity['name'])]);
    vars('head', ['title' => _($entity['name'])]);
}

/**
 * Delete Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_delete(array $entity)
{
    $data = http_post('edit');

    if ($data) {
        delete($entity['id'], ['id' => array_keys($data)]);
    } else {
        message(_('You did not select anything to delete'));
    }

    redirect(allowed('index') ? '*/index' : '');
}

/**
 * View Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_view(array $entity)
{
    // Item does not exist or is inactive
    if (!($item = one($entity['id'], ['id' => http_param('id')]))
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

    if (!empty($item['meta']) && is_array($item['meta'])) {
        vars('head', ['meta' => $item['meta']]);
    }
}

/**
 * Index Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_index(array $entity)
{
    $action = request('action');
    $attrs = array_filter(
        $entity['attr'],
        function ($attr) use ($action) {
            return data_action($action, $attr);
        }
    );
    $crit = empty($entity['attr']['active']) || $action === 'index' ? [] : ['active' => true];
    $p = [];
    $q = http_post('q');

    if (!$q && http_param('q')) {
        $q = urldecode(http_param('q'));
    }

    if ($q) {
        $q = filter_var($q, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR);

        if (($s = array_filter(explode(' ', $q))) && ($all = all($entity['id'], ['name' => $s], ['search' => true]))) {
            $crit['id'] = array_keys($all);
            $p['q'] = urlencode(implode(' ', $s));
        } else {
            message(_('No results for provided query %s', $q));
        }
    }

    $opts = ['limit' => abs((int) config('entity.limit')) ?: 10];
    $size = size($entity['id'], $crit);
    $pages = (int) ceil($size / $opts['limit']);
    $p['page'] = min(max((int) http_param('page'), 1), $pages ?: 1);
    $opts['offset'] = ($p['page'] - 1) * $opts['limit'];

    if (($sort = http_param('sort')) && !empty($attrs[$sort])) {
        $p['sort'] = $sort;
        $p['dir'] = http_param('dir') === 'desc' ? 'desc' : 'asc';
        $opts['order'] = [$p['sort'] => $p['dir']];
    }

    layout_load();
    vars('content', ['data' => all($entity['id'], $crit, $opts), 'title' => _($entity['name']), 'attr' => $attrs, 'params' => $p]);
    vars('pager', ['size' => $size, 'limit' => $opts['limit'], 'params' => $p]);
    vars('head', ['title' => _($entity['name'])]);
}

/**
 * List Action
 *
 * @param array $entity
 *
 * @return void
 */
function action_list(array $entity)
{
    action_index($entity);
}

/**
 * Denied Action
 *
 * @return void
 */
function action_denied()
{
    message(_('Access denied'));
    redirect('user/login');
}

/**
 * Error Action
 *
 * @return void
 */
function action_error()
{
    message(_('The page %s does not exist', request('path')));
    layout_load();
}

/**
 * Home Action
 *
 * @return void
 */
function action_index_index()
{
    layout_load();
}

/**
 * Project Import Action
 *
 * @return void
 */
function action_project_import()
{
    if (!$file = http_files('import')) {
        message(_('No file to import'));
        redirect(allowed('index') ? '*/index' : '');
    }
}

/**
 * Project Export Action
 *
 * @return void
 */
function action_project_export()
{
}

/**
 * Project Switch Action
 *
 * @return void
 */
function action_project_switch()
{
    $id = (int) http_post('id');

    if (size('project', ['id' => $id, 'active' => true])) {
        session('project', $id);
    }

    redirect();
}

/**
 * User Dashboard Action
 *
 * @return void
 */
function action_user_dashboard()
{
    layout_load();
    vars('head', ['title' => _('Dashboard')]);
}

/**
 * User Profile Action
 *
 * @return void
 */
function action_user_profile()
{
    $user = user();

    if (!$user || !registered()) {
        redirect();
    }

    if ($item = http_post('data')) {
        $data = [$user['id'] => array_replace($user, $item)];
        save('user', $data);
    }

    if (!$item = user()) {
        redirect();
    }

    layout_load();
    vars('content', ['item' => $item]);
    vars('head', ['title' => _('User Profile')]);
}

/**
 * User Login Action
 *
 * @return void
 */
function action_user_login()
{
    if (registered()) {
        redirect('*/dashboard');
    }

    if ($data = http_post('data')) {
        if (!empty($data['username'])
            && !empty($data['password'])
            && ($item = one('user', ['username' => $data['username'], 'active' => true, 'project_id' => [PROJECT_DEFAULT, project('id')]]))
            && password_verify($data['password'], $item['password'])
        ) {
            message(_('Welcome %s', $item['name']));
            session_regenerate_id(true);
            session('user', $item['id']);
            redirect('*/dashboard');
        }

        message(_('Invalid username and password combination'));
    }

    layout_load();
    vars('head', ['title' => _('User Login')]);
}

/**
 * User Logout Action
 *
 * @return void
 */
function action_user_logout()
{
    session_regenerate_id(true);
    session_destroy();
    redirect();
}
