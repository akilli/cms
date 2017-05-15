<?php
declare(strict_types = 1);

namespace qnd;

/**
 * Generate URL by given path and params
 *
 * @param string $path
 * @param array $params
 *
 * @return string
 */
function url(string $path = '', array $params = []): string
{
    if ($path && ($path[0] === '#' || strpos($path, 'http') === 0)) {
        return $path;
    }

    if (!$path = trim($path, '/')) {
        return '/';
    }

    return '/' . resolve($path) . ($params ? '?' . http_build_query($params, '', '&amp;') : '');
}

/**
 * Project asset URL
 *
 * @param string $path
 *
 * @return string
 */
function url_asset(string $path): string
{
    return URL['asset'] . project('id') . '/' . $path;
}

/**
 * Project media URL
 *
 * @param string $path
 *
 * @return string
 */
function url_media(string $path): string
{
    return URL['media'] . $path;
}

/**
 * Theme URL
 *
 * @param string $path
 *
 * @return string
 */
function url_theme(string $path): string
{
    return URL['theme'] . $path;
}

/**
 * Rewrite URL
 *
 * @param string $path
 *
 * @return string
 */
function url_rewrite(string $path): string
{
    if ($path === '/') {
        return data('app', 'home');
    }

    if (!preg_match('#' . URL['page'] . '$#', $path)) {
        return $path;
    }

    $data = & registry('url');

    if (empty($data[$path])) {
        $data[$path] = ($page = one('page', [['url', $path]])) ? '/page/view/' . $page['id'] : $path;
    }

    return $data[$path];
}
