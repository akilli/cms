<?php
declare(strict_types = 1);

namespace http;

use app;
use session;
use DomainException;

/**
 * Request
 *
 * @return mixed
 */
function req(string $key)
{
    if (($data = & app\reg('req')) === null) {
        $data['file'] = [];
        $data['data'] = [];
        $data['param'] = [];

        if (!empty($_POST['token'])) {
            if (session\get('token') === $_POST['token']) {
                $data['file'] = !empty($_FILES['data']) && is_array($_FILES['data']) ? file($_FILES['data']) : [];
                $data['data'] = !empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
                $data['data'] = array_replace_recursive($data['data'], data($data['file']));
                $data['param'] = !empty($_POST['param']) && is_array($_POST['param']) ? $_POST['param'] : [];
            }

            session\set('token', null);
        }

        $data['param'] = param($data['param'] + $_GET);
        $data['url'] = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    return $data[$key] ?? null;
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
