<?php
declare(strict_types=1);

namespace response;

function send(string $body, array $headers, int $status = null): string
{
    if ($status) {
        http_response_code($status);
    }

    foreach ($headers as $key => $val) {
        header($key . ': ' . $val);
    }

    return $body;
}

function redirect(string $url = '/'): never
{
    header('location: ' . $url);
    exit;
}
