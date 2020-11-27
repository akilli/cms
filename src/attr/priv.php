<?php
declare(strict_types=1);

namespace attr\priv;

use app;

function opt(): array
{
    if (($opt = &app\registry('opt')['priv']) === null) {
        $opt = [];

        foreach (app\cfg('priv') as $key => $priv) {
            if ($priv['active'] && !$priv['priv'] && !$priv['auto'] && app\allowed($key)) {
                $opt[$key] = $priv['name'];
            }
        }

        asort($opt);
    }

    return $opt;
}
