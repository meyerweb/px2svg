<html>
<head>
<title>Some examples</title>
<style type="text/css">
svg {margin: 1em;}
</style>
</head>

<body>
<?php
require '../src/Converter.php';

use Px2svg\Converter;
//header('Content-Type: text/xml');

$converter = new Converter();
$converter->loadImage('test-horizontal.png');
$converter->setThreshold(80);
$res = $converter->generateSVG();
echo $res;


$converter = new Converter();
$converter->loadImage('test-vertical.png');
$converter->setThreshold(80);
$res = $converter->generateSVG();
echo $res;


$converter = new Converter();
$converter->loadImage('gmail-bozo-tag.gif');
$converter->setThreshold(80);
$res = $converter->generateSVG();
echo $res;


$converter = new Converter();
$converter->loadImage('red-nose.gif');
$converter->setThreshold(80);
$res = $converter->generateSVG();
echo $res;


$converter = new Converter();
$converter->loadImage('darth_vader.png');
$converter->setThreshold(80);
$res = $converter->generateSVG();
echo $res;


?>
</body>
</html>