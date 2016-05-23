<?php
namespace qnd;

/**
 * Request
 *
 * @param string $key
 *
 * @return mixed
 */
function request(string $key)
{
    $data = & registry('request');

    if ($data === null) {
        $data = [];
        $data = request_init();
        event('http.request', $data);
        $data = request_prepare($data);
    }

    return $data[$key] ?? null;
}

/**
 * Initialize request data
 *
 * @return array
 */
function request_init(): array
{
    $data = data('skeleton', 'request');
    $data['base'] = rtrim(filter_path(dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
    $data['url'] = $_SERVER['REQUEST_URI'] ?? $data['base'];
    $data['original_path'] = trim(preg_replace('#^' . $data['base'] . '#', '', explode('?', $data['url'])[0]), '/');
    $data['path'] = url_rewrite($data['original_path']);
    $data['host'] = $_SERVER['HTTP_HOST'];
    $data['scheme'] = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $data['secure'] = $data['scheme'] === 'https';
    $data['get'] = $_GET;
    $data['post'] = !empty($_POST['token']) && post_validate($_POST['token']) ? $_POST : [];
    $data['files'] = $_FILES ? files_validate(files_convert($_FILES)) : [];

    return $data;
}

/**
 * Prepare request data for current request, i.e. entity, action, id and params
 *
 * @param array $data
 *
 * @return array
 */
function request_prepare(array $data): array
{
    $parts = $data['path'] ? explode('/', $data['path']) : [];
    $entity = array_shift($parts);

    if ($entity) {
        $data['entity'] = $entity;
        $action = array_shift($parts);

        if ($action) {
            $data['action'] = $action;
            $count = count($parts);

            for ($i = 0; $i < $count; $i += 2) {
                if (!empty($parts[$i]) && isset($parts[$i + 1])) {
                    $data['params'][$parts[$i]] = $parts[$i + 1];
                }
            }
        }
    }

    $data['id'] = $data['entity'] . '.' . $data['action'];
    $data['_old'] = $data;

    return $data;
}

/**
 * Redirect
 *
 * @param string $url
 * @param array $params
 *
 * @return void
 */
function redirect(string $url = '', array $params = [])
{
    header('Location:' . url($url, $params));
    exit;
}

/**
 * Parameters
 *
 * @param string $key
 *
 * @return mixed
 */
function param(string $key = null)
{
    return request('params')[$key] ?? null;
}

/**
 * Get
 *
 * @param string $key
 *
 * @return mixed
 */
function get(string $key)
{
    return request('get')[$key] ?? null;
}

/**
 * Post
 *
 * @param string $key
 *
 * @return mixed
 */
function post(string $key)
{
    return request('post')[$key] ?? null;
}

/**
 * Post validation
 *
 * @param string $token
 *
 * @return bool
 */
function post_validate(string $token): bool
{
    $session = session('token');
    $success = !empty($session) && !empty($token) && $session === $token;
    session('token', null, true);

    return $success;
}

/**
 * Files
 *
 * @param string $key
 *
 * @return mixed
 */
function files(string $key)
{
    return request('files')[$key] ?? null;
}

/**
 * Validate uploads
 *
 * @param array $data
 *
 * @return array
 */
function files_validate(array $data): array
{
    $exts = config('ext.file');

    foreach ($data as $key => $items) {
        foreach ($items as $id => $item) {
            foreach ($item as $code => $attr) {
                if (empty($attr)) {
                    continue;
                }

                $ext = pathinfo($attr['name'], PATHINFO_EXTENSION);

                if (empty($exts[$ext])) {
                    message(_('Invalid file %s was rejected', $attr['name']));
                    unset($data[$key][$id][$code]);
                } else {
                    $data[$key][$id][$code]['extension'] = $ext;
                }
            }
        }
    }

    return $data;
}

/**
 * Converts uploaded files
 *
 * @param array $data
 *
 * @return array
 */
function files_convert(array $data): array
{
    $files = [];

    foreach ($data as $id => $item) {
        $files[$id] = is_array($item) ? files_fix($item) : $item;
    }

    $keys = ['error', 'name', 'size', 'tmp_name', 'type'];

    foreach ($files as $id => $item) {
        if (!is_array($item)) {
            continue;
        }

        $ids = array_keys($item);
        sort($ids);

        if ($ids != $keys) {
            $files[$id] = files_convert($item);
        } elseif ($item['error'] === UPLOAD_ERR_NO_FILE || !is_uploaded_file($item['tmp_name'])) {
            unset($files[$id]);
        }
    }

    return $files;
}

/**
 * Fixes a malformed PHP $_FILES array.
 *
 * PHP has a bug that the format of the $_FILES array differs, depending on whether the uploaded file fields had normal
 * field names or array-like field names ("normal" vs. "parent[child]").
 *
 * This method fixes the array to look like the "normal" $_FILES array.
 *
 * It's safe to pass an already converted array, in which case this method just returns the original array unmodified.
 *
 * @param array $data
 *
 * @return array
 */
function files_fix(array $data): array
{
    $keys = ['error', 'name', 'size', 'tmp_name', 'type'];
    $ids = array_keys($data);
    sort($ids);

    if ($keys != $ids || !isset($data['name']) || !is_array($data['name'])) {
        return $data;
    }

    $files = $data;

    foreach ($keys as $k) {
        unset($files[$k]);
    }

    foreach (array_keys($data['name']) as $id) {
        $files[$id] = files_fix(
            [
                'error' => $data['error'][$id],
                'name' => $data['name'][$id],
                'type' => $data['type'][$id],
                'tmp_name' => $data['tmp_name'][$id],
                'size' => $data['size'][$id]
            ]
        );
    }

    return $files;
}
