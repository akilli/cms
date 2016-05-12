<?php
namespace qnd;

/**
 * Create Action
 *
 * @return void
 */
function action_create()
{
    $entity = action_internal_entity();
    $data = post('data');

    if ($data && entity_save($entity['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data) {
        // Initial create action call
        $data = skeleton($entity['id'], (int) post('create'));
    }

    action_internal_view($entity);
    vars('entity.create', ['data' => $data, 'header' => _($entity['name'])]);
}

/**
 * Edit Action
 *
 * @return void
 */
function action_edit()
{
    $entity = action_internal_entity();
    $data = post('data');

    if ($data && entity_save($entity['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data && is_array(post('edit'))) {
        // We just selected multiple items to edit on the index page
        $data = entity_load($entity['id'], ['id' => array_keys(post('edit'))]);
    } elseif (!$data && param('id') !== null) {
        // We just clicked on an edit link, p.e. on the index page
        $data = entity_load($entity['id'], ['id' => param('id')]);
    }

    if (!$data) {
        message(_('You did not select anything to edit'));
        redirect(allowed('index') ? '*/index' : '');
    }

    action_internal_view($entity);
    vars('entity.edit', ['data' => $data, 'header' => _($entity['name'])]);
}

/**
 * Delete Action
 *
 * @return void
 */
function action_delete()
{
    $entity = action_internal_entity();
    $data = post('edit');

    if ($data) {
        entity_delete($entity['id'], ['id' => array_keys($data)]);
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
    $entity = action_internal_entity();

    if (!($item = entity_load($entity['id'], ['id' => param('id')], false))
        || !empty($entity['attributes']['active']) && empty($item['active']) && !allowed('edit')
    ) {
        // Item does not exist or is inactive
        action_error();
    } elseif (!empty($entity['attributes']['active']) && empty($item['active'])) {
        // Preview
        message(_('Preview'));
    }

    action_internal_view($entity, $item);
    vars('entity.view', ['item' => $item]);
}

/**
 * Index Action
 *
 * @return void
 */
function action_index()
{
    $entity = action_internal_entity();
    $action = request('action');
    $attrs = array_filter(
        $entity['attributes'],
        function ($attr) use ($action) {
            return data_action($action, $attr);
        }
    );
    $criteria = empty($entity['attributes']['active']) || $action === 'index' ? [] : ['active' => true];
    $order = [];
    $params = [];
    $search = post('search');

    if (!$search && param('search')) {
        $search = urldecode(param('search'));
    }

    if ($search) {
        $content = array_filter(explode(' ', $search));

        if ($content && ($items = entity_load($entity['id'], ['name' => $content], 'search'))) {
            $criteria['id'] = array_keys($items);
            $params['search'] = urlencode(implode(' ', $content));
        } else {
            message(_('No results for provided search terms %s', $search));
        }
    }

    $size = entity_size($entity['id'], $criteria);
    $limit = (int) config('limit.' . $action);
    $page = max((int) param('page'), 1);
    $offset = ($page - 1) * $limit;
    $pages = (int) ceil($size / $limit);

    if ($page > 1) {
        $params['page'] = $page;
    }

    if (($sort = param('sort')) && !empty($attrs[$sort])) {
        $direction = param('dir') === 'desc' ? 'desc' : 'asc';
        $order = [$sort => $direction];
        $params['sort'] = $sort;
        $params['dir'] = $direction;
    }

    $data = entity_load($entity['id'], $criteria, null, $order, [$limit, $offset]);
    array_walk(
        $attrs,
        function (& $attr, $code) use ($params) {
            $dir = !empty($params['sort']) && $params['sort'] === $code && $params['dir'] === 'asc' ? 'desc' : 'asc';
            $attr['url'] = url('*/*', array_replace($params, ['sort' => $code, 'dir' => $dir]));
        }
    );
    unset($params['page']);

    action_internal_view($entity);
    vars('entity.' . $action, ['data' => $data, 'header' => _($entity['name']), 'attributes' => $attrs]);
    vars(
        'entity.' . $action . '.pager',
        [
            'pages' => $pages,
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset,
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

    if (entity_size('project', ['id' => $id])) {
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
        entity_save('user', $data);
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
            && ($item = entity_load('user', ['username' => $data['username'], 'active' => true], false))
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
 * @return array
 */
function action_internal_entity(): array
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
 * @param array $entity
 * @param array $item
 *
 * @return void
 */
function action_internal_view(array $entity, array $item = null)
{
    layout_load();
    vars('head', ['title' => $item['name'] ?? _($entity['name']) . ' ' . _(ucfirst(request('action')))]);

    if ($item && !empty($item['meta']) && is_array($item['meta'])) {
        vars('head', ['meta' => $item['meta']]);
    }
}
