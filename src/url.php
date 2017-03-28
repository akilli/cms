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

    if (preg_match('#^(asset|lib|theme)#', $path)) {
        return '/' . $path;
    }

    if (strpos($path, '*') !== false) {
        $path = url_resolve($path);
    }

    return url_unrewrite('/' . $path) . ($params ? '?' . http_build_query($params, '', '&amp;') : '');
}

/**
 * Project cache URL
 *
 * @param string $path
 *
 * @return string
 */
function url_cache(string $path = ''): string
{
    return '/asset/' . project('id') . '/cache' . ($path ? '/' . $path : '');
}

/**
 * Project media URL
 *
 * @param string $path
 *
 * @return string
 */
function url_media(string $path = ''): string
{
    return '/asset/' . project('id') . '/media' . ($path ? '/' . $path : '');
}

/**
 * Lib URL
 *
 * @param string $path
 *
 * @return string
 */
function url_lib(string $path = ''): string
{
    return '/lib' . ($path ? '/' . $path : '');
}

/**
 * Theme URL
 *
 * @param string $path
 *
 * @return string
 */
function url_theme(string $path = ''): string
{
    return '/theme/' . project('theme') . ($path ? '/' . $path : '');
}

/**
 * Resolves wildcards, i.e. asterisks, for controller and action part with appropriate values from current request
 *
 * @param string $path
 *
 * @return string
 */
function url_resolve(string $path): string
{
    $parts = explode('/', $path);

    // Wildcard for Entity Part
    if (!empty($parts[0]) && $parts[0] === '*') {
        $parts[0] = request('entity');
    }

    // Wildcard for Action Part
    if (!empty($parts[1]) && $parts[1] === '*') {
        $parts[1] = request('action');
    }

    return implode('/', $parts);
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
        return '/' . data('request', 'entity') . '/' . data('request', 'action');
    }

    if (!preg_match('#' . data('app', 'page.url') . '$#', $path)) {
        return $path;
    }

    $data = & registry('url');

    if (empty($data[$path])) {
        $data[$path] = ($page = one('page', ['url' => $path])) ? '/page/view/' . $page['id'] : $path;
    }

    return $data[$path];
}

/**
 * Un-rewrite URL
 *
 * @param string $path
 *
 * @return string
 */
function url_unrewrite(string $path): string
{
    if (!preg_match('#^/page/view/([0-9]+)#', $path, $match)) {
        return $path;
    }

    $data = & registry('url');

    if (in_array($path, (array) $data)) {
        return array_search($path, $data);
    }

    if ($page = one('page', ['id' => $match[1]])) {
        $data[$page['url']];
        return $page['url'];
    }

    $data[$path] = $path;

    return $path;
}
