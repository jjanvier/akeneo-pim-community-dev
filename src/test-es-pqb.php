<?php

use Pim\Component\Catalog\Query\Filter\Operators;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

$c = $kernel->getContainer();

// Find all products from the family "camcorders"
//$pqb = $c->get('pim_catalog.query.product_query_builder_factory')->create();
//$pqb->addFilter('family', Operators::IN_LIST, ['camcorders']);
//
//$products = $pqb->execute();
//
//echo sprintf("%d products found...\n", $products->count());
//foreach ($products as $product) {
//    echo sprintf("Identifier=%s - MySQL ID=%s\n", $product->getIdentifier(), $product->getId());
//}


// Find all products which name contains "sony"
//$pqb = $c->get('pim_catalog.query.product_query_builder_factory')->create();
//$pqb->addFilter('name', Operators::CONTAINS, 'sony);
//
//$products = $pqb->execute();
//
//echo sprintf("%d products found...\n", $products->count());
//foreach ($products as $product) {
//    echo sprintf("Identifier=%s - MySQL ID=%s\n", $product->getIdentifier(), $product->getId());
//}

// find all products with a weight (metric) lower than 3Kg
//$pqb = $c->get('pim_catalog.query.product_query_builder_factory')->create();
//$pqb->addFilter('weight', Operators::LOWER_THAN, ['amount' => 3, 'unit' => 'KILOGRAM']);
//
//$products = $pqb->execute();
//
//echo sprintf("%d products found...\n", $products->count());
//foreach ($products as $product) {
//    echo sprintf("Identifier=%s - MySQL ID=%s\n", $product->getIdentifier(), $product->getId());
//}

// Complex request 1: search for camcorders with "sony" in their names and "performance" in their description
//$pqb = $c->get('pim_catalog.query.product_query_builder_factory')->create();
//$pqb->addFilter('family', Operators::IN_LIST, ['camcorders']);
//$pqb->addFilter('name', Operators::CONTAINS, 'sony');
//$pqb->addFilter('description', Operators::CONTAINS, 'performance', ['locale' => 'en_US', 'scope' => 'print']);
//
//$products = $pqb->execute();
//
//echo sprintf("%d products found...\n", $products->count());
//foreach ($products as $product) {
//    echo sprintf("Identifier=%s - MySQL ID=%s\n", $product->getIdentifier(), $product->getId());
//}

// Complex request 2: search for camcorders with "sony" in their names and "performance" in their description
$pqb = $c->get('pim_catalog.query.product_query_builder_factory')->create();
$pqb->addFilter('name', Operators::IS_EMPTY, null);
$pqb->addFilter('weight', Operators::LOWER_THAN, ['amount' => 3, 'unit' => 'KILOGRAM']);

$products = $pqb->execute();

echo sprintf("%d products found...\n", $products->count());
foreach ($products as $product) {
    echo sprintf("Identifier=%s - MySQL ID=%s\n", $product->getIdentifier(), $product->getId());
}
