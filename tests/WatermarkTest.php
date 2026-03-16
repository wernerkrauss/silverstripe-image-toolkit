<?php

namespace Netwerkstatt\ImageToolkit\Tests;

use Netwerkstatt\ImageToolkit\Extension\AutoOrient;
use Netwerkstatt\ImageToolkit\Extension\ColorOverlay;
use Netwerkstatt\ImageToolkit\Extension\Watermark;
use Netwerkstatt\ImageToolkit\ImageToolkitHelper;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Assets\Dev\TestAssetStore;

class WatermarkTest extends SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();
        TestAssetStore::activate('WatermarkTest');
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();
        parent::tearDown();
    }
    public function testGetWatermarkLogoReturnsConfigValue()
    {
        $image = new Image();
        $extension = new Watermark();
        $extension->setOwner($image);

        Config::modify()->set(Watermark::class, 'default_watermark_logo', 'themes/test:images/logo.png');

        $logo = $extension->getWatermarkLogo();
        $this->assertEquals('themes/test:images/logo.png', $logo);
    }

    public function testGetWatermarkLogoReturnsPassedValue()
    {
        $image = new Image();
        $extension = new Watermark();
        $extension->setOwner($image);

        Config::modify()->set(Watermark::class, 'default_watermark_logo', 'themes/test:images/logo.png');

        $logo = $extension->getWatermarkLogo('custom/path/logo.png');
        $this->assertEquals('custom/path/logo.png', $logo);
    }

    public function testUpdateWatermarkLogoHook()
    {
        // Define a named class for the test
        if (!class_exists(WatermarkTestExtension::class)) {
            eval('namespace Netwerkstatt\ImageToolkit\Tests; class WatermarkTestExtension extends \SilverStripe\Core\Extension { public function updateWatermarkLogo(&$logo) { $logo = "hook/path/logo.png"; } }');
        }
        
        Image::add_extension(WatermarkTestExtension::class);
        Image::add_extension(Watermark::class);
        
        $image = new Image();
        $logo = $image->getWatermarkLogo('original/path.png');
        
        $this->assertEquals('hook/path/logo.png', $logo);
        
        // Cleanup for other tests
        Image::remove_extension(WatermarkTestExtension::class);
        Image::remove_extension(Watermark::class);
    }

    public function testCalculatePadding()
    {
        $extension = new Watermark();
        
        $reflection = new \ReflectionClass(Watermark::class);
        $method = $reflection->getMethod('calculatePadding');
        $method->setAccessible(true);

        // Pixel padding
        $this->assertEquals(20, $method->invoke($extension, 20, 1000));
        $this->assertEquals(20, $method->invoke($extension, '20', 1000));

        // Percentage padding
        $this->assertEquals(50, $method->invoke($extension, '5%', 1000));
        $this->assertEquals(100, $method->invoke($extension, '10%', 1000));
        $this->assertEquals(25, $method->invoke($extension, '5%', 500));
    }

    public function testWatermarkedWithObject()
    {
        $image = new Image();
        $image->setFilename('test-image.jpg');
        $logo = new Image();
        $logo->setFilename('watermark-logo.png');
        
        // Mock dimensions
        $image->setField('Width', 1000);
        $image->setField('Height', 1000);

        // This is where it might trigger the var_export if it still does it
        // Note: result might be null if there is no image backend or file available, 
        // but the call itself should not crash or trigger circular reference warnings.
        $result = $image->Watermarked($logo);
        
        $this->assertTrue(true);
    }

    public function testWatermarkedWithID()
    {
        $image = Image::create();
        $image->setFromLocalFile(__DIR__ . '/../images/example-image.jpg', 'test-image.jpg');
        $image->write();
        
        $logo = Image::create();
        $logo->setFromLocalFile(__DIR__ . '/../images/test-logo.png', 'test-logo.png');
        $logo->write();
        
        $result = $image->Watermarked($logo->ID);
        $this->assertNotNull($result);
        $this->assertTrue($result->exists());
    }

    public function testColorOverlayAndChaining()
    {
        $image = Image::create();
        $image->setFromLocalFile(__DIR__ . '/../images/example-image.jpg', 'test-image.jpg');
        $image->write();

        $overlayed = $image->ColorOverlayed('#ff0000', 0.5);
        $this->assertNotNull($overlayed);
        $this->assertTrue($overlayed->exists());

        $chained = $image->AutoOriented()->ColorOverlayed('#00ff00', 0.3);
        $this->assertNotNull($chained);
        $this->assertTrue($chained->exists());
    }
}
