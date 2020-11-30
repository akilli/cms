<?php
declare(strict_types=1);

namespace event\filter;

use filter\block;
use filter\email;
use filter\image;
use filter\msg;
use filter\tel;

function all(array $data): array
{
    if ($data['image']) {
        $data['html'] = image\filter($data['html'], $data['image']);
    }

    return $data;
}

function body(array $data): array
{
    $data['html'] = block\filter($data['html']);
    $data['html'] = email\filter($data['html']);
    $data['html'] = tel\filter($data['html']);
    $data['html'] = msg\filter($data['html']);

    return $data;
}
