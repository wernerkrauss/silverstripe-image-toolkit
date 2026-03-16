<?php

namespace Netwerkstatt\ImageToolkit\Control;

use Override;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Security\Permission;

class ImageToolkitTestController extends Controller
{
    private static string $url_segment = 'image-toolkit-test';

    private static array $allowed_actions = [
        'index'
    ];

    /**
     * @throws HTTPResponse_Exception
     */
    #[Override]
    protected function init(): void
    {
        parent::init();

        if (!Director::isDev()) {
            $this->httpError(404);
        }

        if (!Permission::check('ADMIN')) {
            $this->httpError(403);
        }
    }

    public function index(HTTPRequest $request)
    {
        return $this->renderWith(['Netwerkstatt/ImageToolkit/Control/ImageToolkitTestController', 'Page']);
    }

    public function getTestImage(): ?Image
    {
        return $this->ensureImage('image-toolkit-test/example-image.jpg', 'silverstripe-image-toolkit/images/example-image.jpg');
    }

    public function getTestLogo(): ?Image
    {
        return $this->ensureImage('image-toolkit-test/test-logo.png', 'silverstripe-image-toolkit/images/test-logo.png');
    }

    protected function ensureImage(string $assetPath, string $modulePath): ?Image
    {
        $image = Image::get()->filter(['FileFilename' => $assetPath])->first();
        if ($image && $image->exists()) {
            return $image;
        }

        $fullModulePath = BASE_PATH . DIRECTORY_SEPARATOR . $modulePath;
        if (!file_exists($fullModulePath)) {
            return null;
        }

        $folder = Folder::find_or_make(dirname($assetPath));
        
        $image = Image::create();
        $image->setFromLocalFile($fullModulePath, $assetPath);
        $image->ParentID = $folder->ID;
        $image->write();
        $image->publishRecursive();

        return $image;
    }
}
