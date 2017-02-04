<?php
declare(strict_types = 1);

namespace qnd;

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
    return file_dir(project_path('media')) && move_uploaded_file($src, project_path('media', $dest));
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
 * @param int $mode
 * @param bool $recursive
 *
 * @return bool
 */
function file_dir(string $path, int $mode = 0775, bool $recursive = true): bool
{
    return is_dir($path) || mkdir($path, $mode, $recursive);
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
    $output($img, $cache, $opts['quality']);
    imagedestroy($src);
    imagedestroy($img);

    return is_file($cache) ? url_cache($cacheId) : url_media($id);
}
