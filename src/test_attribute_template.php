<?php

use \Pim\Component\TemplateAttribute\Brick;
use \Pim\Component\TemplateAttribute\TemplateAttribute;
use \Pim\Component\TemplateAttribute\TemplateAttributeLevel;

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
$brick3 = new Brick([$attrs[10], $attrs[11], $attrs[12], $attrs[13]]);
$brick4 = new Brick([$attrs[5], $attrs[6]], $attrColor);
$brick5 = new Brick([$attrs[7], $attrs[8]], $attrSize);

// no variation, 1 level
$template1 = new TemplateAttribute(
    [
        new TemplateAttributeLevel([$brick1])
    ],
    'template 1'
);

// variations on 1 level, 2 levels total
$template2 = new TemplateAttribute(
    [
        new TemplateAttributeLevel([$brick1, $brick2]),
        new TemplateAttributeLevel([$brick3], $brick4)
    ],
    'template 2'
);

// variations on 2 level, 3 levels total
$template3 = new TemplateAttribute(
    [
        new TemplateAttributeLevel([$brick1]),
        new TemplateAttributeLevel([$brick2, $brick3], $brick4),
        new TemplateAttributeLevel([], $brick5),
    ],
    'template 3'
);

echo (string) $template1;
echo (string) $template2;
echo (string) $template3;


$builder = $c->get('pim_catalog.builder.product');
$saver = $c->get('pim_catalog.saver.product');

$product = $builder->createProduct('foo', $template3);
$builder->addMissingProductValues($product);

var_dump($product);
