<?php
/**
 * Flaming Shame Raster to SVG converter
 *
 * @author  Eric Meyer, Amelia Bellamy-Royds, Robin Cafolla, Neal Brooks
 * @arg	 string url	  Takes a single string url or path to local image to
 *						  convert from raster to SVG.
 */
class px2svg
{
	private $image;

    /**
     * Similarity between colours.
     *
     * Threshold is compared against the distance between two colors in 3
     * dimensions. e.g RGB( 0, 0, 255 ) and RGB( 0, 0, 0 ) would be merged
     * with a threshold greater than 255.
     *
     * @var float $threshold
     */
    private $threshold = 0;

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
     * Check if two colors are within the color tolerance as determined by
     * threshold.
     *
     * @param array $colorA     Color array in form [ red: int, green: int, blue: int ]
     * @param array $colorB     Color array in form [ red: int, green: int, blue: int ]
     * @param float $threshold  Optional. Tolerance to check within.
     * @return bool             True if the colours are within the tolerance,
     *                          false if they are outside the tolerance
     */
    public function checkThreshold( $colorA, $colorB, $threshold = null ) {
        $threshold =  ( $threshold === null ) ? $this->threshold : $threshold;

        $distance = sqrt(
            pow( $colorB['red'] - $colorA['red'], 2) +
            pow( $colorB['green'] - $colorA['green'], 2) +
            pow( $colorB['blue'] - $colorA['blue'], 2)
        );

        if ( $distance < $threshold ) {
            return true;
        }

        return false;
    }

	/**
	 * Generates svg from raster
	 *
	 * @param GDImageIdentifier $img Raster image to convert to svg
	 * @return string				   SVG xml
	 */
	public function generateSVG() {
		$width = imagesx($this->image); // image width
		$height = imagesy($this->image); // image height

		$svgv = "";
		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y = $y + $number_of_consecutive_pixels) {
                $color_at_position = imagecolorsforindex(
                    $this->image,
                    imagecolorat($this->image, $x, $y)
                );

				$number_of_consecutive_pixels = 1;

				while (
					($y + $number_of_consecutive_pixels < $height)
				) {
                    $next_color = imagecolorsforindex(
                        $this->image,
                        imagecolorat($this->image, $x, ($y + $number_of_consecutive_pixels))
                    );

                    if ( $this->checkThreshold( $color_at_position, $next_color ) ) {
                        $number_of_consecutive_pixels++;
                    }
                    else {
                        break;
                    }
				}

				$rgb = $color_at_position;
				$color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

				if ($rgb["alpha"] && ($rgb["alpha"] < 128)) {
					$alpha = (128 - $rgb["alpha"]) / 128;
					$color .= "\" fill-opacity=\"$alpha";
				}

				$svgv .= "<rect x=\"$x\" y=\"$y\" width=\"1\" height=\"$number_of_consecutive_pixels\" fill=\"$color\"/>\n";
			}
		}

		$number_of_consecutive_pixels = 1; //reset number of consecutive pixels
		$svgh = "";
		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x = $x + $number_of_consecutive_pixels) {
				$color_at_position = imagecolorat($this->image, $x, $y);
				$number_of_consecutive_pixels = 1;

				while(
					($x + $number_of_consecutive_pixels < $width) &&
					($color_at_position == imagecolorat($this->image, ($x + $number_of_consecutive_pixels), $y))
				) {
					$number_of_consecutive_pixels++;
				}

				$rgb = imagecolorsforindex($this->image, $color_at_position);
				$color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

				if ($rgb["alpha"] && ($rgb["alpha"] < 128 )) {
					$alpha = (128 - $rgb["alpha"]) / 128;
					$color .= "\" fill-opacity=\"$alpha";
				}

				$svgh .= "<rect x=\"$x\" y=\"$y\" width=\"$number_of_consecutive_pixels\" height=\"1\" fill=\"$color\"/>\n";
			}
		}

		if (strlen($svgh) < strlen($svgv)) $svg = $svgh; else $svg = $svgv;

		return "<svg xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"crispEdges\">\n" . $svg . "</svg>";
	}

    /**
     * Get threshold
     *
     * @return float Current threshold value
     */
    public function getThreshold( $threshold ){
        return $this->threshold;
    }

    /**
     * Set threshold
     *
     * @param float $threshold  New threshold value
     */
    public function setThreshold( $threshold ){
        $this->threshold = $threshold;
    }
}
