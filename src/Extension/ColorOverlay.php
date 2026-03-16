<?php

namespace Netwerkstatt\ImageToolkit\Extension;

use Intervention\Image\Interfaces\ImageInterface;
use Netwerkstatt\ImageToolkit\ImageToolkitHelper;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Extension;

/**
 * Extension for Image class to provide ColorOverlay functionality
 *
 * @property Image|DBFile|ColorOverlay $owner
 */
class ColorOverlay extends Extension
{
    /**
     * Adds a color overlay to the image
     *
     * @param string $color Color in hex, rgb or rgba
     * @param float $opacity Opacity from 0 to 1
     * @return AssetContainer|null
     */
    public function ColorOverlayed(string $color, float $opacity = 0.5)
    {
        $variantName = ImageToolkitHelper::generateVariantName($this->getOwner(), 'ColorOverlay', [$color, $opacity]);

        /** @var AssetContainer|null $manipulated */
        $manipulated = $this->getOwner()->manipulateImage(
            $variantName,
            function (Image_Backend $backend) use ($color, $opacity) {
                $clone = clone $backend;
                $resource = $clone->getImageResource();

                if ($resource instanceof ImageInterface) {
                    $overlay = $resource->driver()->createImage($resource->width(), $resource->height());
                    $overlay->fill($color);

                    $resource->place($overlay, 'center', 0, 0, (int)($opacity * 100));
                }

                return $clone;
            }
        );

        return $manipulated;
    }

    /**
     * Alias for ColorOverlayed()
     *
     * @param string $color
     * @param float $opacity
     * @return AssetContainer|null
     */
    public function ColorOverlay(string $color, float $opacity = 0.5)
    {
        return $this->ColorOverlayed($color, $opacity);
    }
}
