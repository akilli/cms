<?php
namespace media;

use app;
use file;
use url;

/**
 * Media load
 *
 * @param string $key
 *
 * @return array|null
 */
function load($key = null)
{
    static $data;

    if ($data === null) {
        $data = file\scan(app\path('media'));
    }

    if ($key === null) {
        return $data;
    }

    return isset($data[$key]) ? $data[$key] : null;
}

/**
 * Media delete
 *
 * @param string $id
 *
 * @return bool
 */
function delete($id)
{
    return file\remove(app\path('cache', 'media/' . $id))
    && (!$id || !($media = load($id)) || file\remove($media['path']));
}

/**
 * Media image
 *
 * @param array $media
 * @param string $class
 *
 * @return string|bool
 */
function image(array $media, $class)
{
    if (!($config = app\data('media', $class))
        || !file_exists($media['path'])
        || !$info = getimagesize($media['path'])
    ) {
        return url\media($media['id']);
    }

    // Dimensions
    $width = $config['width'];
    $height = $config['height'];
    $quality = $config['quality'];
    $crop = $config['crop'];
    $sourceWidth = $info[0];
    $sourceHeight = $info[1];
    $x = $y = 0;

    if ($info[0] <= $width && $info[1] <= $height) {
        $width = $info[0];
        $height = $info[1];
    } elseif (!$crop) {
        if ($info[0] / $info[1] >= $width / $height) {
            $height = round(($width / $info[0]) * $info[1]);
        } else {
            $width = round(($height / $info[1]) * $info[0]);
        }
    } else {
        if ($info[0] > $width) {
            $sourceWidth = $width;
            $x = ($info[0] - $width) / 2;
        }

        if ($info[1] > $height) {
            $sourceHeight = $height;
            $y = ($info[1] - $height) / 2;
        }
    }

    // Cache
    $cacheId = $media['id'] . '/' . $width . '-' . $height . ($crop ? '-crop' : '') . '.' . $media['extension'];
    $cachePath = app\path('cache', 'media/' . $cacheId);

    // Generate cache file
    if (!file_exists($cachePath) || $media['modified'] >= filemtime($cachePath)) {
        if ($info[0] === $width && $info[1] === $height) {
            file\duplicate($media['path'], $cachePath);
        } else {
            // Callbacks
            if ($info[2] === IMAGETYPE_JPEG) {
                $create = 'imagecreatefromjpeg';
                $output = 'imagejpeg';
            } elseif ($info[2] === IMAGETYPE_PNG) {
                $create = 'imagecreatefrompng';
                $output = 'imagepng';
                $quality = round(($quality / 100) * 10);

                if ($quality < 1) {
                    $quality = 1;
                } elseif ($quality > 10) {
                    $quality = 10;
                }

                $quality = 10 - $quality;
            } elseif ($info[2] === IMAGETYPE_GIF) {
                $create = 'imagecreatefromgif';
                $output = 'imagegif';
            }

            if (!empty($create) && !empty($output)) {
                // Make cache directory
                file\make(dirname($cachePath));

                // Resource
                $source = $create($media['path']);
                $image = imagecreatetruecolor($width, $height);

                // Transparency
                $alpha = imagecolorallocatealpha($image, 0, 0, 0, 127);
                imagecolortransparent($image, $alpha);
                imagealphablending($image, false);
                imagesavealpha($image, true);

                // Output
                imagecopyresampled($image, $source, 0, 0, $x, $y, $width, $height, $sourceWidth, $sourceHeight);
                $umask = umask(0);
                $output($image, $cachePath, $quality);
                umask($umask);

                // Destroy
                imagedestroy($source);
                imagedestroy($image);
            }
        }
    }

    return file_exists($cachePath) ? url\cache('media/' . $cacheId) : url\media($media['id']);
}
