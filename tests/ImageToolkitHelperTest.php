<?php

namespace Netwerkstatt\ImageToolkit\Tests;

use Netwerkstatt\ImageToolkit\ImageToolkitHelper;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\SapphireTest;

class ImageToolkitHelperTest extends SapphireTest
{
    public function testGenerateVariantName()
    {
        $image = new Image();
        // Mock dimensions if possible, but Image might need a file for that.
        // For testing the string logic, a simple Image instance should suffice if getWidth() returns 0 or similar.
        
        $variantName = ImageToolkitHelper::generateVariantName($image, 'Test', ['arg1', '10%', 0.5]);
        
        $this->assertStringStartsWith('Test_', $variantName);
        $this->assertStringContainsString('arg1', $variantName);
        $this->assertStringContainsString('10p', $variantName);
        $this->assertStringContainsString('05', $variantName);
    }

    public function testGenerateVariantNameWithHexColor()
    {
        $image = new Image();
        $variantName = ImageToolkitHelper::generateVariantName($image, 'ColorOverlay', ['#3b82f6', 0.4]);

        $this->assertStringContainsString('3b82f6', $variantName);
        $this->assertStringNotContainsString('#', $variantName);
    }

    public function testGenerateVariantNameWithObject()
    {
        $image = new Image();
        $logo = new Image();
        // This used to cause "var_export does not handle circular references" or similar issues
        // because of serialize()
        $variantName = ImageToolkitHelper::generateVariantName($image, 'Watermark', [$logo]);
        
        $this->assertStringStartsWith('Watermark_', $variantName);
    }
}
