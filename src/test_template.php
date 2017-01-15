<?php

use \Pim\Component\TemplateAttribute\Brick;
use \Pim\Component\TemplateAttribute\TemplateAttribute;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$c = $kernel->getContainer();
$attrRepository = $c->get('pim_catalog.repository.attribute');

$attrs = $attrRepository->findAll();

$attrColor = $attrRepository->findOneByIdentifier('main_color');
$attrSize = $attrRepository->findOneByIdentifier('clothing_size');

$brick1 = new Brick([$attrs[0], $attrs[1], $attrs[2]]);
$brick2 = new Brick([$attrs[3], $attrs[4]]);
$brick3 = new Brick([$attrs[5], $attrs[6]], $attrColor);
$brick4 = new Brick([$attrs[7], $attrs[8]], $attrSize);

$template1 = new TemplateAttribute([$brick1], 'template 1');
$template2 = new TemplateAttribute([$brick1, $brick2, $brick3], 'template 2');
$template3 = new TemplateAttribute([$brick1, $brick2, $brick3, $brick4], 'template 3');

$builder = $c->get('pim_catalog.builder.product');
$saver = $c->get('pim_catalog.saver.product');

$product = $builder->createProduct('foo', $template1);
$builder->addMissingProductValues($product);

$generator = new \Pim\Component\TemplateAttribute\ProductsGenerator($builder, $saver);
$generator->generate($template2);
