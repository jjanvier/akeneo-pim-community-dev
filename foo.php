<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

$loader = require_once __DIR__.'/app/bootstrap.php.cache';
require_once __DIR__.'/app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

$c = $kernel->getContainer();

$factory = $c->get('pim_catalog.query.product_query_builder_factory');
$pqb = $factory->create();
$pqb->addFilter('family', \Pim\Component\Catalog\Query\Filter\Operators::IN_LIST, ['tshirts']);

$products = $pqb->execute();

foreach ($products as $product) {
    echo "\n" . $product->getIdentifier();
}
