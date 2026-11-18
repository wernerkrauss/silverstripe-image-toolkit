# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0.html),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **SVG Placeholder (LQIP)** for `Image` and `DBFile`.
  - Renders a tiny blurred thumbnail embedded in an SVG container and returns it as a data URI.
  - The SVG preserves the original aspect ratio (no layout shift while the full image loads).
  - Configurable thumbnail width and blur strength, with a cache to avoid regeneration on repeated renders (e.g. static publishing).
  - Extension hook `updateSvgPlaceholder` for modifying the generated data URI.
  - Template alias `LQIP`.

## [1.0.0] - 2026-03-16

### Added
- Initial release for Silverstripe 6.
- **Watermark** manipulation for `Image` and `DBFile`.
  - Supports positioning (center, top-left, etc.), padding (px or %), opacity, and relative width.
  - Supports passing `Image` objects, IDs, or file paths as logos.
  - Extension hook `updateWatermarkLogo` for dynamic logo selection.
- **Auto-Orient** manipulation based on EXIF data.
- **Color Overlay** manipulation with adjustable color and opacity.
- **ImageToolkitTestController** for visual verification and documentation (available in dev mode).
- Comprehensive test suite and CI configuration.

### Fixed
- Standardized method names to follow Silverstripe conventions (`ColorOverlayed`, `AutoOriented`) while maintaining aliases for template usage.
- Improved stability of variant name generation by using IDs or hashes instead of deep object serialization.
