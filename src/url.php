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
    $isFullPath = false;
    $base = strpos($path, 'http') === 0 ? '' : request('base');

    if ($path && strpos($path, 'http') !== 0) {
        $path = url_resolve($path);
        $isFullPath = true;
    }

    return $base . url_unrewrite($path, url_query($params, $isFullPath));
}

/**
 * Asset URL
 *
 * @param string $path
 *
 * @return string
 */
function url_asset(string $path = ''): string
{
    static $base;

    if ($base === null) {
        $base = 'asset/' . project('id');
    }

    return url($base . ($path ? '/' . $path : ''));
}

/**
 * Media URL
 *
 * @param string $path
 *
 * @return string
 */
function url_media(string $path = ''): string
{
    static $base;

    if ($base === null) {
        $base = 'media/' . project('id');
    }

    return url($base . ($path ? '/' . $path : ''));
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
    static $base;

    if ($base === null) {
        $base = 'theme/' . project('theme');
    }

    return url($base . ($path ? '/' . $path : ''));
}

/**
 * Generate query string for given params
 *
 * @param array $params
 * @param bool $isFullPath
 *
 * @return string
 */
function url_query(array $params, bool $isFullPath = false): string
{
    if (!$params) {
        return '';
    }

    if ($isFullPath) {
        $del = '/';
        $sep = '/';
        $glue = '/';
    } else {
        $del = '?';
        $sep = '=';
        $glue = '&';
    }

    $query = [];

    foreach ($params as $key => $value) {
        $query[] = $key . $sep . $value;
    }

    return $del . implode($glue, $query);
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
    static $data;

    if ($data === null) {
        $data = all('rewrite', [], ['index' => 'name']);
    }

    if (!isset($data[$path])) {
        return $path;
    }

    if (!empty($data[$path]['redirect']) && $redirect) {
        redirect($data[$path]['target']);
    }

    return $data[$path]['target'];
}

/**
 * Un-rewrite URL
 *
 * @param string $path
 * @param string $query
 *
 * @return string
 */
function url_unrewrite(string $path, string $query = null): string
{
    static $data;

    if ($data === null) {
        $data = all('rewrite', [], ['index' => 'target', 'order' => ['system' => 'desc']]);
    }

    $url = !empty($data[$path . $query]) ? $data[$path . $query]['name'] : $path . $query;

    return $url;
}
