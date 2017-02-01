#!/usr/bin/env php
<?php

use Acme\Bundle\RangeBundle\AttributeType\RangeType;
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

/**
 * @return string
 */
function getDatetime()
{
    $datetime = new \DateTime();

    return $datetime->format('Y-m-d H:i:s').': ';
}
