<?php
declare(strict_types = 1);

namespace file;

/**
 * Loads data from file
 */
function one(string $file): array
{
    if (is_file($file) && str_ends_with($file, APP['php.ext']) && ($data = include $file) && is_array($data)) {
        return $data;
    }

    return [];
}

/**
 * Loads data from all files in given directory
 */
function all(string $path): array
{
    $data = [];

    foreach (glob($path . '/*' . APP['php.ext']) as $id) {
        $data[basename($id, APP['php.ext'])] = one($id);
    }

    return $data;
}

/**
 * Saves data to file
 */
function save(string $file, array $data): bool
{
    return dir(dirname($file)) && file_put_contents($file, "<?php\nreturn " . var_export($data, true) . ';') > 0;
}

/**
 * Uploads a file
 */
function upload(string $src, string $dest): bool
{
    return $src && $dest && dir(dirname($dest)) && move_uploaded_file($src, $dest);
}

/**
 * Removes a file or directory
 */
function delete(string $path): bool
{
    if (!writable($path)) {
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
            delete($file);
        }
    }

    return rmdir($path);
}

/**
 * Makes a directory if it doesn't exist
 */
function dir(string $path): bool
{
    return writable($path) && (is_dir($path) || mkdir($path, 0755, true));
}

/**
 * Checks if path is writable
 */
function writable(string $path): bool
{
    return (bool) preg_match('#^(file://)?(' . APP['path']['file'] . '|' . APP['path']['tmp'] . ')#', $path);
}
