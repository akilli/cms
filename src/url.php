<?php
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

    $path = ($path = trim($path, '/')) ? url_unrewrite('/' . url_resolve($path)) : '/';

    return $path . ($params ? '?' . http_build_query($params, '', '&amp;') : '');
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
 * Project cache URL
 *
 * @param string $path
 *
 * @return string
 */
function url_cache(string $path = ''): string
{
    return '/asset/' . project('uid') . '/cache' . ($path ? '/' . $path : '');
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
    return '/asset/' . project('uid') . '/media' . ($path ? '/' . $path : '');
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
 * Rewrite request path
 *
 * @param string $path
 *
 * @return string
 */
function url_rewrite(string $path): string
{
    $data = & registry('url');

    if (empty($data[$path])) {
        if ($url = one('url', ['name' => $path])) {
            $data[$url['name']] = $url['target'];
        } elseif($path === '/') {
            $data[$path] = '/' . data('request', 'entity') . '/' . data('request', 'action');
        } else {
            $data[$path] = $path;
        }
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
    $data = & registry('url');

    if (!in_array($path, (array) $data)) {
        if ($url = one('url', ['target' => $path], ['order' => ['system' => 'desc']])) {
            $data[$url['name']] = $path;
        } else {
            $data[$path] = $path;
        }
    }

    return array_search($path, $data);
}
