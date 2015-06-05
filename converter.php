<?php

# $url = "test.png";
# $url = "test.gif";
# $url = "http://fray.com/images/i3c.gif";
# $url = "http://upload.wikimedia.org/wikipedia/commons/b/bb/Quilt_design_as_46x46_uncompressed_GIF.gif";
  $url = "http://pbs.twimg.com/profile_images/539094743133614080/cj8fsBZE_reasonably_small.png";

$img = loadImage($url);
//var_dump( getimagesize($img));
if (!!$img) { 
  header('Content-type: image/svg+xml');
  generateSVG($img); 
}
else {
  echo "<a href=\"$url\">Bad image file</a>";
}


function generateSVG($img) {
	$w = imagesx($img); // image width
	$h = imagesy($img); // image height
  $n = 1; //number of consecutive pixels
	$svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"crispEdges\">";
	for ($x = 0; $x < $w; $x++) {
		for ($y = 0; $y < $h; $y = $y+$n) {
			$col = imagecolorat($img, $x, $y);
      $n = 1;
      while( ($y+$n < $h) &&
        ($col == imagecolorat($img, $x, ($y+$n))) ) {
        $n++;
      }
			$rgb = imagecolorsforindex($img, $col);
		  $color = "rgb($rgb[red],$rgb[green],$rgb[blue])";
      if ($rgb["alpha"] && ($rgb["alpha"] < 128 )) {
        $alpha = (128 - $rgb["alpha"]) / 128;
		    $color .= "\" fill-opacity=\"$alpha";
      }
			$svg .= "<rect width=\"1\" x=\"$x\" height=\"$n\" y=\"$y\" fill=\"$color\"/>\n";
		}
	}
	$svg .= '</svg>';
	echo $svg;
}

function loadImage($url) {

	$ch = curl_init();
	$timeout = 0;
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

	// Getting binary data
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

	$image = curl_exec($ch);
	curl_close($ch);
	// output to browser

	$img = @imagecreatefromstring($image);

  return $img; 
}

?>