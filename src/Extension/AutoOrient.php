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
 * Extension for Image class to provide AutoOrient functionality
 *
 * @property Image|DBFile|AutoOrient $owner
 */
class AutoOrient extends Extension
{
    /**
     * Corrects the image orientation based on EXIF data
     *
     * @return AssetContainer|null
     */
    public function AutoOriented()
    {
        $variantName = ImageToolkitHelper::generateVariantName($this->getOwner(), 'AutoOrient');

        /** @var AssetContainer|null $manipulated */
        $manipulated = $this->getOwner()->manipulateImage(
            $variantName,
            function (Image_Backend $backend) {
                $clone = clone $backend;
                $resource = $clone->getImageResource();

                if ($resource instanceof ImageInterface) {
                    $resource->orient();
                }

                return $clone;
            }
        );

        return $manipulated;
    }

    /**
     * Alias for AutoOriented()
     *
     * @return AssetContainer|null
     */
    public function AutoOrient()
    {
        return $this->AutoOriented();
    }
}
