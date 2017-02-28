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
$pqb = $c->get('pim_catalog.query.product_query_builder_factory')->create();
$pqb->addFilter('name', Operators::CONTAINS, 'Sony SRS-BTV25');

$products = $pqb->execute();

echo sprintf("%d products found...\n", $products->count());
foreach ($products as $product) {
    echo sprintf("Identifier=%s - MySQL ID=%s\n", $product->getIdentifier(), $product->getId());
}
