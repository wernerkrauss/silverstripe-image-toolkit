<?php

namespace Netwerkstatt\ImageToolkit;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\DBFile;

class ImageToolkitHelper
{
    /**
     * Generates a unique variant name for the manipulation
     *
     * @param Image|DBFile $owner
     * @param string $method
     * @param array $args
     * @return string
     */
    public static function generateVariantName(Image|DBFile $owner, string $method, array $args = []): string
    {
        $parts = [$method];
        foreach ($args as $arg) {
            $parts[] = self::processArgument($arg);
        }

        $parts[] = $owner->getWidth() . 'x' . $owner->getHeight();

        return implode('_', $parts);
    }

    protected static function processArgument(mixed $arg): string
    {
        if (is_object($arg)) {
            if (isset($arg->ID) && $arg->ID > 0) {
                return (string)$arg->ID;
            }

            if ($arg instanceof Image || $arg instanceof DBFile) {
                return md5($arg->getFilename() ?: spl_object_hash($arg));
            }

            return md5(spl_object_hash($arg));
        }

        if (is_array($arg)) {
            $processedArray = array_map(self::processArgument(...), $arg);
            return md5(json_encode($processedArray));
        }

        return str_replace(['%', '.', '#'], ['p', '', ''], (string)$arg);
    }
}
