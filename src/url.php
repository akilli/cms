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

    if (strpos($path, 'http') === 0) {
        $base = '';
    } else {
        $base = config('url.base') ?: request('base');
    }

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
function url_asset(string $path): string
{
    static $base;

    if ($base === null) {
        $base = config('url.asset') ?: url('asset');
        $base = rtrim($base, '/');
    }

    return strpos($path, 'http') === 0 ? $path : $base . '/' . trim($path, '/');
}

/**
 * CSS URL
 *
 * @param string $path
 *
 * @return string
 */
function url_css(string $path): string
{
    return url_asset('css/' . trim($path, '/'));
}

/**
 * JavaScript URL
 *
 * @param string $path
 *
 * @return string
 */
function url_js(string $path): string
{
    return url_asset('js/' . trim($path, '/'));
}

/**
 * Media URL
 *
 * @param string $path
 *
 * @return string
 */
function url_media(string $path): string
{
    return url_asset('media/' . trim($path, '/'));
}

/**
 * Cache URL
 *
 * @param string $path
 *
 * @return string
 */
function url_cache(string $path): string
{
    return url_asset('cache/' . trim($path, '/'));
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
        $delimiter = '/';
        $separator = '/';
        $paramSeparator = '/';
    } else {
        $delimiter = '?';
        $separator = '=';
        $paramSeparator = '&';
    }

    $query = [];

    foreach ($params as $key => $value) {
        $query[] = $key . $separator . $value;
    }

    return $delimiter . implode($paramSeparator, $query);
}

/**
 * Resolves wildcards, i.e. asterisks, for controller and action part with appropriate values from current request
 *
 * @param string $key
 *
 * @return string
 */
function url_resolve(string $key = ''): string
{
    if (!$key) {
        return request('id');
    }

    $parts = explode('/', $key);

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
    $path = $path ?: 'http-base';
    $item = model_load('rewrite', ['id' => $path], false);

    if (!$item) {
        return $path;
    }

    if (!empty($item['redirect'])) {
        redirect($item['target']);
    }

    return $item['target'];
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
        $data = model_load('rewrite', null, 'target', ['system' => 'desc']);
    }

    $url = !empty($data[$path . $query]) ? $data[$path . $query]['id'] : $path . $query;

    return $url === 'http-base' ? '' : $url;
}
