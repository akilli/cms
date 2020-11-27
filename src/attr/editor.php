<?php
declare(strict_types=1);

namespace attr\editor;

function validator(string $val): string
{
    return trim(preg_replace('#<p>\s*</p>#', '', strip_tags($val, APP['html.tags'])));
}

function viewer(string $val): string
{
    return $val;
}
