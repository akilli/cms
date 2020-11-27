<?php
declare(strict_types=1);

namespace opt;

use app;

/**
 * Privilege
 */
function priv(): array
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
