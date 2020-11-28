<?php
declare(strict_types=1);

namespace event;

use contentfilter\block;
use contentfilter\email;
use contentfilter\image;
use contentfilter\msg;
use contentfilter\tel;

/**
 * Layout postrender
 */
function layout_postrender(array $data): array
{
    $data['html'] = block\filter($data['html']);
    $data['html'] = email\filter($data['html']);
    $data['html'] = tel\filter($data['html']);
    $data['html'] = msg\filter($data['html']);

    if ($data['image']) {
        $data['html'] = image\filter($data['html'], $data['image']);
    }

    return $data;
}
