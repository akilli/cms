<?php
declare(strict_types=1);

namespace event\filter;

use filter\block;
use filter\email;
use filter\image;
use filter\msg;
use filter\tel;

function body(array $data): array
{
    $data['html'] = image\filter($data['html']);
    $data['html'] = block\filter($data['html']);
    $data['html'] = email\filter($data['html']);
    $data['html'] = tel\filter($data['html']);
    $data['html'] = msg\filter($data['html']);

    return $data;
}
