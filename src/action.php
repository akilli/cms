<?php
namespace qnd;

/**
 * Create Action
 *
 * @return void
 */
function action_create()
{
    $entity = _action_entity();
    $data = post('data');

    if ($data && save($entity['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data) {
        // Initial create action call
        $data = skeleton($entity['id'], (int) post('create'));
    }

    _action_view($entity);
    vars('entity.create', ['data' => $data, 'header' => _($entity['name'])]);
}

/**
 * Edit Action
 *
 * @return void
 */
function action_edit()
{
    $entity = _action_entity();
    $data = post('data');

    if ($data && save($entity['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data && is_array(post('edit'))) {
        // We just selected multiple items to edit on the index page
        $data = all($entity['id'], ['id' => array_keys(post('edit'))]);
    } elseif (!$data && param('id') !== null) {
        // We just clicked on an edit link, p.e. on the index page
        $data = all($entity['id'], ['id' => param('id')]);
    }

    if (!$data) {
        message(_('You did not select anything to edit'));
        redirect(allowed('index') ? '*/index' : '');
    }

    _action_view($entity);
    vars('entity.edit', ['data' => $data, 'header' => _($entity['name'])]);
}

/**
 * Delete Action
 *
 * @return void
 */
function action_delete()
{
    $entity = _action_entity();
    $data = post('edit');

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
 * @return void
 */
function action_view()
{
    $entity = _action_entity();

    if (!($item = one($entity['id'], ['id' => param('id')]))
        || !empty($entity['attr']['active']) && empty($item['active']) && !allowed('edit')
    ) {
        // Item does not exist or is inactive
        action_error();
    } elseif (!empty($entity['attr']['active']) && empty($item['active'])) {
        // Preview
        message(_('Preview'));
    }

    _action_view($entity, $item);
    vars('entity.view', ['item' => $item]);
}

/**
 * Index Action
 *
 * @return void
 */
function action_index()
{
    $entity = _action_entity();
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
    $search = post('search');

    if (!$search && param('search')) {
        $search = urldecode(param('search'));
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
    $page = max((int) param('page'), 1);
    $opts['offset'] = ($page - 1) * $opts['limit'];

    if ($page > 1) {
        $params['page'] = $page;
    }

    if (($sort = param('sort')) && !empty($attrs[$sort])) {
        $dir = param('dir') === 'desc' ? 'desc' : 'asc';
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

    _action_view($entity);
    vars('entity.' . $action, ['data' => $data, 'header' => _($entity['name']), 'attr' => $attrs]);
    vars(
        'entity.' . $action . '.pager',
        [
            'pages' => (int) ceil($size / $opts['limit']),
            'page' => $page,
            'limit' => $opts['limit'],
            'offset' => $opts['offset'],
            'size' => $size,
            'params' => $params
        ]
    );
}

/**
 * List Action
 *
 * @return void
 */
function action_list()
{
    action_index();
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
    redirect();
}

/**
 * Home Action
 *
 * @return void
 */
function action_http_index()
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
    $id = (int) param('id');

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

    $item = post('data');

    if ($item) {
        $data = [$user['id'] => array_replace($user, $item)];
        save('user', $data);
    }

    if (!$item = user()) {
        redirect();
    }

    layout_load();
    vars('user.profile', ['item' => $item]);
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

    $data = post('data');

    if ($data) {
        if (!empty($data['username'])
            && !empty($data['password'])
            && ($item = one('user', ['username' => $data['username'], 'active' => true]))
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

/**
 * Retrieve entity from request and validate entity and action
 *
 * @internal
 *
 * @return array
 */
function _action_entity(): array
{
    $entity = data('entity', request('entity'));

    // Check if action is allowed for entity
    if (!data_action(request('action'), $entity)) {
        action_error();
    }

    return $entity;
}

/**
 * Load View
 *
 * @internal
 *
 * @param array $entity
 * @param array $item
 *
 * @return void
 */
function _action_view(array $entity, array $item = null)
{
    layout_load();
    vars('head', ['title' => $item['name'] ?? _($entity['name']) . ' ' . _(ucfirst(request('action')))]);

    if ($item && !empty($item['meta']) && is_array($item['meta'])) {
        vars('head', ['meta' => $item['meta']]);
    }
}
