<?php

include 'converter.php';

/**
 * Flaming Shame Raster to SVG converter
 *
 * @author  Eric Meyer, Amelia Bellamy-Royds, Robin Cafolla
 * @arg     string url      Takes a single string url or path to local image to
 *                          convert from raster to SVG.
 */
if (count($argv) < 1) {
    throw new \RuntimeException(
        'Too few arguments passed to converter'
    );
}

$url = $argv[1];

$img = loadImage($url);
if (!!$img) {
    header('Content-type: image/svg+xml');
    echo generateSVG($img);
}
else {
    echo "<a href=\"$url\">Bad image file</a>";
}

?>