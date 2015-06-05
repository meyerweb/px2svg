<?php

// error_reporting(0);

include 'converter.php';

$converter = new px2svg();
$url = "./gmail-bozo-tag.gif";

header('Content-type: image/svg+xml');
echo $converter->loadImage($url)->generateSVG();
