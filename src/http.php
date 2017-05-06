<?php
declare(strict_types = 1);

namespace qnd;

/**
 * Session data
 *
 * @param string $key
 * @param mixed $val
 * @param bool $reset
 *
 * @return mixed
 */
function session(string $key, $val = null, bool $reset = false)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        session_start();

        if (!empty($_SESSION['_deleted']) && $_SESSION['_deleted'] < time() - 180) {
            session_destroy();
            session_start();
        }
    }

    if ($reset) {
        unset($_SESSION[$key]);
    } elseif ($val !== null) {
        $_SESSION[$key] = $val;
    }

    return $_SESSION[$key] ?? null;
}

/**
 * Regenerates session ID
 *
 * @return void
 */
function session_regenerate(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $id = session_create_id();
    $_SESSION['_deleted'] = time();
    session_commit();
    ini_set('session.use_strict_mode', '0');
    session_id($id);
    session_start();
    ini_set('session.use_strict_mode', '1');
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
    $data = session('message') ?? [];

    if ($message && !in_array($message, $data)) {
        $data[] = $message;
        session('message', $data);
    }
}

/**
 * Token
 *
 * @return string
 */
function token(): string
{
    return session('token') ?: session('token', md5(uniqid((string) mt_rand(), true)));
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
    $data = & registry('request');

    if ($data === null) {
        $data['host'] = $_SERVER['HTTP_HOST'];
        $data['get'] = http_filter($_GET);
        $data['post'] = !empty($_POST['token']) && http_post_validate($_POST['token']) ? $_POST : [];
        $data['files'] = $_FILES ? http_files_convert($_FILES) : [];
        $url = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $data['url'] = preg_replace('#^' . $_SERVER['SCRIPT_NAME'] . '#', '', $url);
        $parts = explode('/', trim(url_rewrite($data['url']), '/'));
        $data['entity'] = $parts[0];
        $data['action'] = $parts[1];
        $data['id'] = $parts[2] ?? null;
        $data['path'] = $data['entity'] . '/' . $data['action'];
    }

    return $data[$key] ?? null;
}

/**
 * Resolves wildcards, i.e. asterisks, for entity and action part with appropriate values from current request
 *
 * @param string $path
 *
 * @return string
 */
function resolve(string $path): string
{
    if (strpos($path, '*') === false) {
        return $path;
    }

    $parts = explode('/', $path);

    // Wildcard for Entity Part
    if ($parts[0] === '*') {
        $parts[0] = request('entity');
    }

    // Wildcard for Action Part
    if (!empty($parts[1]) && $parts[1] === '*') {
        $parts[1] = request('action');
    }

    return implode('/', $parts);
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
    foreach ($data as $key => $val) {
        if (!$val = filter_var($val, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR)) {
            unset($data[$key]);
            continue;
        }

        $data[$key] = is_numeric($val) ? (int) $val : $val;
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
 * @param array $files
 *
 * @return array
 */
function http_files_convert(array $files): array
{
    $keys = ['error', 'name', 'size', 'tmp_name', 'type'];
    $exts = data('file');

    foreach (array_filter($files, 'is_array') as $id => $item) {
        $item = http_files_fix($item);
        $ids = array_keys($item);
        sort($ids);

        if ($ids !== $keys) {
            $files[$id] = http_files_convert($item);
        } elseif ($item['error'] === UPLOAD_ERR_NO_FILE || !is_uploaded_file($item['tmp_name'])) {
            unset($files[$id]);
        } else {
            $files[$id] = $item + ['ext' => pathinfo($item['name'], PATHINFO_EXTENSION)];

            if (empty($exts[$files[$id]['ext']])) {
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
 * field names or array-like field names ("normal" vs. "parent[child]"). This method fixes the array to look like the
 * "normal" $_FILES array. It's safe to pass an already converted array, in which case this method just returns the
 * original array unmodified.
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

    if ($keys !== $ids || !isset($data['name']) || !is_array($data['name'])) {
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
