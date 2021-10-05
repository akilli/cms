<?php
declare(strict_types=1);

namespace response;

/**
 * Redirect
 */
function redirect(string $url = '/'): never
{
    header('location: ' . $url);
    exit;
}
