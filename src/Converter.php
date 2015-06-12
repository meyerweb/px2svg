<?php

namespace Px2svg;

use DOMDocument;
use DOMImplementation;
use InvalidArgumentException;

/**
 * Flaming Shame Raster to SVG converter
 *
 * @author  Eric Meyer, Amelia Bellamy-Royds, Robin Cafolla, Neal Brooks
 * @arg  string url   Takes a single string url or path to local image to
 *                        convert from raster to SVG.
 */
class Converter
{
    /**
     * Image source path
     *
     * @var string
     */
    protected $path;

    /**
     * GDImageIdentifier
     *
     * @var resource
     */
    protected $image;

    /**
     * Image pixel width
     *
     * @var int
     */
    protected $width;

    /**
     * Image pixel $this->height
     *
     * @var int
     */
    protected $height;

    /**
     * Similarity between colours.
     *
     * Threshold is compared against the distance between two colors in 3
     * dimensions. e.g RGB( 0, 0, 255 ) and RGB( 0, 0, 0 ) would be merged
     * with a threshold greater than 255.
     *
     * @var float $threshold
     */
    protected $threshold = 0;

    /**
     * Destruct the current instance
     */
    public function __destruct()
    {
        $this->flushImageSettings();
    }

    /**
     * Remove image settings
     */
    protected function flushImageSettings()
    {
        if (! is_null($this->image)) {
            imagedestroy($this->image);
            $this->image  = null;
            $this->width  = 0;
            $this->height = 0;
        }
    }

    /**
     * Get threshold value
     *
     * @return float Current threshold value
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Set threshold value
     *
     * @param float $threshold
     */
    public function setThreshold($threshold)
    {
        if ($threshold <= 0 || $threshold > 255) {
            throw new InvalidArgumentException(
                'the submitted threshold is invalid, value must be between > 0 and < 255'
            );
        }
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Get an image from a URL or file path
     *
     * @param string $path url or path to a file
     *
     * @return static
     */
    public function loadImage($path)
    {
        if (! is_readable($path) && ! filter_var($path, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf("Supplied URL / path is invalid : '%s'", $path));
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Return the current path
     *
     * @return string
     */
    public function getCurrentImagePath()
    {
        return $this->path;
    }

    /**
     * Generates svg from raster
     *
     * @return string
     */
    public function generateSVG()
    {
        $svg = $this->toXML();

        return $svg->saveXML($svg->documentElement);
    }

    /**
     * Generates svg from raster and save to a given file
     *
     * @param string $path Path where to save the generated SVG
     *
     * @return int
     */
    public function saveSVG($path)
    {
        return $this->toXML()->save($path);
    }

    /**
     * Generates svg from raster Horizontally
     *
     * @return DOMDocument
     */
    public function toXML()
    {
        $this->setImageSettings();
        $svgh = $this->generateHorizontalSVG();
        $svg  = $this->generateVerticalSVG();
        if ($svgh->getElementsByTagName('rect')->length < $svg->getElementsByTagName('rect')->length) {
            $svg = $svgh;
        }
        $this->flushImageSettings();

        return $svg;
    }

    /**
     * initialize Image settings
     *
     * @throws InvalidArgumentException if the image is not yet loaded
     */
    protected function setImageSettings()
    {
        $this->flushImageSettings();
        if (empty($this->path)) {
            throw new InvalidArgumentException('You must use the `loadImage` method first');
        }
        $this->image  = imagecreatefromstring(file_get_contents($this->path));
        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    /**
     * Generates svg from raster Horizontally
     *
     * @return DOMDocument
     */
    protected function generateHorizontalSVG()
    {
        $svg = $this->getTemplateSvg();
        for ($y = 0; $y < $this->height; $y++) {
            $number_of_consecutive_pixels = 1;
            for ($x = 0; $x < $this->width; $x = $x + $number_of_consecutive_pixels) {
                $number_of_consecutive_pixels = $this->createHorizontalRectangle($svg, $x, $y);
            }
        }

        return $svg;
    }

    /**
     * Create a Rect Element for Horizontal SVG
     *
     * @param DOMDocument $svg
     * @param int         $x    X coordonate
     * @param int         $y    Y coordonate
     *
     * @return int the number of consecutive pixels
     */
    protected function createHorizontalRectangle(DOMDocument $svg, $x, $y)
    {
        $color_at_position = imagecolorat($this->image, $x, $y);
        $width = 1;
        while (($x + $width < $this->width) && ($color_at_position == imagecolorat($this->image, ($x + $width), $y))) {
            ++$width;
        }

        $rgba = imagecolorsforindex($this->image, $color_at_position);
        $this->createRectElement($svg, $rgba, $x, $y, $width);

        return $width;
    }

    /**
     * Create a SVG rect Element
     *
     * @param DOMDocument $svg
     * @param array       $rgba  Color array in form [red: int, green: int, blue: int, alpha: int]
     * @param int         $x     X coordonate
     * @param int         $y     Y coordonate
     * @param int         $width element width
     */
    protected function createRectElement(DOMDocument $svg, array $rgba, $x, $y, $width)
    {
        $rect = $svg->createElement('rect');
        $rect->setAttribute("x", $x);
        $rect->setAttribute("y", $y);
        $rect->setAttribute("width", $width);
        $rect->setAttribute("height", 1);
        $rect->setAttribute("fill", "rgb({$rgba['red']},{$rgba['green']},{$rgba['blue']})");
        $alpha = filter_var($rgba["alpha"], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 128, 'default' => 0]
        ]);
        if ($alpha > 0) {
            $rect->setAttribute("fill-opacity", (128 - $alpha) / 128);
        }
        $svg->documentElement->appendChild($rect);
    }

    /**
     * Create a template SVG file
     *
     * @return DOMDocument
     */
    protected function getTemplateSvg()
    {
        $imp = new DOMImplementation();
        $dom = $imp->createDocument(
            null,
            'svg',
            $imp->createDocumentType(
                'svg',
                '-//W3C//DTD SVG 1.1//EN',
                'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'
            )
        );
        $dom->encoding     = 'UTF-8';
        $dom->formatOutput = true;
        $dom->documentElement->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $dom->documentElement->setAttribute('shape-rendering', 'crispEdges');

        return $dom;
    }

    /**
     * Generates svg from raster Vertically
     *
     * @return DOMDocument
     */
    protected function generateVerticalSVG()
    {
        $svg = $this->getTemplateSvg();
        for ($x = 0; $x < $this->width; ++$x) {
            $number_of_consecutive_pixels = 1;
            for ($y = 0; $y < $this->height; $y = $y + $number_of_consecutive_pixels) {
                $number_of_consecutive_pixels = $this->createVerticalRectangle($svg, $x, $y);
            }
        }

        return $svg;
    }

    /**
     * Create a Rect Element for Vertical SVG
     *
     * @param DOMDocument $svg
     * @param int         $x    X coordonate
     * @param int         $y    Y coordonate
     *
     * @return int      the number of consecutive pixels
     */
    protected function createVerticalRectangle(DOMDocument $svg, $x, $y)
    {
        $rgba  = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
        $width = 1;
        while (($y + $width) < $this->height) {
            $next_color = imagecolorsforindex(
                $this->image,
                imagecolorat($this->image, $x, ($y + $width))
            );
            if (! $this->checkThreshold($rgba, $next_color)) {
                break;
            }
            ++$width;
        }

        $this->createRectElement($svg, $rgba, $x, $y, $width);

        return $width;
    }

    /**
     * Check if two colors are within the color tolerance as determined by
     * threshold.
     *
     * @param array $colorA     Color array in form [ red: int, green: int, blue: int ]
     * @param array $colorB     Color array in form [ red: int, green: int, blue: int ]
     * @param float $threshold  Optional. Tolerance to check within.
     *
     * @return bool             True if the colours are within the tolerance,
     *                          false if they are outside the tolerance
     */
    protected function checkThreshold(array $colorA, array $colorB, $threshold = null)
    {
        $threshold = $threshold ?: $this->threshold;

        return $threshold > sqrt(
            pow($colorB['red'] - $colorA['red'], 2) +
            pow($colorB['green'] - $colorA['green'], 2) +
            pow($colorB['blue'] - $colorA['blue'], 2)
        );
    }
}
