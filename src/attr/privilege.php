<?php
declare(strict_types=1);

namespace attr\privilege;

use app;

function opt(): array
{
    if (($opt = &app\registry('opt')['privilege']) === null) {
        $opt = [];

        foreach (app\cfg('privilege') as $id => $privilege) {
            if (!$privilege['auto'] && !$privilege['use'] && app\allowed($id)) {
                $opt[$id] = $privilege['name'];
            }
        }

        asort($opt);
    }

    return $opt;
}
