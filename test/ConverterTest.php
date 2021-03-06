<?php

namespace Px2svg\Test;

use PHPUnit_Framework_TestCase;
use Px2svg\Converter;

/**
 * @group components
 */
class ConverterTest extends PHPUnit_Framework_TestCase
{
    private $converter;

    public function setUp()
    {
        $this->converter = new Converter();
    }

    public function tearDown()
    {
        $file = __DIR__.'/res.svg';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testLoadImage()
    {
        $expected = __DIR__.'/gmail-bozo-tag.gif';
        $this->converter->loadImage($expected);
        $this->assertSame($expected, $this->converter->getCurrentImagePath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadImageFailed()
    {
        $this->converter->loadImage('/fsdfqds/fsd');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testConvertingWrongImage()
    {
        $this->converter->loadImage(__FILE__)->toXML();
    }

    public function testThreshold()
    {
        $expected = 28;
        $this->assertSame(0, $this->converter->getThreshold());
        $this->converter->setThreshold($expected);
        $this->assertSame($expected, $this->converter->getThreshold());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThresholdFailed()
    {
        $this->converter->setThreshold(-1);
    }

    public function testToXML()
    {
        $this->converter->loadImage(__DIR__.'/test-vertical.png');
        $res = $this->converter->toXML();
        $this->assertInstanceOf('\DOMDocument', $res);
        $this->converter = null;
    }

    public function testThresholdWithDOM()
    {
        $this->converter->loadImage(__DIR__.'/darth_vader.png');

        $this->converter->setThreshold(1);
        $res = $this->converter->toXML()->getElementsByTagName('rect')->length;

        $this->converter->setThreshold(253);
        $res2 = $this->converter->toXML()->getElementsByTagName('rect')->length;

        $this->assertNotEquals($res, $res2);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testToFailed()
    {
        $res = $this->converter->toXML();
        $this->assertInstanceOf('\DOMDocument', $res);
    }

    public function testGenerateSVGOutput()
    {
        $this->converter->loadImage(__DIR__.'/test-vertical.png');
        $res = $this->converter->generateSVG();
        $this->assertContains('<svg xmlns="http://www.w3.org/2000/svg" shape-rendering="crispEdges">', $res);
    }

    public function testSaveSvg()
    {
        $this->converter->loadImage(__DIR__.'/red-nose.gif');
        $res = $this->converter->saveSVG(__DIR__.'/res.svg');
        $this->assertInternalType('int', $res);
    }
}
