<?php

namespace Netwerkstatt\ImageToolkit\Tests;

use Netwerkstatt\ImageToolkit\Extension\SvgPlaceholder;
use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\SapphireTest;

class SvgPlaceholderTest extends SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();
        TestAssetStore::activate('SvgPlaceholderTest');
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();
        parent::tearDown();
    }

    public function testSvgPlaceholderReturnsDataUri()
    {
        $image = Image::create();
        $image->setFromLocalFile(__DIR__ . '/../images/example-image.jpg', 'test-image.jpg');
        $image->write();

        $dataUri = $image->SvgPlaceholder();

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);

        $svg = base64_decode(substr($dataUri, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('feGaussianBlur', $svg);
        $this->assertStringContainsString('data:image/', $svg);
    }

    public function testSvgPlaceholderPreservesDimensions()
    {
        $image = Image::create();
        $image->setFromLocalFile(__DIR__ . '/../images/example-image.jpg', 'test-image.jpg');
        $image->write();

        $dataUri = $image->SvgPlaceholder();
        $svg = base64_decode(substr($dataUri, strlen('data:image/svg+xml;base64,')));

        $width = (int)$image->getWidth();
        $height = (int)$image->getHeight();

        $this->assertStringContainsString(sprintf('width="%d"', $width), $svg);
        $this->assertStringContainsString(sprintf('height="%d"', $height), $svg);
        $this->assertStringContainsString(sprintf('viewBox="0 0 %d %d"', $width, $height), $svg);
    }

    public function testSvgPlaceholderIsCached()
    {
        $image = Image::create();
        $image->setFromLocalFile(__DIR__ . '/../images/example-image.jpg', 'test-image.jpg');
        $image->write();

        $first = $image->SvgPlaceholder();
        $second = $image->SvgPlaceholder();

        $this->assertNotNull($first);
        $this->assertEquals($first, $second);
    }

    public function testLQIPAlias()
    {
        $image = Image::create();
        $image->setFromLocalFile(__DIR__ . '/../images/example-image.jpg', 'test-image.jpg');
        $image->write();

        $this->assertEquals($image->SvgPlaceholder(), $image->LQIP());
    }

    public function testSvgPlaceholderReturnsNullForMissingImage()
    {
        $image = new Image();
        $image->setFilename('missing-image.jpg');

        $this->assertNull($image->SvgPlaceholder());
    }
}
