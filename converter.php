<?php
/**
 * Flaming Shame Raster to SVG converter
 *
 * @author  Eric Meyers, Amelia Bellamy-Royds, Robin Cafolla, Neal Brooks
 * @arg     string url      Takes a single string url or path to local image to
 *                          convert from raster to SVG.
 */
//if (php_sapi_name() == 'cli' && count($argv) < 1) {
//    throw new \RuntimeException(
//        'Too few arguments passed to converter'
//    );
//}
//
//if (php_sapi_name() == 'cli') {
//
//    $url = $argv[1];
//}


//$img = loadImage($url);
//if (!!$img) {
//    header('Content-type: image/svg+xml');
//    echo generateSVG($img);
//} else {
//    echo "<a href=\"$url\">Bad image file</a>";
//}




class px2svg
{

    private $image;


    /**
     * Get an image from a URL or file path
     *
     * @param string $url Url or path to local file
     * @return GDImageIdentifier
     */
    public function loadImage($path)
    {

        if (!$this->localFileExists($path) && !$this->isUrl($path)) {
            throw new \LogicException('Supplied URL / path is invalid.');
        }

        $this->image = imagecreatefromstring(file_get_contents($path));

        return $this;
    }

    private function isUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    private function localFileExists($path) {
        return file_exists($path);
    }

    /**
     * Generates svg from raster
     *
     * @param GDImageIdentifier $img Raster image to convert to svg
     * @return string                   SVG xml
     */
    public function generateSVG() {

        $width = imagesx($this->image); // image width
        $height = imagesy($this->image); // image height

        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"crispEdges\">";
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y = $y + $number_of_consecutive_pixels) {
                $color_at_position = imagecolorat($this->image, $x, $y);
                $number_of_consecutive_pixels = 1;

                while (
                    ($y + $number_of_consecutive_pixels < $height) &&
                    ($color_at_position == imagecolorat($this->image, $x, ($y + $number_of_consecutive_pixels)))
                ) {
                    $number_of_consecutive_pixels++;
                }

                $rgb = imagecolorsforindex($this->image, $color_at_position);
                $color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

                if ($rgb["alpha"] && ($rgb["alpha"] < 128)) {
                    $alpha = (128 - $rgb["alpha"]) / 128;
                    $color .= "\" fill-opacity=\"$alpha";
                }

                $svg .= "<rect width=\"1\" x=\"$x\" height=\"$number_of_consecutive_pixels\" y=\"$y\" fill=\"$color\"/>\n";
            }
        }

        $svg .= '</svg>';
        return $svg;

    }

}


$url = 'meyer.png';

$converter = new px2svg();
//header('Content-type: image/svg+xml');
echo $converter->loadImage($url)->generateSVG();