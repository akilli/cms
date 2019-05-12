<?php
declare(strict_types = 1);

namespace contentfilter;

use app;
use arr;
use str;

/**
 * Converts email addresses to HTML entity hex format
 */
function email(string $html): string
{
    $call = function (array $m): string {
        return str\hex($m[0]);
    };

    return preg_replace_callback('#(?:mailto:)?[\w.-]+@[\w.-]+\.[a-z]{2,6}#im', $call, $html);
}

/**
 * Makes img-elements somehow responsive
 */
function image(string $html, array $cfg = []): string
{
    if (empty($cfg['srcset'])) {
        return $html;
    }

    $cfg = arr\replace(APP['image'], $cfg);
    $pattern = '#(<img(?:[^>]*) src="' . APP['url.file'] . '(\d+)\.(jpg|png|webp)")((?:[^>]*)>)#';
    $call = function (array $m) use ($cfg): string {
        $w = & app\registry('contentfilter.image');
        $w[$m[2]] = $w[$m[2]] ?? getimagesize(app\path('file', $m[2] . '.' . $m[3]))[0] ?: null;
        $sizes = $cfg['sizes'] ? ' sizes="' . $cfg['sizes'] . '"' : '';
        $set = '';

        if (strpos($m[0], 'srcset="') === false && $w[$m[2]]) {
            foreach ($cfg['srcset'] as $s) {
                if ($s >= $w[$m[2]]) {
                    $set .= $set ? ', ' . APP['url.file'] . $m[2] . '.' . $m[3] . ' ' . $w[$m[2]] . 'w' : '';
                    break;
                }

                $set .= ($set ? ', ' : '') . APP['url.file'] . $m[2] . '/' . $s . '.' . $m[3] . ' ' . $s . 'w';
            }
        }

        return $set ? $m[1] . ' srcset="' . $set . '"' . $sizes . $m[4] : $m[0];
    };

    return preg_replace_callback($pattern, $call, $html);
}
