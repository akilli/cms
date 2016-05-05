<?php
namespace qnd;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * List files recursively inside the specified path
 *
 * @param string $path
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int|array $limit
 *
 * @return array
 */
function file_load(string $path, array $criteria = null, $index = null, array $order = null, $limit = null): array
{
    if (!is_dir($path)) {
        return [];
    }

    $data = [];
    $single = $index === false;

    if (!$index || $index === 'search') {
        $search = $index === 'search';
        $index = 'id';
    }

    $iterator = new RecursiveDirectoryIterator(
        $path,
        RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS
    );

    if (!empty($criteria['recursive'])) {
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::LEAVES_ONLY);
        unset($criteria['recursive']);
    }

    /* @var SplFileInfo $item */
    foreach ($iterator as $item) {
        if (!$item->isFile()) {
            continue;
        }

        $item = [
            'id' => $iterator->getSubPathname(),
            'name' => $iterator->getSubPathname(),
            'path' => $item->getRealPath(),
            'extension' => $item->getExtension(),
            'size' => $item->getSize(),
            'modified' => $item->getMTime()
        ];

        if ($single) {
            return $item;
        }

        if ($index === 'unambiguous') {
            $data['id'][$item['id']] = $item['id'];
        } elseif (is_array($index)
            && !empty($index[0])
            && !empty($index[1])
            && !empty($item[$index[0]])
            && !empty($item[$index[1]])
        ) {
            $data[$item[$index[0]]][$item[$index[1]]] = $item;
        } else {
            $data[$item[$index]] = $item;
        }
    }

    if ($criteria) {
        $data = data_filter($data, $criteria, !empty($search));
    }

    if ($order) {
        $data = data_order($data, $order);
    }

    if ($limit) {
        $data = data_limit($data, $limit);
    }

    return $data;
}

/**
 * Create file
 *
 * @param string $destination
 * @param string $content
 * @param int $flags
 * @param resource $context
 *
 * @return int|bool
 */
function file_save(string $destination, string $content, int $flags = 0, $context = null)
{
    if (!file_dir(dirname($destination))) {
        return false;
    }

    $umask = umask(0);
    $result = file_put_contents($destination, $content, $flags, $context);
    umask($umask);

    return $result;
}

/**
 * Removes a file or directory
 *
 * A directory will be removed recursively, will preserve specified path if $preserve is set to true
 *
 * @param string $path
 * @param bool $preserve
 *
 * @return bool
 */
function file_delete(string $path, bool $preserve = false): bool
{
    if (!file_exists($path)) {
        return true;
    } elseif (!file_writable($path)) {
        return false;
    } elseif (is_file($path)) {
        return unlink($path);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS
        ),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    /* @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } elseif ($file->isFile() || $file->isLink()) {
            unlink($file->getPathname());
        }
    }

    if ($preserve) {
        return true;
    }

    rmdir($path);

    return !file_exists($path);
}

/**
 * Copies a file or directory
 *
 * @param string $source
 * @param string $destination
 * @param string|array $criteria
 *
 * @return bool
 */
function file_copy(string $source, string $destination, $criteria = null): bool
{
    if (!($isFile = is_file($source)) && !is_dir($source) || !file_dir(dirname($destination))) {
        return false;
    }

    $umask = umask(0);

    if ($isFile) {
        copy($source, $destination);
    } else {
        $files = file_load($source, $criteria);

        foreach ($files as $id => $file) {
            if (file_dir(dirname($destination . '/' . $id))) {
                copy($file['path'], $destination . '/' . $id);
            }
        }
    }

    umask($umask);

    return $isFile ? is_file($destination) : is_dir($destination);
}

/**
 * Upload file
 *
 * @param string $source
 * @param string $destination
 *
 * @return bool
 */
function file_upload(string $source, string $destination): bool
{
    if (!is_uploaded_file($source) || !file_dir(dirname($destination))) {
        return false;
    }

    $umask = umask(0);
    move_uploaded_file($source, $destination);
    umask($umask);

    return is_file($destination);
}

/**
 * Makes a directory if it doesn't exist
 *
 * @param string $path
 * @param int $mode
 * @param bool $recursive
 *
 * @return bool
 */
function file_dir(string $path, int $mode = 0775, bool $recursive = true): bool
{
    if (!file_writable($path)) {
        return false;
    } elseif (is_dir($path)) {
        return true;
    }

    $umask = umask(0);
    $result = mkdir($path, $mode, $recursive);
    umask($umask);

    return $result;
}

/**
 * Checks whether specified path is writable
 *
 * @param string $path
 *
 * @return bool
 */
function file_writable(string $path): bool
{
    static $pattern;

    if ($pattern === null) {
        $pattern = '#^(file://)?(' . path('app') . '|' . path('cache') . ')#';
    }

    return (bool) preg_match($pattern, $path);
}

/**
 * Retrieve configured extensions
 *
 * @param string $key
 *
 * @return array
 */
function file_ext(string $key): array
{
    static $data;

    if ($data === null) {
        $data['file'] = config('file.all');
        $data['audio'] = config('file.audio');
        $data['embed'] = config('file.embed');
        $data['image'] = config('file.image');
        $data['video'] = config('file.video');
    }

    return $data[$key] ?? [];
}
