<?php
declare(strict_types = 1);

namespace cms;

/**
 * Session data getter
 *
 * @return mixed
 */
function session_get(string $key)
{
    session_init();

    return $_SESSION[$key] ?? null;
}

/**
 * Session data (un)setter
 */
function session_set(string $key, $val): void
{
    session_init();

    if ($val === null) {
        unset($_SESSION[$key]);
    } else {
        $_SESSION[$key] = $val;
    }
}

/**
 * Initializes session
 */
function session_init(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        session_start();

        if (!empty($_SESSION['_deleted']) && $_SESSION['_deleted'] < time() - 180) {
            session_destroy();
            session_start();
        }
    }
}

/**
 * Regenerates session ID
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
 */
function message(string $message): void
{
    $data = session_get('message') ?? [];

    if ($message && !in_array($message, $data)) {
        $data[] = $message;
        session_set('message', $data);
    }
}

/**
 * Token
 */
function token(): string
{
    if (!$token = session_get('token')) {
        $token = md5(uniqid((string) mt_rand(), true));
        session_set('token', $token);
    }

    return $token;
}

/**
 * Redirect
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
 * @return mixed
 */
function request(string $key)
{
    $req = & registry('request');

    if ($req === null) {
        $req['host'] = $_SERVER['HTTP_HOST'];
        $req['data'] = [];
        $param = [];

        if (!empty($_POST['token'])) {
            if (session_get('token') === $_POST['token']) {
                $req['data'] = !empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
                $param = !empty($_POST['param']) && is_array($_POST['param']) ? $_POST['param'] : [];
            }

            session_set('token', null);
        }

        $req['param'] = http_filter($param + $_GET);
        $req['file'] = !empty($_FILES['data']) ? http_file($_FILES['data']) : [];
        $req['url'] = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $parts = explode('/', trim(url_rewrite($req['url']), '/'));
        $req['entity'] = $parts[0];
        $req['action'] = $parts[1] ?? null;
        $req['id'] = $parts[2] ?? null;
        $req['path'] = $req['entity'] . '/' . $req['action'];
    }

    return $req[$key] ?? null;
}

/**
 * Resolves wildcards, i.e. asterisks, for entity and action part with appropriate values from current request
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
 * Filters request variables
 */
function http_filter(array $data): array
{
    foreach ($data as $key => $val) {
        $val = filter_var($val, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES);

        if ($val === false) {
            $data[$key] = null;
        } else {
            $data[$key] = is_numeric($val) ? (int) $val : filter_param($val);
        }
    }

    return $data;
}

/**
 * Filters file uploads
 */
function http_file(array $data): array
{
    $keys = array_keys($data);
    sort($keys);

    if ($keys !== ['error', 'name', 'size', 'tmp_name', 'type'] || !is_array($data['name'])) {
        return $data;
    }

    $exts = cfg('file');
    $files = [];

    foreach ($data['name'] as $key => $value) {
        if (!$value) {
            continue;
        }

        $ok = $data['error'][$key] === UPLOAD_ERR_OK && is_uploaded_file($data['tmp_name'][$key]);

        if (!is_array($value) && (!$ok || empty($exts[pathinfo($data['name'][$key], PATHINFO_EXTENSION)]))) {
            message(_('Invalid file %s', $data['name'][$key]));
            continue;
        }

        $f = http_file(
            [
                'error' => $data['error'][$key],
                'name' => $data['name'][$key],
                'type' => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size' => $data['size'][$key]
            ]
        );

        if ($f) {
            $files[$key] = $f;
        }
    }

    return $files;
}
