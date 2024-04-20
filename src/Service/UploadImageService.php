<?php

namespace App\Service;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;

class UploadImageService
{
    const PRODUCT_PATH = '/products/';

    public function uploadFileFromUrl(string $url): Image
    {
        $pathInfo = pathinfo($url);
        $image = Image::getByPath(sprintf('%s%s', self::PRODUCT_PATH, $pathInfo['basename']));
        $fileSize = @getimagesize($url);

        if (!$image && $fileSize) {
            $image = new Image();
            $image->setFilename($pathInfo['basename']);
            $image->setData(file_get_contents($url));
            $image->setParent(Asset::getByPath(self::PRODUCT_PATH));
            $image->save();
        }

        $image->clearThumbnails();

       return $image;
    }
}
