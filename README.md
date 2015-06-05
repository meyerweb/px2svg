# px2svg

Turning raster images into SVG files, one pixel at a time.  Yes, really.


## What?

The PHP accepts a raster image (GIF, PNG, JPEG, that sort of thing) and creates an SVG image that recreates the raster image.  It does this by drawing filled rectangles to recreate the pixels in the image.  In many cases, this is literally a 1-by-1 rectangle, but thanks to [Amelia Bellamy-Royds](https://github.com/AmeliaBR/), the code can do some basic color-run optimization.

The script requires [GD](http://php.net/manual/en/image.installation.php) and [curl](http://php.net/manual/en/curl.installation.php).

## Why?

There actually are some reasons.  I’ll document them soon.


## Who?

[Eric Meyer](http://meyerweb.com/), sometime CSS guy.  Eric A. Meyer if you’re nasty.

[Amelia Bellamy-Royds](https://github.com/AmeliaBR/), sometime SVG gal, added the check for runs of constant color, alpha transparency support, and made the output a valid, responsive SVG file.  Because she refused to accept that an SVG could be a less optimal image file format than a Windows .bmp bitmap file.
