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
 * @param bool $redirect
 *
 * @return string
 */
function url_rewrite(string $path, bool $redirect = false): string
{
    $data = & registry('url.rewrite');

    if (!empty($data[$path])) {
        if ($url = one('url', ['name' => $path])) {
            $data[$path] = ['target' => $url['target'], 'redirect' => $url['redirect']];
        } else {
            $data[$path] = ['target' => $path === '/' ? '/content/index' : $path, 'redirect' => false];
        }
    }

    if ($redirect && $data[$path]['redirect']) {
        redirect($data[$path]['target']);
    }

    return $data[$path]['target'];
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
    $data = & registry('url.unrewrite');

    if (!empty($data[$path])) {
        $url = one('url', ['target' => $path, 'redirect' => false], ['order' => ['system' => 'desc']]);
        $data[$path] = $url ? $url['name'] : $path;
    }

    return $data[$path];
}
