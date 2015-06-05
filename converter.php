<?php
/**
 * Flaming Shame Raster to SVG converter
 *
 * @author  Eric Meyers, Amelia Bellamy-Royds, Robin Cafolla
 * @arg     string url      Takes a single string url or path to local image to
 *                          convert from raster to SVG.
 */
if (php_sapi_name() == 'cli' && count($argv) < 1) {
    throw new \RuntimeException(
        'Too few arguments passed to converter'
    );
}

if(php_sapi_name() == 'cli') {

$url = $argv[1];
}


$img = loadImage($url);
if (!!$img) {
    header('Content-type: image/svg+xml');
    echo generateSVG($img);
}
else {
    echo "<a href=\"$url\">Bad image file</a>";
}

/**
 * Generates svg from raster
 *
 * @param GDImageIdentifier $img    Raster image to convert to svg
 * @return string                   SVG xml
 */
function generateSVG($img) {
    $width = imagesx($img); // image width
    $height = imagesy($img); // image height

    $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"crispEdges\">";
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y = $y+$number_of_consecutive_pixels) {
            $color_at_position = imagecolorat($img, $x, $y);
            $number_of_consecutive_pixels = 1;

            while(
                ($y+$number_of_consecutive_pixels < $height) &&
                ($color_at_position == imagecolorat($img, $x, ($y+$number_of_consecutive_pixels)))
            ) {
                $number_of_consecutive_pixels++;
            }

            $rgb = imagecolorsforindex($img, $color_at_position);
            $color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

            if ($rgb["alpha"] && ($rgb["alpha"] < 128 )) {
                $alpha = (128 - $rgb["alpha"]) / 128;
                $color .= "\" fill-opacity=\"$alpha";
            }

            $svg .= "<rect width=\"1\" x=\"$x\" height=\"$number_of_consecutive_pixels\" y=\"$y\" fill=\"$color\"/>\n";
        }
    }

    $svg .= '</svg>';
    return $svg;
}

/**
 * Get an image from a URL or file path
 *
 * @param string $url   Url or path to local file
 * @return GDImageIdentifier
 */
function loadImage( $url ) {
    // Handle URLS
    if( filter_var($url, FILTER_VALIDATE_URL) ){
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
    }
    // Handle local files
    else if ( file_exists( $url ) ){
        $img = @imagecreatefromstring( file_get_contents( $url ) );
    }
    else {
        throw new \LogicException( 'Url is invalid.' );
    }

    return $img;
}

?>
