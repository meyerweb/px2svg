<?php

error_reporting(-1);
ini_set('display_errors', '1');

$path = dirname(__DIR__);

require $path.'/vendor/autoload.php';

$converter = new \Px2svg\Converter();

foreach (['gmail-bozo-tag.gif', 'test-horizontal.png', 'test-vertical.png'] as $img) {
    echo $converter->loadImage($img)->generateSVG(), PHP_EOL;
}
