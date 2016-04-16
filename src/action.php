<?php
namespace akilli;

/**
 * Create Action
 *
 * @return void
 */
function action_create()
{
    $meta = action_internal_meta();
    $data = post('data');

    if ($data && model_save($meta['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data) {
        // Initial create action call
        $data = meta_skeleton($meta['id'], (int) post('create'));
    }

    action_internal_view($meta);
    vars('entity.create', ['data' => $data, 'header' => _($meta['name'])]);
}

/**
 * Edit Action
 *
 * @return void
 */
function action_edit()
{
    $meta = action_internal_meta();
    $data = post('data');

    if ($data && model_save($meta['id'], $data)) {
        // Perform save callback and redirect to index on success
        redirect(allowed('index') ? '*/index' : '');
    } elseif (!$data && is_array(post('edit'))) {
        // We just selected multiple items to edit on the index page
        $data = model_load($meta['id'], ['id' => array_keys(post('edit'))]);
    } elseif (!$data && param('id') !== null) {
        // We just clicked on an edit link, p.e. on the index page
        $data = model_load($meta['id'], ['id' => param('id')]);
    }

    if (!$data) {
        message(_('You did not select anything to edit'));
        redirect(allowed('index') ? '*/index' : '');
    }

    action_internal_view($meta);
    vars('entity.edit', ['data' => $data, 'header' => _($meta['name'])]);
}

/**
 * Delete Action
 *
 * @return void
 */
function action_delete()
{
    $meta = action_internal_meta();
    $data = post('data');

    if ($data) {
        model_delete($meta['id'], ['id' => array_keys($data)]);
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
    $meta = action_internal_meta();

    if (!($item = model_load($meta['id'], ['id' => param('id')], false))
        || !empty($meta['attributes']['is_active']) && empty($item['is_active']) && !allowed('edit')
    ) {
        // Item does not exist or is inactive
        action_error();
    } elseif (!empty($meta['attributes']['is_active']) && empty($item['is_active'])) {
        // Preview
        message(_('Preview'));
    }

    action_internal_view($meta, $item);
    vars('entity.view', ['item' => $item]);
}

/**
 * Index Action
 *
 * @return void
 */
function action_index()
{
    $meta = action_internal_meta();
    $action = request('action');
    $attrs = array_filter(
        $meta['attributes'],
        function ($attr) use ($action) {
            return meta_action($action, $attr);
        }
    );
    $criteria = empty($meta['attributes']['is_active']) || $action === 'index' ? [] : ['is_active' => true];
    $order = null;
    $params = [];
    $search = post('search');

    if (!$search && param('search')) {
        $search = urldecode(param('search'));
    }

    if ($search) {
        $content = array_filter(explode(' ', $search));

        if ($content && ($items = model_load($meta['id'], ['name' => $content], 'search'))) {
            $criteria['id'] = array_keys($items);
            $params['search'] = urlencode(implode(' ', $content));
        } else {
            message(_('No results for provided search terms %s', $search));
        }
    }

    $size = model_size($meta['id'], $criteria);
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

    $data = model_load($meta['id'], $criteria, null, $order, [$limit, $offset]);
    array_walk(
        $attrs,
        function (& $attr, $code) use ($params) {
            $dir = !empty($params['sort']) && $params['sort'] === $code && $params['dir'] === 'asc' ? 'desc' : 'asc';
            $attr['url'] = url('*/*', array_replace($params, ['sort' => $code, 'dir' => $dir]));
        }
    );
    unset($params['page']);

    action_internal_view($meta);
    vars('entity.' . $action, ['data' => $data, 'header' => _($meta['name']), 'attributes' => $attrs]);
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
 * Denied Action
 *
 * @return void
 */
function action_denied()
{
    message(_('Access denied'));
    redirect('account/login');
}

/**
 * Dashboard Action
 *
 * @return void
 */
function action_account_dashboard()
{
    layout_load();
    vars('head', ['title' => _('Dashboard')]);
}

/**
 * Profile Action
 *
 * @return void
 */
function action_account_profile()
{
    $account = account();

    if (!$account || !registered()) {
        redirect();
    }

    $item = post('data');

    if ($item) {
        $data = [$account['id'] => array_replace($account, $item)];
        model_save('account', $data);
    }

    if (!$item = account()) {
        redirect();
    }

    layout_load();
    vars('account.profile', ['item' => $item]);
    vars('head', ['title' => _('Account Profile')]);
}

/**
 * Login Action
 *
 * @return void
 */
function action_account_login()
{
    if (registered()) {
        redirect('*/dashboard');
    }

    $data = post('data');

    if ($data) {
        if (!empty($data['name'])
            && !empty($data['password'])
            && ($item = model_load('account', ['name' => $data['name'], 'is_active' => true], false))
            && password_verify($data['password'], $item['password'])
        ) {
            message(_('Welcome %s', $item['name']));
            session_regenerate_id(true);
            session('account', $item['id']);
            redirect('*/dashboard');
        }

        message(_('Invalid name and password combination'));
    }

    layout_load();
    vars('head', ['title' => _('Account Login')]);
}

/**
 * Logout Action
 *
 * @return void
 */
function action_account_logout()
{
    session_regenerate_id(true);
    session_destroy();
    redirect();
}

/**
 * Index Action
 *
 * @return void
 */
function action_http_index()
{
    layout_load();
}

/**
 * Retrieve entity from request and validate metadata and action
 *
 * @return array
 */
function action_internal_meta(): array
{
    $meta = data('meta', request('entity'));

    // Check if action is allowed for entity
    if (!meta_action(request('action'), $meta)) {
        action_error();
    }

    return $meta;
}

/**
 * Load View
 *
 * @param array $meta
 * @param array $item
 *
 * @return void
 */
function action_internal_view(array $meta, array $item = null)
{
    layout_load();
    vars('head', ['title' => $item['name'] ?? _($meta['name']) . ' ' . _(ucfirst(request('action')))]);

    if ($item && !empty($item['meta']) && is_array($item['meta'])) {
        vars('head', ['meta' => $item['meta']]);
    }
}
