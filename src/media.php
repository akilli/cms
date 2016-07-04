<?php
namespace qnd;

/**
 * Media image
 *
 * @param string $file
 * @param array $opts
 *
 * @return string
 */
function image(string $file, array $opts): string
{
    $media = media_load($file);
    $opts = array_replace(['width' => 0, 'height' => 0, 'quality' => 100, 'crop' => false], $opts);

    if (!$media || $opts['width'] <= 0 || $opts['height'] <= 0 || !$info = getimagesize($media['path'])) {
        return url_media($file);
    }

    // Dimensions
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

    // Asset
    $assetId = $media['id'] . '/' . $opts['width'] . '-' . $opts['height'] . ($opts['crop'] ? '-crop' : '') . '.' . $media['ext'];
    $assetPath = project_path('asset', 'media/' . $assetId);

    // Generate asset file
    if (!file_exists($assetPath) || $media['modified'] >= filemtime($assetPath)) {
        if ($info[0] === $opts['width'] && $info[1] === $opts['height']) {
            file_copy($media['path'], $assetPath);
        } else {
            // Callbacks
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
            } elseif ($media['ext'] = 'webp') {
                $create = 'imagecreatefromwebp';
                $output = 'imagewebp';
            }

            if (!empty($create) && !empty($output)) {
                // Make asset directory
                file_dir(dirname($assetPath));

                // Resource
                $source = $create($media['path']);
                $image = imagecreatetruecolor($opts['width'], $opts['height']);

                // Transparency
                $alpha = imagecolorallocatealpha($image, 0, 0, 0, 127);
                imagecolortransparent($image, $alpha);
                imagealphablending($image, false);
                imagesavealpha($image, true);

                // Output
                imagecopyresampled($image, $source, 0, 0, $x, $y, $opts['width'], $opts['height'], $width, $height);
                $umask = umask(0);
                $output($image, $assetPath, $opts['quality']);
                umask($umask);

                // Destroy
                imagedestroy($source);
                imagedestroy($image);
            }
        }
    }

    return file_exists($assetPath) ? url_asset('media/' . $assetId) : url_media($media['id']);
}

/**
 * Media load
 *
 * @param string $key
 *
 * @return array|null
 */
function media_load(string $key = null)
{
    static $data;

    if ($data === null) {
        $data = file_all(project_path('media'));
    }

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}

/**
 * Media delete
 *
 * @param string $id
 *
 * @return bool
 */
function media_delete(string $id): bool
{
    return file_delete(project_path('asset', 'media/' . $id))
        && (!$id || !($media = media_load($id)) || file_delete($media['path']));
}
