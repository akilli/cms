<?php
declare(strict_types=1);

namespace event;

use contentfilter;

/**
 * Layout postrender
 */
function layout_postrender(array $data): array
{
    $data['html'] = contentfilter\block($data['html']);
    $data['html'] = contentfilter\email($data['html']);
    $data['html'] = contentfilter\tel($data['html']);
    $data['html'] = contentfilter\msg($data['html']);

    if ($data['image']) {
        $data['html'] = contentfilter\image($data['html'], $data['image']);
    }

    return $data;
}
