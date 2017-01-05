<?php
namespace qnd;

/**
 * Session data
 *
 * @param string $key
 * @param mixed $value
 * @param bool $reset
 *
 * @return mixed
 */
function & session(string $key, $value = null, bool $reset = false)
{
    static $data;

    if ($data === null) {
        // Start session if it was not started before
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $data = & $_SESSION;
    }

    // If $value is provided, set $value for $key
    if ($value !== null) {
        $data[$key] = $value;
    }

    // Reset value or set $key in first call, so we have something to return
    if (isset($data[$key]) && $reset || !isset($data[$key])) {
        $data[$key] = null;
    }

    return $data[$key];
}

/**
 * Add message
 *
 * @param string $message
 *
 * @return void
 */
function message(string $message): void
{
    $data = & session('message');

    if ($message && (!$data || !in_array($message, $data))) {
        $data[] = $message;
    }
}

/**
 * Token
 *
 * @return string
 */
function token(): string
{
    $token = & session('token');

    if (empty($token)) {
        $token = md5(uniqid(mt_rand(), true));
    }

    return $token;
}

/**
 * Redirect
 *
 * @param string $url
 * @param int $code
 *
 * @return void
 */
function redirect(string $url = '/', int $code = 302): void
{
    if ($code < 300 && $code > 308) {
        $code = 302;
    }

    header('Location: ' . $url, true, $code);
    exit;
}

/**
 * Request
 *
 * @param string $key
 *
 * @return mixed
 */
function request(string $key)
{
    return data('request', $key);
}

/**
 * Get
 *
 * @param string $key
 *
 * @return mixed
 */
function http_get(string $key)
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
function http_post(string $key)
{
    return request('post')[$key] ?? null;
}

/**
 * Files
 *
 * @param string $key
 *
 * @return mixed
 */
function http_files(string $key)
{
    return request('files')[$key] ?? null;
}

/**
 * Filters request variables
 *
 * @param array $data
 *
 * @return array
 */
function http_filter(array $data): array
{
    foreach ($data as $key => $value) {
        if (!$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR)) {
            unset($data[$key]);
            continue;
        }

        $data[$key] = is_numeric($value) ? (int) $value : $value;
    }

    return $data;
}

/**
 * Post validation
 *
 * @param string $token
 *
 * @return bool
 */
function http_post_validate(string $token): bool
{
    $session = session('token');
    $success = !empty($session) && !empty($token) && $session === $token;
    session('token', null, true);

    return $success;
}

/**
 * Converts and validates uploaded files
 *
 * @param array $data
 *
 * @return array
 */
function http_files_convert(array $data): array
{
    $files = [];

    foreach ($data as $id => $item) {
        $files[$id] = is_array($item) ? http_files_fix($item) : $item;
    }

    $keys = ['error', 'name', 'size', 'tmp_name', 'type'];
    $exts = data('ext', 'file');

    foreach ($files as $id => $item) {
        if (!is_array($item)) {
            continue;
        }

        $ids = array_keys($item);
        sort($ids);

        if ($ids != $keys) {
            $files[$id] = http_files_convert($item);
        } elseif ($item['error'] === UPLOAD_ERR_NO_FILE || !is_uploaded_file($item['tmp_name'])) {
            unset($files[$id]);
        } else {
            $files[$id]['ext'] = pathinfo($item['name'], PATHINFO_EXTENSION);

            if (!in_array($files[$id]['ext'], $exts)) {
                message(_('Invalid file %s', $item['name']));
                unset($files[$id]);
            }
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
function http_files_fix(array $data): array
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
        $files[$id] = http_files_fix(
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
