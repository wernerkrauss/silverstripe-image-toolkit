<?php

namespace Netwerkstatt\ImageToolkit\Extension;

use Intervention\Image\Interfaces\ImageInterface;
use Netwerkstatt\ImageToolkit\ImageToolkitHelper;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

/**
 * Extension for Image class to provide watermarking functionality
 *
 * @property Image|DBFile|Watermark $owner
 */
class Watermark extends Extension
{
    use Configurable;

    /**
     * Default watermark logo path (e.g. themes/mytheme:images/watermark.png)
     * @var string|null
     */
    private static ?string $default_watermark_logo = null;

    /**
     * Default watermark opacity (0-1)
     * @var float
     */
    private static float $default_watermark_opacity = 1.0;

    /**
     * Default watermark position (bottom-right, bottom-left, top-right, top-left, center)
     * @var string
     */
    private static string $default_watermark_position = 'bottom-right';

    /**
     * Default watermark padding (pixels or percentage, e.g. 20 or '5%')
     * @var string
     */
    private static string $default_watermark_padding = '5%';

    /**
     * Default watermark width (percentage of background image, e.g. '25%')
     * @var string
     */
    private static string $default_watermark_width = '25%';

    /**
     * Watermark the image with another image (logo)
     *
     * @param Image|string|null $logo The logo to use as watermark (Image object, path, or null for default)
     * @param string|null $position bottom-right, bottom-left, top-right, top-left, center (or null for default)
     * @param int|string|null $padding Padding from the edges in pixels or percentage (or null for default)
     * @param float|null $opacity Opacity from 0 to 1 (or null for default)
     * @param string|null $width The width of the watermark as percentage of the background (or null for default)
     * @param int|null $bgWidth Explicit background width (for resizing within manipulation)
     * @param int|null $bgHeight Explicit background height (for resizing within manipulation)
     * @return AssetContainer|null
     */
    public function Watermarked($logo = null, ?string $position = null, $padding = null, ?float $opacity = null, ?string $width = null, ?int $bgWidth = null, ?int $bgHeight = null)
    {
        $watermark = $this->getWatermarkLogo($logo);
        if (!$watermark) {
            return $this->getOwner();
        }
        $position ??= (string)$this->config()->get('default_watermark_position');
        $padding ??= $this->config()->get('default_watermark_padding');
        $opacity ??= (float)$this->config()->get('default_watermark_opacity');
        $width ??= (string)$this->config()->get('default_watermark_width');

        $targetWidth = $bgWidth ?: $this->getOwner()->getWidth();
        $targetHeight = $bgHeight ?: $this->getOwner()->getHeight();

        $variantName = ImageToolkitHelper::generateVariantName($this->getOwner(), 'Watermarked', [$watermark, $position, $padding, $opacity, $width, $targetWidth, $targetHeight]);

        /** @var AssetContainer|null $manipulated */
        $manipulated = $this->getOwner()->manipulateImage(
            $variantName,
            function (Image_Backend $backend) use ($watermark, $position, $padding, $opacity, $width, $targetWidth, $targetHeight) {
                $clone = clone $backend;
                $resource = $clone->getImageResource();

                if ($resource instanceof ImageInterface) {
                    if ($resource->width() !== $targetWidth || $resource->height() !== $targetHeight) {
                        $resource->cover($targetWidth, $targetHeight);
                    }

                    $watermarkResource = $this->resolveWatermarkResource($watermark, $resource->width(), $width);

                    if ($watermarkResource) {
                        $absPaddingX = $this->calculatePadding($padding, $resource->width());
                        $absPaddingY = $this->calculatePadding($padding, $resource->height());

                        $resource->place($watermarkResource, $position, $absPaddingX, $absPaddingY, $opacity * 100);

                        if (is_resource($watermarkResource)) {
                            fclose($watermarkResource);
                        }
                    }
                }

                return $clone;
            }
        );

        return $manipulated;
    }

    /**
     * Alias for Watermarked()
     */
    public function Watermark($logo = null, ?string $position = null, $padding = null, ?float $opacity = null, ?string $width = null)
    {
        return $this->Watermarked($logo, $position, $padding, $opacity, $width);
    }

    /**
     * Resolves the watermark logo. Allows overriding via extension hook.
     */
    public function getWatermarkLogo($logo = null)
    {
        if (!$logo) {
            $logo = $this->config()->get('default_watermark_logo');
        }

        if (is_numeric($logo) && (int)$logo > 0) {
            $logo = Image::get()->byID($logo);
        }

        $this->getOwner()->invokeWithExtensions('updateWatermarkLogo', $logo);

        return $logo;
    }

    protected function calculatePadding($padding, int $dimension): int
    {
        if (is_string($padding) && str_contains($padding, '%')) {
            $percentage = (float)str_replace('%', '', $padding);
            return (int)round($dimension * ($percentage / 100));
        }

        return (int)$padding;
    }

    protected function resolveWatermarkResource($watermark, int $bgWidth, string $width)
    {
        $percentage = (float)str_replace('%', '', $width);
        $targetLogoWidth = (int)($bgWidth * ($percentage / 100));

        if ($targetLogoWidth <= 0) {
            return null;
        }

        if ($watermark instanceof Image) {
            /** @var AssetContainer $resizedLogo */
            $resizedLogo = $watermark->ScaleWidth($targetLogoWidth);
            if ($resizedLogo->exists()) {
                return $resizedLogo->getStream();
            }
        }

        if (is_string($watermark)) {
            $path = ModuleResourceLoader::singleton()->resolvePath($watermark);
            if ($path) {
                $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . $path;
                if (file_exists($fullPath)) {
                    return $fullPath;
                }
            }
        }

        return null;
    }
}
