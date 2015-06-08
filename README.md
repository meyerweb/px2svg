# px2svg

Turning raster images into SVG files, one pixel at a time.  Yes, really.


## What?

The PHP accepts a raster image (GIF, PNG, JPEG, that sort of thing) and creates an SVG image that recreates the raster image.  It does this by drawing filled rectangles to recreate the pixels in the image.  In many cases, this is literally a 1-by-1 rectangle, but it will check for runs of similar color (similar to GIF compression) and one rectangle per run.  It checks both horizontal and vertical runs to see which approach is more efficient, and returns the better option.

The script requires [GD](http://php.net/manual/en/image.installation.php).


## Why?

There are situations where people want to take small bitmaps—think primary-color buttons or badges—and make them scalable while still preserving their 8-bit appearance.  See, for example, [Joe Crawford’s post about upscaling a minitag](http://artlung.com/smorgasborg/image-upsizing-with-svg/).  For those cases, this script will enable a quick conversion to SVG with at least some minimal attempts at optimization.

This all originally started as a one-off experiment and a bit of a jape.  You can see the original at [flaming-shame](https://github.com/meyerweb/flaming-shame), if you fancy a laugh.

## Who?

[Eric Meyer](http://meyerweb.com/), sometime CSS guy.

[Amelia Bellamy-Royds](https://github.com/AmeliaBR/), sometime SVG gal, added the check for runs of constant color and alpha transparency support.

[Robin Cafolla](https://github.com/robincafolla) made the script command-line usable and encapsulated for use in other code bases, and added posterization.

[Neal Brooks](https://github.com/nealio82) thoroughly refactored the code and removed curl dependency.

## Documentation

### Installation

Using any PSR-4 compatible autoloader.

### Using the library

Converting a Image into a SVG and directly output the corresponding SVG

```php

use Px2svg\Converter;

$converter = new Converter();
$converter->loadImage('/path/to/my/image.gif');
$converter->setThreshold(80);
header('Content-Type: text/xml');
$res = $converter->generateSVG();
```

Converting a Image into a SVG and saving the SVG to a file

```php

use Px2svg\Converter;

$converter = new Converter();
$converter->loadImage('/path/to/my/image.gif');
$converter->setThreshold(80);
$res = $converter->saveSVG('/path/to/the/save.svg');
```

If you need to further manipulate the SVG then you better use `Converter::toXML()` . This method will return a PHP `DOMDocument` object.

```php

use Px2svg\Converter;

$converter = new Converter();
$converter->loadImage('/path/to/my/image.gif');
$converter->setThreshold(80);
$res = $converter->toXML();
//$res is a DOMDocument object
```

### Using the command line tool

```
$ php app/console --threshold=80 --output="/path/to/the/save.svg" '/path/to/my/image.gif'
```

The file `/path/to/my/image.gif` will be converted to SVG and save to `/path/to/the/save.svg`. It will use the threshold value to determines whether similar colors are treated as the same color when creating the SVG vectors.

If an error occurs you will be notice. You can of course use the help documentation by calling

```
$ php app/console --help
```

Happy coding!