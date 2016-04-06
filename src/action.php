<?php
namespace action;

use akilli;
use data;
use http;

/**
 * Create Action
 *
 * @return void
 */
function create_action()
{
    $metadata = meta();
    $data = http\post_data('save');

    if ($data) {
        // Perform create callback
        if (akilli\model_save($metadata['id'], $data)) {
            http\redirect(akilli\allowed('index') ? '*/index' : '');
        }
    } else {
        // Initial create action call
        $data = akilli\metadata_skeleton($metadata['id'], (int) http\post_data('create', 'number'));
    }

    // View
    view($metadata);
    akilli\view_vars('entity.create', ['data' => $data, 'header' => akilli\_($metadata['name'])]);
}

/**
 * Edit Action
 *
 * @return void
 */
function edit_action()
{
    $metadata = meta();
    $data = http\post_data('save');

    if ($data) {
        // Perform save callback and redirect to index on success
        if (akilli\model_save($metadata['id'], $data)) {
            http\redirect(akilli\allowed('index') ? '*/index' : '');
        }
    } elseif (($id = http\get('id')) !== null) {
        // We just clicked on an edit link, p.e. on the index page
        $data = akilli\model_load($metadata['id'], ['id' => $id]);
    } elseif (($data = http\post_data('edit'))) {
        // We just selected multiple items to edit on the index page
        $data = akilli\model_load($metadata['id'], ['id' => array_keys($data)]);
    }

    // If $data is empty, there haven't been any matching records to edit
    if (empty($data)) {
        akilli\message(akilli\_('You did not select anything to edit'));
        http\redirect(akilli\allowed('index') ? '*/index' : '');
    }

    // View
    view($metadata);
    akilli\view_vars('entity.edit', ['data' => $data, 'header' => akilli\_($metadata['name'])]);
}

/**
 * Delete Action
 *
 * @return void
 */
function delete_action()
{
    $metadata = meta();
    $data = http\post_data('delete');

    if ($data) {
        // Data posted, perform delete callback
        akilli\model_delete($metadata['id'], ['id' => array_keys($data)]);
    } else {
        // No data posted
        akilli\message(akilli\_('You did not select anything to delete'));
    }

    http\redirect(akilli\allowed('index') ? '*/index' : '');
}

/**
 * View Action
 *
 * @return void
 */
function view_action()
{
    $metadata = meta();

    if (!($item = akilli\model_load($metadata['id'], ['id' => http\get('id')], false))
        || !empty($metadata['attributes']['is_active']) && empty($item['is_active']) && !akilli\allowed('edit')
    ) {
        // Item does not exist or is inactive
        error_action();
    } elseif (!empty($metadata['attributes']['is_active']) && empty($item['is_active'])) {
        // Preview
        akilli\message(akilli\_('Preview'));
    }

    // View
    view($metadata, $item);
    akilli\view_vars('entity.view', ['item' => $item]);
}

/**
 * Index Action
 *
 * @return void
 */
function index_action()
{
    index();
}

/**
 * List Action
 *
 * @return void
 */
function list_action()
{
    index();
}

/**
 * Error Action
 *
 * @return void
 */
function error_action()
{
    akilli\message(akilli\_('The page %s does not exist', http\request('path')));
    http\redirect();
}

/**
 * Denied Action
 *
 * @return void
 */
function denied_action()
{
    akilli\message(akilli\_('Access denied'));
    http\redirect('account/login');
}

/**
 * Internal index action
 *
 * @return void
 */
function index()
{
    $metadata = meta();
    $action = http\request('action');
    $attributes = array_filter(
        $metadata['attributes'],
        function ($attribute) use ($action) {
            return akilli\metadata_action($action, $attribute);
        }
    );
    $criteria = empty($metadata['attributes']['is_active']) || $action === 'index' ? [] : ['is_active' => true];
    $order = null;
    $params = [];

    // Search
    if (($terms = http\post_data('search', 'terms'))
        || ($terms = http\get('terms')) && ($terms = urldecode($terms))
    ) {
        if (($content = array_filter(explode(' ', $terms)))
            && $searchItems = akilli\model_load('search', ['entity_id' => $metadata['id'], 'content' => $content], 'search')
        ) {
            $ids = [];

            foreach ($searchItems as $item) {
                $ids[] = $item['content_id'];
            }

            $criteria['id'] = $ids;
            $params['terms'] = urlencode(implode(' ', $content));
        } else {
            akilli\message(akilli\_('No results for provided search terms %s', implode(', ', $content)));
        }
    }

    $size = akilli\model_size($metadata['id'], $criteria);
    $limit = (int) akilli\config('limit.' . $action);
    $page = max((int) http\get('page'), 1);
    $offset = ($page - 1) * $limit;
    $pages = (int) ceil($size / $limit);

    if ($page > 1) {
        $params['page'] = $page;
    }

    // Order
    if (($sort = http\get('sort')) && !empty($attributes[$sort])) {
        $direction = http\get('direction') === 'desc' ? 'desc' : 'asc';
        $order = [$sort => $direction];
        $params['sort'] = $sort;
        $params['direction'] = $direction;
    }

    $data = akilli\model_load($metadata['id'], $criteria, null, $order, [$limit, $offset]);
    array_walk(
        $attributes,
        function (& $attribute, $code) use ($params) {
            if (!empty($params['sort']) && $params['sort'] === $code && $params['direction'] === 'asc') {
                $direction = 'desc';
            } else {
                $direction = 'asc';
            }

            $attribute['url'] = akilli\url(
                '*/*',
                array_replace($params, ['sort' => $code, 'direction' => $direction])
            );
        }
    );
    unset($params['page']);

    // View
    view($metadata);
    akilli\view_vars(
        'entity.' . $action,
        ['data' => $data, 'header' => akilli\_($metadata['name']), 'attributes' => $attributes]
    );
    akilli\view_vars(
        'entity.' . $action . '.pager',
        [
            'pages' => $pages,
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset,
            'size' => $size,
            'params' => $params]
    );
}

/**
 * Retrieve entity from request and validate metadata and action
 *
 * @return array
 */
function meta(): array
{
    $metadata = akilli\data('metadata', http\request('entity'));

    // Check if action is allowed for entity
    if (!akilli\metadata_action(http\request('action'), $metadata)) {
        error_action();
    }

    return $metadata;
}

/**
 * Load View
 *
 * @param array $metadata
 * @param array $item
 *
 * @return void
 */
function view(array $metadata, array $item = null)
{
    $title = ($item ? $item['name'] : akilli\_($metadata['name']))
        . ' ' . akilli\config('meta.separator')
        . ' ' . akilli\config('meta.title');
    akilli\view_load();
    akilli\view_vars('title', ['title' => $title]);

    if ($item && !empty($item['meta']) && is_array($item['meta'])) {
        akilli\view_vars('meta', $item['meta']);
    }
}

/**
 * Dashboard Action
 *
 * @return void
 */
function account_dashboard_action()
{
    akilli\view_load();
    akilli\view_vars('title', ['title' => akilli\_('Dashboard')]);
}

/**
 * Profile Action
 *
 * @return void
 */
function account_profile_action()
{
    $account = akilli\account();

    if (!$account || !akilli\registered()) {
        http\redirect();
    }

    $item = http\post_data('save');

    if ($item) {
        $data = [$account['id'] => array_replace($account, $item)];
        akilli\model_save('account', $data);
    }

    if (!$item = akilli\account()) {
        http\redirect();
    }

    // View
    akilli\view_load();
    akilli\view_vars('account.profile', ['item' => $item]);
}

/**
 * Login Action
 *
 * @return void
 */
function account_login_action()
{
    if (akilli\registered()) {
        http\redirect('*/dashboard');
    }

    $data = http\post_data('login');

    if ($data) {
        if (!empty($data['name'])
            && !empty($data['password'])
            && ($item = akilli\model_load('account', ['name' => $data['name'], 'is_active' => true], false))
            && password_verify($data['password'], $item['password'])
        ) {
            akilli\message(akilli\_('Welcome %s', $item['name']));
            session_regenerate_id(true);
            akilli\session('account', $item['id']);
            http\redirect('*/dashboard');
        }

        akilli\message(akilli\_('Invalid name and password combination'));
    }

    akilli\view_load();
}

/**
 * Logout Action
 *
 * @return void
 */
function account_logout_action()
{
    session_regenerate_id(true);
    session_destroy();
    http\redirect();
}

/**
 * Index Action
 *
 * @return void
 */
function http_index_action()
{
    akilli\view_load();
}
