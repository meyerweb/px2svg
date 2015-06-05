<?php

include 'converter.php';

$url = "http://area51/px2svg/gmail-bozo-tag.gif";
$img = loadImage($url);
if (!!$img) {
    header('Content-type: image/svg+xml');
    echo generateSVG($img);
}
else {
    echo "<a href=\"$url\">Bad image file</a>";
}


?>
