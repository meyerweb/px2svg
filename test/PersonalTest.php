<?php

use Px2svg\Converter;

require_once('../src/Converter.php');

$converter = new Converter();

$output = $converter->loadImage('gmail-bozo-tag.gif')->setThreshold(10)->saveSVG('./test.svg');