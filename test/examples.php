<html>
<head>
<title>Some examples</title>
<style type="text/css">
div {padding: 1em; border-bottom: 1px dotted silver;}
svg {margin: 0 1em;}
</style>
</head>

<body>
<?php
require '../src/Converter.php';

use Px2svg\Converter;
//header('Content-Type: text/xml');

$images = array(
	'red-box.gif',
	'red-nose.gif',
	'test-vertical.png',
	'test-horizontal.png',
	'gmail-bozo-tag.gif',
	'darth_vader.png',
);

foreach ($images as $img) {

	echo "\n<div>\n<img src='$img'>\n";
	$converter = new Converter();
	$converter->loadImage($img);
	$converter->setThreshold(80);
	$res = $converter->generateSVG();
	echo $res;
	echo "\n</div>\n";
}


?>
</body>
</html>