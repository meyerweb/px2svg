<?php

// error_reporting(0);

include 'converter.php';

$converter = new px2svg();
$url = "http://www.nealio.co.uk/images/Octocat.png";

header('Content-type: image/svg+xml');
echo $converter->loadImage($url)->generateSVG();
