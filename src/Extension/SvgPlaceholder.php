<?php

namespace Netwerkstatt\ImageToolkit\Extension;

use Intervention\Image\Interfaces\ImageInterface;
use Netwerkstatt\ImageToolkit\ImageToolkitHelper;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;

/**
 * Extension providing an inline SVG placeholder (LQIP) for images.
 *
 * Renders a tiny blurred version of the image embedded in an SVG container
 * and returns it as a data URI, so it can be used directly as the src of an
 * <img> tag without an additional request. The SVG preserves the original
 * aspect ratio, which prevents layout shift while the full image loads.
 *
 * @property Image|DBFile|SvgPlaceholder $owner
 */
class SvgPlaceholder extends Extension
{
    use Configurable;

    /**
     * Default width of the embedded thumbnail in pixels
     * @var int
     */
    private static int $default_thumb_width = 24;

    /**
     * Default blur strength (feGaussianBlur stdDeviation) applied in the SVG
     * @var float
     */
    private static float $default_blur = 1.2;

    /**
     * Cache the generated data URI so repeated renders (e.g. static publishing)
     * do not regenerate the thumbnail.
     * @var bool
     */
    private static bool $enable_cache = true;

    /**
     * Return an inline SVG placeholder (LQIP) for the image as a data URI.
     *
     * @param int|null $thumbWidth Width of the embedded thumbnail in pixels (or null for default)
     * @param float|null $blur Blur strength applied in the SVG (or null for default)
     * @return string|null Data URI (data:image/svg+xml;base64,...) or null if the image cannot be processed
     */
    public function SvgPlaceholder(?int $thumbWidth = null, ?float $blur = null): ?string
    {
        $owner = $this->getOwner();
        if (!$owner->exists()) {
            return null;
        }

        $thumbWidth ??= (int)$this->config()->get('default_thumb_width');
        $blur ??= (float)$this->config()->get('default_blur');

        $cacheKey = null;
        $cache = null;
        if ($this->config()->get('enable_cache')) {
            $cache = Injector::inst()->get(CacheInterface::class . '.ImageToolkitSvgPlaceholder');
            $cacheKey = md5(implode('|', [$owner->getHash(), $thumbWidth, $blur]));
            $cached = $cache->get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $variantName = ImageToolkitHelper::generateVariantName($owner, 'SvgPlaceholder', [$thumbWidth]);

        /** @var AssetContainer|null $variant */
        $variant = $owner->manipulateImage(
            $variantName,
            function (Image_Backend $backend) use ($thumbWidth) {
                $clone = clone $backend;
                $resource = $clone->getImageResource();

                if ($resource instanceof ImageInterface) {
                    $resource->scaleDown($thumbWidth);
                }

                return $clone;
            }
        );

        if (!$variant || !$variant->exists()) {
            return null;
        }

        $width = (int)$owner->getWidth();
        $height = (int)$owner->getHeight();
        $mime = $variant->getMimeType() ?: 'image/jpeg';

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">' .
            '<filter id="b"><feGaussianBlur stdDeviation="%s"/></filter>' .
            '<image width="100%%" height="100%%" filter="url(#b)" ' .
            'href="data:%s;base64,%s" preserveAspectRatio="xMidYMid slice"/></svg>',
            $width,
            $height,
            $width,
            $height,
            $blur,
            $mime,
            base64_encode($variant->getString())
        );

        $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        $owner->invokeWithExtensions('updateSvgPlaceholder', $dataUri);

        if ($cacheKey !== null && $cache !== null) {
            $cache->set($cacheKey, $dataUri);
        }

        return $dataUri;
    }

    /**
     * Alias for SvgPlaceholder()
     */
    public function LQIP(?int $thumbWidth = null, ?float $blur = null): ?string
    {
        return $this->SvgPlaceholder($thumbWidth, $blur);
    }
}
