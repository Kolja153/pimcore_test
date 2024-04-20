<?php

namespace App\Service;

use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Element\Service;

class ProductService
{
    public static function findOrCreateProduct(string $gtin): Product
    {
        $product = Product::getByGtin($gtin)->current();

        if (!$product instanceof Product || $product->getGtin() !== (int)$gtin) {
            $product = new Product();
            $product->setGtin($gtin);
            $product->setKey(Service::getValidKey($gtin, 'object'));
            $product->setParentId(Product::classId());
            $product->setClassId(Product::classId());
            $product->setPublished(true);
            $product->save();
        }

        return $product;
    }
}
