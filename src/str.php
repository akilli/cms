<?php
declare(strict_types=1);

namespace str;

use app;

/**
 * Converts special chars to HTML entities
 */
function enc(?string $val): string
{
    return $val === null ? '' : htmlspecialchars($val, ENT_QUOTES, double_encode: false);
}

/**
 * Converts string to HTML entity hex format
 */
function hex(string $val): string
{
    $out = '';
    $length = strlen($val);

    for ($i = 0; $i < $length; $i++) {
        $out .= $val[$i] === ' ' ? ' ' : '&#x' . bin2hex($val[$i]) . ';';
    }

    return $out;
}

/**
 * Translates configured characters
 */
function tr(string $val): string
{
    return strtr(mb_strtolower($val), app\cfg('tr'));
}

function uid(string $val): string
{
    return trim(preg_replace(['#[^a-z0-9\-]+#', '#[-]+#'], '-', tr($val)), '-');
}

/**
 * Generates a unique string
 */
function uniq(string $prefix = ''): string
{
    return $prefix . md5(uniqid((string)time(), true));
}
