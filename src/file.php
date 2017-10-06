<?php
declare(strict_types = 1);

namespace cms;

/**
 * Uploads a file
 *
 * @param string $src
 * @param string $dest
 *
 * @return bool
 */
function file_upload(string $src, string $dest): bool
{
    $dest = strpos($dest, '/') === 0 ? $dest : path('media', $dest);

    return file_dir(dirname($dest)) && move_uploaded_file($src, $dest);
}

/**
 * Copies a file or directory
 *
 * @param string $src
 * @param string $dest
 *
 * @return bool
 */
function file_copy(string $src, string $dest): bool
{
    if (is_file($src)) {
        return file_dir(dirname($dest)) && copy($src, $dest);
    }

    if (!is_dir($src) || !file_dir($dest)) {
        return false;
    }

    $success = true;

    foreach (array_diff(scandir($src), ['.', '..']) as $id) {
        $file = $src . '/' . $id;

        if (is_file($file) && !copy($file, $dest . '/' . $id)) {
            $success = false;
        } elseif (is_dir($file)) {
            $success = file_copy($file, $dest . '/' . $id);
        }
    }

    return $success;
}

/**
 * Removes a file or directory
 *
 * @param string $path
 *
 * @return bool
 */
function file_delete(string $path): bool
{
    if (!file_writable($path)) {
        return false;
    }

    if (!file_exists($path) || is_file($path) && unlink($path)) {
        return true;
    }

    foreach (array_diff(scandir($path), ['.', '..']) as $id) {
        $file = $path . '/' . $id;

        if (is_file($file)) {
            unlink($file);
        } elseif (is_dir($file)) {
            file_delete($file);
        }
    }

    return rmdir($path);
}

/**
 * Makes a directory if it doesn't exist
 *
 * @param string $path
 *
 * @return bool
 */
function file_dir(string $path): bool
{
    return file_writable($path) && (is_dir($path) || mkdir($path, 0755, true));
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
    return (bool) preg_match('#^(file://)?(' . path('log') . '|' . path('media') . '|' . path('tmp') . ')#', $path);
}

/**
 * Returns list of accepted extensions for given file type
 *
 * @param string $type
 *
 * @return string
 */
function file_accept(string $type): string
{
    $accept = '';

    foreach (cfg('file') as $ext => $types) {
        if (in_array($type, $types)) {
            $accept .= ($accept ? ', .' : '.') . $ext;
        }
    }

    return $accept;
}
