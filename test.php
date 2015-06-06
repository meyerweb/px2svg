<?php

// error_reporting(0);

include 'converter.php';

$converter = new px2svg();
$url = "./gmail-bozo-tag.gif";

header('Content-type: text/html');

echo $converter->loadImage($url)->generateSVG();

echo "\n\n";

echo $converter->loadImage('test-horizontal.png')->generateSVG();

echo "\n\n";

echo $converter->loadImage('test-vertical.png')->generateSVG();