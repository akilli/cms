<?php
declare(strict_types = 1);

namespace request;

use app;
use session;
use DomainException;

/**
 * Request data
 *
 * @return mixed
 */
function get(string $key)
{
    if (($data = & app\registry('request')) === null) {
        $data['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
        $secure = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) === 'https' || ($_SERVER['HTTPS'] ?? null === 'on');
        $data['base'] = 'http' . ($secure ? 's' : '') . '://' . $data['host'];
        $data['url'] = app\enc(strip_tags(urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))));
        $data['full'] = $data['base'] . $data['url'];
        $data['file'] = [];
        $data['data'] = [];
        $data['param'] = [];

        if (!empty($_POST['token'])) {
            if (session\get('token') === $_POST['token']) {
                $data['file'] = !empty($_FILES['data']) && is_array($_FILES['data']) ? file($_FILES['data']) : [];
                $data['data'] = !empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
                $data['data'] = array_replace_recursive($data['data'], convert($data['file']));
                $data['param'] = !empty($_POST['param']) && is_array($_POST['param']) ? $_POST['param'] : [];
            }

            session\set('token', null);
        }

        $data['param'] = filter($data['param'] + $_GET);
    }

    return $data[$key] ?? null;
}

/**
 * Redirect
 */
function redirect(string $url = '/', int $code = null): void
{
    if ($code && in_array($code, APP['redirect'])) {
        header('Location: ' . $url, true, $code);
    } else {
        header('Location: ' . $url);
    }

    exit;
}

/**
 * Filters request parameters
 */
function filter(array $data): array
{
    foreach ($data as $key => $val) {
        $val = filter_var($val, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES);

        if (is_numeric($val)) {
            $data[$key] += 0;
        } elseif (!is_string($val) || !($data[$key] = trim(preg_replace('#[^\w -]#u', '', $val)))) {
            unset($data[$key]);
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
