<?php
declare(strict_types = 1);

namespace req;

use app;
use session;
use DomainException;

/**
 * Request data
 *
 * @return mixed
 */
function data(string $key)
{
    if (($data = & app\reg('req')) === null) {
        $data['host'] = $_SERVER['HTTP_HOST'];
        $data['url'] = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $data['file'] = [];
        $data['post'] = [];
        $data['get'] = [];

        if (!empty($_POST['token'])) {
            if (session\get('token') === $_POST['token']) {
                $data['file'] = !empty($_FILES['data']) && is_array($_FILES['data']) ? file($_FILES['data']) : [];
                $data['post'] = !empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
                $data['post'] = array_replace_recursive($data['post'], convert($data['file']));
                $data['get'] = !empty($_POST['param']) && is_array($_POST['param']) ? $_POST['param'] : [];
            }

            session\set('token', null);
        }

        $data['get'] = filter($data['get'] + $_GET);
    }

    return $data[$key] ?? null;
}

/**
 * Filters request parameters
 */
function filter(array $data): array
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
 * Converts file to post
 *
 * @throws DomainException
 */
function convert(array $in): array
{
    $out = [];

    foreach ($in as $k => $v) {
        if (!is_array($v)) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $out[$k] = ($keys = array_keys($v)) && sort($keys) && $keys === APP['upload'] ? $v['name'] : convert($v);
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
            app\msg('Could not upload %s', $n);
            continue;
        }

        if ($f) {
            $out[$k] = $f;
        }
    }

    return $out;
}
