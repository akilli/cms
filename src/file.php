<?php
namespace qnd;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Load file collection
 *
 * @param string $path
 *
 * @return array
 */
function file_load(string $path): array
{
    if (!is_dir($path)) {
        return [];
    }

    $data = [];
    $flags = RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS;
    $it = new RecursiveDirectoryIterator($path, $flags);

    /* @var SplFileInfo $file */
    foreach ($it as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $item = [
            'id' => $it->getSubPathname(),
            'name' => $it->getBasename(),
            'ext' => $file->getExtension(),
            'subdir' => dirname($it->getSubPathname()),
            'path' => $file->getRealPath(),
            'dir' => $file->getPathInfo()->getRealPath(),
            'size' => $file->getSize(),
            'modified' => $file->getMTime()
        ];
        $data[$item['id']] = $item;
    }

    return $data;
}

/**
 * Create file
 *
 * @param string $dest
 * @param string $content
 * @param int $flags
 * @param resource $context
 *
 * @return bool
 */
function file_save(string $dest, string $content, int $flags = 0, $context = null): bool
{
    if (!file_dir(dirname($dest))) {
        return false;
    }

    $umask = umask(0);
    $result = file_put_contents($dest, $content, $flags, $context);
    umask($umask);

    return $result !== false;
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
    } elseif (is_file($path)) {
        return unlink($path);
    }

    $flags = RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, $flags), RecursiveIteratorIterator::CHILD_FIRST);

    /* @var SplFileInfo $file */
    foreach ($it as $file) {
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
 * Deletes a media file
 *
 * @param string $id
 *
 * @return bool
 */
function file_delete_media(string $id): bool
{
    return !$id || file_delete(project_path('cache', $id)) && file_delete(project_path('media', $id));
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
    $isFile = is_file($src);

    if ((!$isFile || !file_dir(dirname($dest))) && (!is_dir($src) || !file_dir($dest))) {
        return false;
    }

    $umask = umask(0);

    if ($isFile) {
        copy($src, $dest);
    } else {
        $files = file_load($src);

        foreach ($files as $file) {
            if (file_dir(dirname($dest . '/' . $file['id']))) {
                copy($file['path'], $dest . '/' . $file['id']);
            }
        }
    }

    umask($umask);

    return $isFile ? is_file($dest) : is_dir($dest);
}

/**
 * Upload file
 *
 * @param string $src
 * @param string $dest
 *
 * @return bool
 */
function file_upload(string $src, string $dest): bool
{
    if (!is_uploaded_file($src) || !file_dir(dirname($dest))) {
        return false;
    }

    $umask = umask(0);
    move_uploaded_file($src, $dest);
    umask($umask);

    return is_file($dest);
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
    if (is_dir($path)) {
        return true;
    }

    $umask = umask(0);
    $result = mkdir($path, $mode, $recursive);
    umask($umask);

    return $result;
}

/**
 * Image file
 *
 * @param string $id
 * @param string $conf
 *
 * @return string
 */
function image(string $id, string $conf): string
{
    $file = project_path('media', $id);

    if (!is_file($file) || !($opts = data('image', $conf)) || !($info = getimagesize($file))) {
        return url_media($id);
    }

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $width = $info[0];
    $height = $info[1];
    $x = $y = 0;

    if ($info[0] <= $opts['width'] && $info[1] <= $opts['height']) {
        $opts['width'] = $info[0];
        $opts['height'] = $info[1];
    } elseif (!$opts['crop']) {
        if ($info[0] / $info[1] >= $opts['width'] / $opts['height']) {
            $opts['height'] = round(($opts['width'] / $info[0]) * $info[1]);
        } else {
            $opts['width'] = round(($opts['height'] / $info[1]) * $info[0]);
        }
    } else {
        if ($info[0] > $opts['width']) {
            $width = $opts['width'];
            $x = ($info[0] - $opts['width']) / 2;
        }

        if ($info[1] > $opts['height']) {
            $height = $opts['height'];
            $y = ($info[1] - $opts['height']) / 2;
        }
    }

    $cacheId = $id . '/' . $opts['width'] . '-' . $opts['height'] . ($opts['crop'] ? '-crop' : '') . '.' . $ext;
    $cache = project_path('cache', $cacheId);

    if (file_exists($cache) && filemtime($file) < filemtime($cache)) {
        return url_cache($cacheId);
    }

    if ($info[0] === $opts['width'] && $info[1] === $opts['height']) {
        return file_copy($file, $cache) ? url_cache($cacheId) : url_media($id);
    }

    if ($info[2] === IMAGETYPE_JPEG) {
        $create = 'imagecreatefromjpeg';
        $output = 'imagejpeg';
    } elseif ($info[2] === IMAGETYPE_PNG) {
        $create = 'imagecreatefrompng';
        $output = 'imagepng';
        $opts['quality'] = round(($opts['quality'] / 100) * 10);

        if ($opts['quality'] < 1) {
            $opts['quality'] = 1;
        } elseif ($opts['quality'] > 10) {
            $opts['quality'] = 10;
        }

        $opts['quality'] = 10 - $opts['quality'];
    } elseif ($info[2] === IMAGETYPE_GIF) {
        $create = 'imagecreatefromgif';
        $output = 'imagegif';
    } elseif ($info[2] === IMAGETYPE_WEBP) {
        $create = 'imagecreatefromwebp';
        $output = 'imagewebp';
    } else {
        return url_media($id);
    }

    file_dir(dirname($cache));
    $src = $create($file);
    $img = imagecreatetruecolor($opts['width'], $opts['height']);
    $alpha = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagecolortransparent($img, $alpha);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagecopyresampled($img, $src, 0, 0, $x, $y, $opts['width'], $opts['height'], $width, $height);
    $umask = umask(0);
    $output($img, $cache, $opts['quality']);
    umask($umask);
    imagedestroy($src);
    imagedestroy($img);

    return is_file($cache) ? url_cache($cacheId) : url_media($id);
}
