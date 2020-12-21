<?php
declare(strict_types=1);

namespace attr\entitychild;

use app;
use arr;

function opt(array $data): array
{
    if ($data['_entity']['parent_id']) {
        return [$data['_entity']['id'] => $data['_entity']['name']];
    }

    if (($opt = &app\registry('opt')['entitychild'][$data['_entity']['id']]) === null) {
        $opt = array_column(arr\filter(app\cfg('entity'), 'parent_id', $data['_entity']['id']), 'name', 'id');
        asort($opt);
    }

    return $opt;
}
