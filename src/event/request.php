<?php
declare(strict_types=1);

namespace event\request;

use arr;
use request;
use session;
use str;

function data(array $data): array
{
    $data = arr\replace(APP['data']['request'], $data);
    $data['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
    $data['method'] = strtolower($_SERVER['REQUEST_METHOD']);
    $https = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) === 'https' || ($_SERVER['HTTPS'] ?? null) === 'on';
    $data['proto'] = $https ? 'https' : 'http';
    $data['base'] = $data['proto'] . '://' . $data['host'];
    $data['url'] = str\enc(strip_tags(urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))));
    $data['full'] = $data['base'] . rtrim($data['url'], '/');
    $data['get'] = request\filter($_GET);

    if (!empty($_POST['token'])) {
        if (session\get('token') === $_POST['token']) {
            unset($_POST['token']);
            $data['file'] = array_filter(array_map('request\normalize', $_FILES));
            $data['post'] = $_POST;
            $data['post'] = array_replace_recursive($data['post'], request\convert($data['file']));
        }

        session\delete('token');
    }

    return $data;
}
