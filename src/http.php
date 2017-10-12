<?php
declare(strict_types = 1);

namespace http;

use function app\i18n;
use app;
use filter;
use session;
use RuntimeException;

const UPLOAD = ['error', 'name', 'size', 'tmp_name', 'type'];

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
function req(string $key)
{
    $req = & app\data('req');

    if ($req === null) {
        $req['file'] = [];
        $req['data'] = [];
        $req['param'] = [];

        if (!empty($_POST['token'])) {
            if (session\get('token') === $_POST['token']) {
                $req['file'] = !empty($_FILES['data']) && is_array($_FILES['data']) ? file($_FILES['data']) : [];
                $req['data'] = !empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
                $req['data'] = array_replace_recursive($req['data'], data($req['file']));
                $req['param'] = !empty($_POST['param']) && is_array($_POST['param']) ? $_POST['param'] : [];
            }

            session\set('token', null);
        }

        $req['param'] = param($req['param'] + $_GET);
        $req['url'] = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $parts = explode('/', trim(app\rewrite($req['url']), '/'));
        $req['ent'] = $parts[0];
        $req['act'] = $parts[1] ?? null;
        $req['id'] = $parts[2] ?? null;
        $req['path'] = $req['ent'] . '/' . $req['act'];
    }

    return $req[$key] ?? null;
}

/**
 * Filters request parameters
 */
function param(array $data): array
{
    foreach ($data as $key => $val) {
        $val = filter_var($val, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES);

        if ($val === false) {
            $data[$key] = null;
        } else {
            $data[$key] = is_numeric($val) ? (int) $val : filter\param($val);
        }
    }

    return $data;
}

/**
 * Filters request data
 *
 * @throws RuntimeException
 */
function data(array $in): array
{
    $out = [];

    foreach ($in as $k => $v) {
        if (!is_array($v)) {
            throw new RuntimeException(i18n('Invalid data'));
        }

        $out[$k] = ($keys = array_keys($v)) && sort($keys) && $keys === UPLOAD ? $v['name'] : data($v);
    }

    return $out;
}

/**
 * Filters file uploads
 *
 * @throws RuntimeException
 */
function file(array $in): array
{
    if (!($keys = array_keys($in)) || !sort($keys) || $keys !== UPLOAD || !is_array($in['name'])) {
        throw new RuntimeException(i18n('Invalid data'));
    }

    $exts = app\cfg('file');
    $out = [];

    foreach (array_filter($in['name']) as $k => $n) {
        $e = $in['error'][$k];
        $t = $in['tmp_name'][$k];
        $f = ['error' => $e, 'name' => $n, 'size' => $in['size'][$k], 'tmp_name' => $t, 'type' => $in['type'][$k]];

        if (is_array($n)) {
            $f = file($f);
        } elseif ($e !== UPLOAD_ERR_OK || !is_uploaded_file($t) || empty($exts[pathinfo($n, PATHINFO_EXTENSION)])) {
            app\msg(i18n('Invalid file %s', $n));
            continue;
        }

        if ($f) {
            $out[$k] = $f;
        }
    }

    return $out;
}
