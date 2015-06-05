# px2svg

Turning raster images into SVG files, one pixel at a time.  Yes, really.


## What?

The PHP accepts a raster image (GIF, PNG, JPEG, that sort of thing) and creates an SVG image that recreates the raster image.  It does this by drawing filled rectangles to recreate the pixels in the image.  In many cases, this is literally a 1-by-1 rectangle, but thanks to [Amelia Bellamy-Royds](https://github.com/AmeliaBR/), the code can do some basic color-run optimization.

The script requires [GD](http://php.net/manual/en/image.installation.php) and [curl](http://php.net/manual/en/curl.installation.php).


## Why?

There are situation where people want to take small bitmaps—think primary-color buttons or badges—and make them scalable while still preserving their 8-bit appearance.  See, for example, [Joe Crawford’s post about upscaling a minitag](http://artlung.com/smorgasborg/image-upsizing-with-svg/).  For those cases, this script will enable a quick conversion to SVG with at least some minimal attempts at optimization.

This all originally started as a one-off experiment and a bit of a jape.  You can see the original at [flaming-shame](https://github.com/meyerweb/flaming-shame), if you fancy a laugh.


## Who?

[Eric Meyer](http://meyerweb.com/), sometime CSS guy.

[Amelia Bellamy-Royds](https://github.com/AmeliaBR/), sometime SVG gal, added the check for runs of constant color, alpha transparency support, and made the output a valid, responsive SVG file.  Because she refused to accept that an SVG could be a less optimal image file format than a Windows .bmp bitmap file.
