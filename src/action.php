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
    $opts = [];
    $params = [];
    $search = http_post('search');

    if (!$search && http_param('search')) {
        $search = urldecode(http_param('search'));
    }

    if ($search) {
        $content = array_filter(explode(' ', $search));

        if ($content && ($items = all($entity['id'], ['name' => $content], ['search' => true]))) {
            $crit['id'] = array_keys($items);
            $params['search'] = urlencode(implode(' ', $content));
        } else {
            message(_('No results for provided search terms %s', $search));
        }
    }

    $size = size($entity['id'], $crit);
    $opts['limit'] = $action === 'index' ? config('entity.index') : config('entity.list');
    $page = max((int) http_param('page'), 1);
    $opts['offset'] = ($page - 1) * $opts['limit'];

    if ($page > 1) {
        $params['page'] = $page;
    }

    if (($sort = http_param('sort')) && !empty($attrs[$sort])) {
        $dir = http_param('dir') === 'desc' ? 'desc' : 'asc';
        $opts['order'] = [$sort => $dir];
        $params['sort'] = $sort;
        $params['dir'] = $dir;
    }

    $data = all($entity['id'], $crit, $opts);
    array_walk(
        $attrs,
        function (& $attr, $code) use ($params) {
            $dir = !empty($params['sort']) && $params['sort'] === $code && $params['dir'] === 'asc' ? 'desc' : 'asc';
            $attr['url'] = url('*/*', array_replace($params, ['sort' => $code, 'dir' => $dir]));
        }
    );
    unset($params['page']);

    layout_load();
    vars('content', ['data' => $data, 'title' => _($entity['name']), 'attr' => $attrs]);
    vars(
        'pager',
        [
            'pages' => (int) ceil($size / $opts['limit']),
            'page' => $page,
            'limit' => $opts['limit'],
            'offset' => $opts['offset'],
            'size' => $size,
            'params' => $params
        ]
    );
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
 * Project Switch Action
 *
 * @return void
 */
function action_project_switch()
{
    $id = (int) http_param('id');

    if (size('project', ['id' => $id])) {
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
            && ($item = one('user', ['username' => $data['username'], 'active' => true, 'project_id' => [0, project('id')]]))
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
