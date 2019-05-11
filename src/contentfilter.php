<?php
declare(strict_types = 1);

namespace contentfilter;

use app;
use str;

/**
 * Converts email addresses to HTML entity hex format
 */
function email(string $val): string
{
    $call = function (array $m): string {
        return str\hex($m[0]);
    };

    return preg_replace_callback('#(?:mailto:)?[\w.-]+@[\w.-]+\.[a-z]{2,6}#im', $call, $val);
}

/**
 * Makes img-elements somehow responsive
 */
function image(string $val): string
{
    $pattern = '#(<img(?:[^>]*) src="' . APP['url.file'] . '([0-9]+)\.(jpg|png|webp)")((?:[^>]*)>)#';
    $call = function (array $m): string {
        if (strpos($m[0], 'srcset="') !== false) {
            return $m[0];
        }

        $w = & app\registry('contentfilter.image');
        $w[$m[2]] = $w[$m[2]] ?? getimagesize(app\path('file', $m[2] . '.' . $m[3]))[0] ?? null;
        $set = '';

        if ($w[$m[2]]) {
            foreach (APP['image'] as $s) {
                if ($s >= $w[$m[2]]) {
                    $set .= $set ? ', ' . APP['url.file'] . $m[2] . '.' . $m[3] . ' ' . $w[$m[2]] . 'w' : '';
                    break;
                }

                $set .= ($set ? ', ' : '') . APP['url.file'] . $m[2] . '/' . $s . '.' . $m[3] . ' ' . $s . 'w';
            }
        }

        return $m[1] . ($set ? ' srcset="' . $set . '"' : '') . $m[4];
    };

    return preg_replace_callback($pattern, $call, $val);
}
