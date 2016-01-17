<?php
namespace action;

use account;
use app;
use config;
use data;
use http;
use i18n;
use metadata;
use model;
use role;
use session;
use url;
use view;

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
        if (model\save($metadata['id'], $data)) {
            http\redirect(role\allowed('index') ? '*/index' : '');
        }
    } else {
        // Initial create action call
        $data = metadata\skeleton($metadata['id'], (int) http\post_data('create', 'number'));
    }

    // View
    view($metadata);
    view\vars('entity.create', ['data' => $data, 'header' => i18n\translate($metadata['name'])]);
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
        if (model\save($metadata['id'], $data)) {
            http\redirect(role\allowed('index') ? '*/index' : '');
        }
    } elseif (($id = http\get('id')) !== null) {
        // We just clicked on an edit link, p.e. on the index page
        $data = model\load($metadata['id'], ['id' => $id]);
    } elseif (($data = http\post_data('edit'))) {
        // We just selected multiple items to edit on the index page
        $data = model\load($metadata['id'], ['id' => array_keys($data)]);
    }

    // If $data is empty, there haven't been any matching records to edit
    if (empty($data)) {
        session\message(i18n\translate('You did not select anything to edit'));
        http\redirect(role\allowed('index') ? '*/index' : '');
    }

    // View
    view($metadata);
    view\vars('entity.edit', ['data' => $data, 'header' => i18n\translate($metadata['name'])]);
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
        delete($metadata['id'], ['id' => array_keys($data)]);
    } else {
        // No data posted
        session\message(i18n\translate('You did not select anything to delete'));
    }

    http\redirect(role\allowed('index') ? '*/index' : '');
}

/**
 * View Action
 *
 * @return void
 */
function view_action()
{
    $metadata = meta();

    if (!($item = model\load($metadata['id'], ['id' => http\get('id')], false))
        || !empty($metadata['attributes']['is_active']) && empty($item['is_active']) && !role\allowed('edit')
    ) {
        // Item does not exist or is inactive
        error_action();
    } elseif (!empty($metadata['attributes']['is_active']) && empty($item['is_active'])) {
        // Preview
        session\message(i18n\translate('Preview'));
    }

    // View
    view($metadata, $item);
    view\vars('entity.view', ['item' => $item]);
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
    session\message(i18n\translate('The page %s does not exist', http\request('path')));
    http\redirect();
}

/**
 * Denied Action
 *
 * @return void
 */
function denied_action()
{
    session\message(i18n\translate('Access denied'));
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
            return metadata\action($action, $attribute);
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
            && $searchItems = model\load('search', ['entity_id' => $metadata['id'], 'content' => $content], 'search')
        ) {
            $ids = [];

            foreach ($searchItems as $item) {
                $ids[] = $item['content_id'];
            }

            $criteria['id'] = $ids;
            $params['terms'] = urlencode(implode(' ', $content));
        } else {
            session\message(i18n\translate('No results for provided search terms %s', implode(', ', $content)));
        }
    }

    $size = model\size($metadata['id'], $criteria);
    $limit = (int) config\value('limit.' . $action);
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

    $data = model\load($metadata['id'], $criteria, null, $order, [$limit, $offset]);
    array_walk(
        $attributes,
        function (& $attribute, $code) use ($params) {
            if (!empty($params['sort']) && $params['sort'] === $code && $params['direction'] === 'asc') {
                $direction = 'desc';
            } else {
                $direction = 'asc';
            }

            $attribute['url'] = url\path(
                '*/*',
                array_replace($params, ['sort' => $code, 'direction' => $direction])
            );
        }
    );
    unset($params['page']);

    // View
    view($metadata);
    view\vars(
        'entity.' . $action,
        ['data' => $data, 'header' => i18n\translate($metadata['name']), 'attributes' => $attributes]
    );
    view\vars(
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
    $metadata = app\data('metadata', http\request('entity'));

    // Check if action is allowed for entity
    if (!metadata\action(http\request('action'), $metadata)) {
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
    $title = ($item ? $item['name'] : i18n\translate($metadata['name']))
        . ' ' . config\value('meta.separator')
        . ' ' . config\value('meta.title');
    view\load();
    view\vars('title', ['title' => $title]);

    if ($item && !empty($item['meta']) && is_array($item['meta'])) {
        view\vars('meta', $item['meta']);
    }
}

/**
 * Dashboard Action
 *
 * @return void
 */
function account_dashboard_action()
{
    view\load();
    view\vars('title', ['title' => i18n\translate('Dashboard')]);
}

/**
 * Profile Action
 *
 * @return void
 */
function account_profile_action()
{
    $account = account\user();

    if (!$account || !account\registered()) {
        http\redirect();
    }

    $item = http\post_data('save');

    if ($item) {
        $data = [$account['id'] => array_replace($account, $item)];
        model\save('account', $data);
    }

    if (!$item = account\user()) {
        http\redirect();
    }

    // View
    view\load();
    view\vars('account.dashboard', ['item' => $item]);
}

/**
 * Login Action
 *
 * @return void
 */
function account_login_action()
{
    if (account\registered()) {
        http\redirect('*/dashboard');
    }

    $data = http\post_data('login');

    if ($data) {
        if (!empty($data['name'])
            && !empty($data['password'])
            && ($item = model\load('account', ['name' => $data['name'], 'is_active' => true], false))
            && password_verify($data['password'], $item['password'])
        ) {
            session\message(i18n\translate('Welcome %s', $item['name']));
            session_regenerate_id(true);
            session\data('account', $item['id']);
            http\redirect('*/dashboard');
        }

        session\message(i18n\translate('Invalid name and password combination'));
    }

    view\load();
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
    view\load();
}
