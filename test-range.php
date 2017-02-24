#!/usr/bin/env php
<?php

use Acme\Bundle\RangeBundle\AttributeType\RangeType;
use Acme\Bundle\RangeBundle\Model\Range;
use Symfony\Component\Debug\Debug;

echo getDatetime()."Boot Symfony kernel...\n";

$loader = require_once __DIR__.'/app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/app/AppKernel.php';

$kernel = new AppKernel('test', true);
$kernel->boot();

echo getDatetime()."Get container and services...\n";

$container = $kernel->getContainer();

$attributeRepository = $container->get('pim_catalog.repository.attribute');
$attributeFactory = $container->get('pim_catalog.factory.attribute');
$attributeSaver = $container->get('pim_catalog.saver.attribute');

$productBuilder = $container->get('pim_catalog.builder.product');
$productUpdater = $container->get('pim_catalog.updater.product');
$productSaver = $container->get('pim_catalog.saver.product');
$productDetacher = $container->get('akeneo_storage_utils.doctrine.object_detacher');
$productRepository = $container->get('pim_catalog.repository.product');

echo getDatetime()."Check if there is an attribute of type range...\n";

if (null === $rangeAttribute = $attributeRepository->findOneByIdentifier('range')) {
    echo getDatetime()."Non found, create one...\n";

    $rangeAttribute = $attributeFactory->createAttribute(RangeType::RANGE);
    $rangeAttribute->setCode('range');

    echo getDatetime()."Save the new attribute...\n";
    $attributeSaver->save($rangeAttribute);
} else {
    echo getDatetime()."There is...\n";
}

$identifier = 'sku_'.uniqid();

echo getDatetime()."Generate a new product with identifier \"".$identifier."\"...\n";
$product = $productBuilder->createProduct($identifier);

echo getDatetime()."Update the product with range value...\n";
$productUpdater->update($product, [
    'values' => [
        'range' => [[
            'locale' => null,
            'scope' => null,
            'data' => [
                'from_data' => 0,
                'to_data'   => 42,
            ],
        ]],
    ],
]);

echo getDatetime()."Save the product...\n";
$productSaver->save($product);

echo getDatetime()."Detach the saved product...\n";
$productDetacher->detach($product);

echo getDatetime()."Get the product...\n";
$product = $productRepository->findOneByIdentifier($identifier);
$rangeValue = $product->getValue('range');

echo getDatetime()."Test the product range value...\n";
$range = $rangeValue->getData();
if (!$range instanceof Range) {
    echo "\n    \"Range\" object expected in product value, get \"".gettype($range)."\"\n";
}

$fromData = $range->getFromData();
$toData = $range->getToData();

if (0 === $fromData && 42 === $toData) {
    echo "\n    Product contains expected range: from 0 to 42\n";
} else {
    echo "\n    Error: expected range was from 0 to 42, product actually contain from ".$fromData." to ".$toData."\n";
}

/**
 * @return string
 */
function getDatetime()
{
    $datetime = new \DateTime();

    return $datetime->format('Y-m-d H:i:s').': ';
}
