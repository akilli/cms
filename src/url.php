<?php
namespace url;

use app;
use config;
use http;
use model;

/**
 * Generate URL by given path and params
 *
 * @param string $path
 * @param array $params
 *
 * @return string
 */
function path($path = '', array $params = [])
{
    $isFullPath = false;

    if (strpos($path, 'http') === 0) {
        $base = '';
    } else {
        $base = config\value('url.base') ?: http\request('base');
    }

    if ($path && strpos($path, 'http') !== 0) {
        $path = resolve($path);
        $isFullPath = true;
    }

    return $base . unrewrite($path, query($params, $isFullPath));
}

/**
 * Asset URL
 *
 * @param string $path
 *
 * @return string
 */
function asset($path)
{
    static $base;

    if ($base === null) {
        $base = config\value('url.asset') ?: path('asset');
        $base = rtrim($base, '/');
    }

    return strpos($path, 'http') === 0 ? $path : $base . '/' . trim($path, '/');
}

/**
 * Media URL
 *
 * @param string $path
 *
 * @return string
 */
function media($path)
{
    return asset('media/' . trim($path, '/'));
}

/**
 * Cache URL
 *
 * @param string $path
 *
 * @return string
 */
function cache($path)
{
    return asset('cache/' . trim($path, '/'));
}

/**
 * Generate query string for given params
 *
 * @param array $params
 * @param bool $isFullPath
 *
 * @return string
 */
function query(array $params, $isFullPath = false)
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
function resolve($key = '')
{
    if (!$key) {
        return http\request('id');
    }

    $parts = explode('/', $key);

    // Wildcard for Entity Part
    if (!empty($parts[0]) && $parts[0] === '*') {
        $parts[0] = http\request('entity');
    }

    // Wildcard for Action Part
    if (!empty($parts[1]) && $parts[1] === '*') {
        $parts[1] = http\request('action');
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
function rewrite($path)
{
    $path = $path ?: 'http-base';
    $item = model\load('rewrite', ['id' => $path], false);

    if (!$item) {
        return $path;
    }

    if (!empty($item['is_redirect'])) {
        http\redirect($item['target']);
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
function unrewrite($path, $query = null)
{
    static $data;

    if ($data === null) {
        $data = model\load('rewrite', null, 'target', ['is_system' => 'desc']);
    }

    $url = !empty($data[$path . $query]) ? $data[$path . $query]['id'] : $path . $query;

    return $url === 'http-base' ? '' : $url;
}
