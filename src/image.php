<?php
declare(strict_types=1);

namespace image;

use file;

/**
 * Creates a resized or cropped version from given source image with provided options
 */
function create(string $src, string $dest, int $width, int $height = null, bool $crop = false): bool
{
    if (!$src
        || !$dest
        || !file\writable($dest)
        || $width <= 0
        || $height && $height <= 0
        || !($mime = mime_content_type($src))
        || empty(APP['image'][$mime])
        || !($info = getimagesize($src))
    ) {
        return false;
    }

    $ratio = $info[0] / $info[1];
    $height ??= $width / $ratio;

    if ($info[0] <= $width && $info[1] <= $height) {
        return $src === $dest || copy($src, $dest);
    }

    // Dimensions
    $wgt = $info[0] > $width;
    $hgt = $info[1] > $height;
    $rgte = $ratio >= $width / $height;
    $w = $crop && $wgt ? $width : $info[0];
    $h = $crop && $hgt ? $height : $info[1];
    $x = $crop && $wgt ? ($info[0] - $width) / 2 : 0;
    $y = $crop && $hgt ? ($info[1] - $height) / 2 : 0;
    $width = !$crop && !$rgte ? round($height * $ratio) : $width;
    $height = !$crop && $rgte ? round($width / $ratio) : $height;

    // Create image
    ['create' => $create, 'output' => $output, 'quality' => $quality] = APP['image'][$mime];
    $srcImg = $create($src);
    $destImg = imagecreatetruecolor($width, $height);
    $alpha = imagecolorallocatealpha($destImg, 0, 0, 0, 127);
    imagecolortransparent($destImg, $alpha);
    imagealphablending($destImg, false);
    imagesavealpha($destImg, true);
    imagecopyresampled($destImg, $srcImg, 0, 0, $x, $y, $width, $height, $w, $h);
    $output($destImg, $dest, ...($quality ? [$quality] : []));
    imagedestroy($srcImg);
    imagedestroy($destImg);

    return file_exists($dest);
}
