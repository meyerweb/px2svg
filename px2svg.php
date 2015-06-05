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

header('Content-type: image/svg+xml');

$converter = new px2svg();
echo $converter->loadImage($url)->generateSVG();
