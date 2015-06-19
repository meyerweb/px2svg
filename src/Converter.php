<?php

namespace Px2svg;

use DOMDocument;
use DOMImplementation;
use InvalidArgumentException;

/**
 * Flaming Shame Raster to SVG converter
 *
 * @author  Eric Meyer, Amelia Bellamy-Royds, Robin Cafolla, Neal Brooks
 *
 */
class Converter
{
    const DIRECTION_HORIZONTAL = 1;

    const DIRECTION_VERTICAL = 2;

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
     * Get threshold value
     *
     * @return int Current threshold value
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Set threshold value
     *
     * @param int $threshold
     */
    public function setThreshold($threshold)
    {
        $threshold = filter_var($threshold, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 255]]);
        if ($threshold === false) {
            throw new InvalidArgumentException(
                'the submitted threshold is invalid, value must be a integer between > 0 and < 255'
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
    public function getLoadedImagePath()
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
     * Generates svg from raster
     *
     * @return DOMDocument
     */
    public function toXML()
    {
        $this->setImageSettings();
        $svgh = $this->generateSvgFromRaster(self::DIRECTION_HORIZONTAL);
        $svg  = $this->generateSvgFromRaster(self::DIRECTION_VERTICAL);
        if ($svgh->getElementsByTagName('rect')->length < $svg->getElementsByTagName('rect')->length) {
            $svg = $svgh;
        }
        $this->flushImageSettings();

        return $svg;
    }

    /**
     * Create a SVG document from raster depending on
     * its direction HORIZONTALLY OR VERTICALLY
     *
     * @param int $direction horizontal OR vertical
     *
     * @return DOMDocument
     */
    protected function generateSvgFromRaster($direction)
    {
        $svg = $this->createSvgDocument();
        for ($x = 0; $x < $this->width; ++$x) {
            $number_of_consecutive_pixels = 1;
            for ($y = 0; $y < $this->height; $y = $y + $number_of_consecutive_pixels) {
                $number_of_consecutive_pixels = $this->createLine($svg, $x, $y, $direction);
            }
        }

        return $svg;
    }

    /**
     * Create a template SVG file
     *
     * @return DOMDocument
     */
    protected function createSvgDocument()
    {
        $imp = new DOMImplementation();
        $dom = $imp->createDocument(
            null,
            'svg'
        );
        // $dom->encoding     = 'UTF-8';
        $dom->formatOutput = true;
        $dom->documentElement->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $dom->documentElement->setAttribute('shape-rendering', 'crispEdges');
        $dom->documentElement->setAttribute('width', $this->width);
        $dom->documentElement->setAttribute('height', $this->height);
        $dom->documentElement->setAttribute('viewBox', '0 0 '.$this->width.' '.$this->height);

        return $dom;
    }

    /**
     * Create a line SVG
     *
     * @param DOMDocument $svg
     * @param int         $x         X coordonate
     * @param int         $y         Y coordonate
     * @param int         $direction horizontal OR vertical
     *
     * @return int      the number of consecutive pixels
     */
    protected function createLine(DOMDocument $svg, $x, $y, $direction)
    {
        $rgba  = $this->getPixelColors($x, $y);
        $delta = 1;
        while ($this->isSimilarPixel($rgba, $x, $y, $delta, $direction)) {
            ++$delta;
        }
        $this->createRectElement($svg, $rgba, $x, $y, $delta, $direction);

        return $delta;
    }

    /**
     * Create a Rect Element for SVG
     *
     * @param int $x X coordonate
     * @param int $y Y coordonate
     *
     * @return array Color array in form [red: int, green: int, blue: int, alpha: int]
     */
    protected function getPixelColors($x, $y)
    {
        return imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
    }

    /**
     * Return whether the Pixel are similar in color
     * depending on the direction
     *
     * @param array $rgba      Color array in form [red: int, green: int, blue: int, alpha: int]
     * @param int   $x         X coordonate
     * @param int   $y         Y coordonate
     * @param int   $delta     difference dimension
     * @param int   $direction horizontal OR vertical
     *
     * @return bool
     */
    protected function isSimilarPixel($rgba, $x, $y, $delta, $direction)
    {
        if ($direction == self::DIRECTION_HORIZONTAL) {
            $res = $x + $delta;

            return $res < $this->width && $this->checkThreshold($rgba, $this->getPixelColors($res, $y));
        }

        $res = $y + $delta;

        return $res < $this->height && $this->checkThreshold($rgba, $this->getPixelColors($x, $res));
    }

    /**
     * Create a SVG rect Element
     *
     * @param DOMDocument $svg
     * @param array       $rgba      Color array in form [red: int, green: int, blue: int, alpha: int]
     * @param int         $x         X coordonate
     * @param int         $y         Y coordonate
     * @param int         $width     element width
     * @param int         $direction horizontal OR vertical
     */
    protected function createRectElement(DOMDocument $svg, array $rgba, $x, $y, $width, $direction)
    {
        $rectWidth  = $width;
        $rectHeight = 1;
        if ($direction == self::DIRECTION_VERTICAL) {
            $rectWidth  = 1;
            $rectHeight = $width;
        }
        $rect = $svg->createElement('rect');
        $rect->setAttribute("x", $x);
        $rect->setAttribute("y", $y);
        $rect->setAttribute("width", $rectWidth);
        $rect->setAttribute("height", $rectHeight);
        $rect->setAttribute("fill", "rgb({$rgba['red']},{$rgba['green']},{$rgba['blue']})");
        $alpha = filter_var($rgba["alpha"], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 128]]);
        if ($alpha > 0) {
            $rect->setAttribute("fill-opacity", (128 - $alpha) / 128);
        }
        $svg->documentElement->appendChild($rect);
    }

    /**
     * Check if two colors are within the color tolerance as determined by
     * threshold.
     *
     * @param array $colorA Color array in form [ red: int, green: int, blue: int ]
     * @param array $colorB Color array in form [ red: int, green: int, blue: int ]
     *
     * @return bool true  if the colours are within the tolerance,
     *              false if they are outside the tolerance
     */
    protected function checkThreshold(array $colorA, array $colorB)
    {
        return $this->threshold > sqrt(
            pow($colorB['red'] - $colorA['red'], 2) +
            pow($colorB['green'] - $colorA['green'], 2) +
            pow($colorB['blue'] - $colorA['blue'], 2)
        );
    }
}
