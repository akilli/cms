<?php
declare(strict_types = 1);

namespace cms;

use RuntimeException;

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
function msg(string $msg): void
{
    $data = session_get('msg') ?? [];

    if ($msg && !in_array($msg, $data)) {
        $data[] = $msg;
        session_set('msg', $data);
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
        $req['file'] = [];
        $req['data'] = [];
        $req['param'] = [];

        if (!empty($_POST['token'])) {
            if (session_get('token') === $_POST['token']) {
                $req['file'] = !empty($_FILES['data']) && is_array($_FILES['data']) ? request_file($_FILES['data']) : [];
                $req['data'] = !empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
                $req['param'] = !empty($_POST['param']) && is_array($_POST['param']) ? $_POST['param'] : [];
            }

            session_set('token', null);
        }

        $req['param'] = request_filter($req['param'] + $_GET);
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
 * Filters request variables
 */
function request_filter(array $data): array
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
 *
 * @throws RuntimeException
 */
function request_file(array $in): array
{
    if (!($keys = array_keys($in)) || !sort($keys) || $keys !== ['error', 'name', 'size', 'tmp_name', 'type']) {
        throw new RuntimeException(_('Invalid data'));
    }

    if (!is_array($in['name'])) {
        return $in;
    }

    $exts = cfg('file');
    $out = [];

    foreach (array_filter($in['name']) as $k => $n) {
        $e = $in['error'][$k];
        $t = $in['tmp_name'][$k];
        $f = ['error' => $e, 'name' => $n, 'size' => $in['size'][$k], 'tmp_name' => $t, 'type' => $in['type'][$k]];

        if (is_array($n)) {
            $f = request_file($f);
        } elseif ($e !== UPLOAD_ERR_OK || !is_uploaded_file($t) || empty($exts[pathinfo($n, PATHINFO_EXTENSION)])) {
            msg(_('Invalid file %s', $n));
            continue;
        }

        if ($f) {
            $out[$k] = $f;
        }
    }

    return $out;
}
