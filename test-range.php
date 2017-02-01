#!/usr/bin/env php
<?php

use Acme\Bundle\RangeBundle\AttributeType\RangeType;
use Symfony\Component\Debug\Debug;

echo getDatetime()."Boot Symfony kernel...\n";

$loader = require_once __DIR__.'/app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

echo getDatetime()."Get container and services...\n";

$container = $kernel->getContainer();

$attributeRepository = $container->get('pim_catalog.repository.attribute');
$attributeFactory = $container->get('pim_catalog.factory.attribute');
$attributeSaver = $container->get('pim_catalog.saver.attribute');

echo getDatetime()."Check if there is an attribute of type range...\n";

if (null === $rangeAttribute = $attributeRepository->findOneByIdentifier('range')) {
    echo getDatetime()."Non found, create one...\n";

    $rangeAttribute = $attributeFactory->createAttribute(RangeType::RANGE);
    $rangeAttribute->setCode('range');

    echo getDatetime()."Save the new attribute...\n";
    $attributeSaver->save($rangeAttribute);
}

/**
 * @return string
 */
function getDatetime()
{
    $datetime = new \DateTime();

    return $datetime->format('Y-m-d H:i:s').': ';
}
