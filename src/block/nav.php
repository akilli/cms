<?php
declare(strict_types=1);

namespace block\nav;

use app;
use arr;
use layout;
use DomainException;

/**
 * @throws DomainException
 */
function render(array $block): string
{
    if (!$block['cfg']['data']) {
        return '';
    }

    $count = count($block['cfg']['data']);
    $start = current($block['cfg']['data'])['level'] ?? 1;
    $base = ['name' => null, 'url' => null, 'disabled' => false, 'level' => $start];
    $level = 0;
    $i = 0;
    $attrs = ['id' => $block['id']];
    $call = function (array $it): ?string {
        $url = app\data('request', 'url');
        return match (true) {
            $it['url'] === $url => 'active',
            $it['url'] && str_starts_with($url, preg_replace('#\.html#', '', $it['url'])) => 'path',
            default => null,
        };
    };
    $html = $block['cfg']['title'] ? app\html('h2', [], app\i18n($block['cfg']['title'])) : '';
    $html .= layout\render_children($block['id']);

    if ($block['cfg']['toggle']) {
        $html .= app\html('a', ['data-action' => 'toggle', 'data-target' => $block['id']]);
        $attrs['data-toggle'] = '';
    }

    foreach ($block['cfg']['data'] as $item) {
        if (empty($item['name'])) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $item = arr\replace($base, $item);
        $item['level'] = $item['level'] - $start + 1;
        $a = $item['url'] && !$item['disabled'] ? ['href' => $item['url']] : [];
        $c = (array) $call($item);
        $class = '';
        $toggle = '';

        if ($next = next($block['cfg']['data'])) {
            $next = arr\replace($base, $next);
            $next['level'] = $next['level'] - $start + 1;
        }

        if ($next && $item['level'] < $next['level']) {
            if (!$c && $call($next)) {
                $c = ['path'];
            }

            $c[] = 'parent';

            if ($block['cfg']['toggle']) {
                $ta = ['data-action' => 'toggle'];

                if (array_intersect(['active', 'path'], $c)) {
                    $ta['data-toggle'] = '';
                }

                $toggle = app\html('a', $ta);
            }
        }

        if ($c) {
            $a['class'] = implode(' ', $c);
            $class = ' class="' . $a['class'] . '"';
        }

        $html .= match ($item['level'] <=> $level) {
            1 => '<ul><li' . $class . '>',
            -1 => '</li>' . str_repeat('</ul></li>', $level - $item['level']) . '<li' . $class . '>',
            default => '</li><li' . $class . '>',
        };
        $html .= $toggle . app\html('a', $a, $item['name']);
        $html .= ++$i === $count ? str_repeat('</li></ul>', $item['level']) : '';
        $level = $item['level'];
    }

    return $block['cfg']['tag'] ? app\html($block['cfg']['tag'], $attrs, $html) : $html;
}
