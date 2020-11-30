<?php
declare(strict_types=1);

namespace attr\privilege;

use app;

function opt(): array
{
    if (($opt = &app\registry('opt')['privilege']) === null) {
        $opt = [];

        foreach (app\cfg('privilege') as $id => $privilege) {
            if ($privilege['active'] && !$privilege['delegate'] && !$privilege['auto'] && app\allowed($id)) {
                $opt[$id] = $privilege['name'];
            }
        }

        asort($opt);
    }

    return $opt;
}
