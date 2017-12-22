<?php
declare(strict_types = 1);

namespace http;

use app;
use session;
use DomainException;

/**
 * Redirect
 */
function redirect(string $url = '/', int $code = 302): void
{
    header('Location: ' . $url, true, in_array($code, APP['code']) ? $code : 302);
    exit;
}

/**
 * Request
 *
 * @return mixed
 */
function req(string $key)
{
    if (($req = & app\data('req')) === null) {
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
        $req['ent'] = array_shift($parts);
        $req['act'] = array_shift($parts);
        $req['id'] = $parts ? (int) array_shift($parts) : null;
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
        } elseif (is_numeric($val)) {
            $data[$key] += 0;
        } else {
            $data[$key] = preg_replace('#[^\w -_]#u', '', $val);
        }
    }

    return $data;
}

/**
 * Filters request data
 *
 * @throws DomainException
 */
function data(array $in): array
{
    $out = [];

    foreach ($in as $k => $v) {
        if (!is_array($v)) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $out[$k] = ($keys = array_keys($v)) && sort($keys) && $keys === APP['upload'] ? $v['name'] : data($v);
    }

    return $out;
}

/**
 * Filters file uploads
 *
 * @throws DomainException
 */
function file(array $in): array
{
    if (!($keys = array_keys($in)) || !sort($keys) || $keys !== APP['upload'] || !is_array($in['name'])) {
        throw new DomainException(app\i18n('Invalid data'));
    }

    $out = [];

    foreach (array_filter($in['name']) as $k => $n) {
        $e = $in['error'][$k];
        $t = $in['tmp_name'][$k];
        $f = ['error' => $e, 'name' => $n, 'size' => $in['size'][$k], 'tmp_name' => $t, 'type' => $in['type'][$k]];

        if (is_array($n)) {
            $f = file($f);
        } elseif ($e !== UPLOAD_ERR_OK || !is_uploaded_file($t)) {
            app\msg(app\i18n('Could not upload %s', $n));
            continue;
        }

        if ($f) {
            $out[$k] = $f;
        }
    }

    return $out;
}
