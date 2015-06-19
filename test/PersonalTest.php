<?php

use Px2svg\Converter;

require_once('../src/Converter.php');

$converter = new Converter();

$output = $converter->loadImage('build.gif')->saveSVG('./test.svg');