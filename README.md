# Silverstripe Image Toolkit

This module provides a collection of image manipulations for Silverstripe CMS using Intervention Image V3. It's specifically designed for Silverstripe 6 and leverages the power of the Intervention Image library for various effects and optimizations.

## Installation

```bash
composer require netwerkstatt/silverstripe-image-toolkit
```

## Features

- **Watermark**: Add a logo or image as a watermark. Supports flexible positioning, padding, opacity, and relative sizing. Now also stable against circular references when using `Image` objects or their IDs.
- **Auto-Orient**: Automatically corrects image orientation based on EXIF data (ideal for mobile uploads).
- **Color Overlay**: Places a semi-transparent color over an image (useful for banners and hero sections).

## Configuration

The toolkit is automatically registered for `SilverStripe\Assets\Image` and `SilverStripe\Assets\Storage\DBFile`. You can customize default settings via YAML.

### Watermark Configuration

```yaml
Netwerkstatt\ImageToolkit\Extension\Watermark:
  default_watermark_logo: 'themes/your-theme:images/watermark.png'
  default_watermark_opacity: 0.5
  default_watermark_position: 'bottom-right'
  default_watermark_padding: '5%'
  default_watermark_width: '25%'
```

## Usage

In your templates or PHP code, you can use the toolkit methods on any `Image` or `DBFile` object.

### Watermark

Adds a watermark to the image. You can pass an `Image` object, an ID, or a path.

```ss
$Image.Watermark
$Image.Watermark($CustomLogo.ID, 'center', '10%', 0.5, '50%')
$Image.Watermark($CustomLogo.ID, 'bottom-right')
```

**Note:** Using the ID (`$Logo.ID`) is particularly stable as the object is only loaded when needed. However, this also prevents circular reference errors when passing whole objects.

### Auto-Orient (Alias: AutoOriented)

Corrects the orientation of an image based on its EXIF data.

```ss
$Image.AutoOrient
```

### Color Overlay (Alias: ColorOverlayed)

Applies a color overlay. Perfect for making text more readable on images.

```ss
$Image.ColorOverlay('#000000', 0.4)
```

### Chaining

All methods are "fluent" and can be combined:

```ss
$Image.AutoOrient.ScaleWidth(1200).ColorOverlay('#333', 0.5).Watermark
```

## Advanced: Watermark Logo Hook

You can dynamically change the watermark logo (e.g., from `SiteConfig`) using the `updateWatermarkLogo` hook:

```php
use SilverStripe\Core\Extension;

class MyImageExtension extends Extension
{
    public function updateWatermarkLogo(&$logo)
    {
        $siteConfig = \SilverStripe\SiteConfig\SiteConfig::current_site_config();
        if ($siteConfig->WatermarkLogoID) {
            $logo = $siteConfig->WatermarkLogo();
        }
    }
}
```

## Requirements

- PHP ^8.2
- Silverstripe Framework ^6
- Silverstripe Assets ^3

## License

See [LICENSE](LICENSE)
