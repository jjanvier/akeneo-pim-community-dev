<?php

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Stopwatch\Stopwatch;

$loader = require_once __DIR__ . '/../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../app/AppKernel.php';

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

$familyFactory = $container->get('pim_catalog.factory.family');
$familyUpdate = $container->get('pim_catalog.updater.family');
$familySaver = $container->get('pim_catalog.saver.family');
$familyRepository = $container->get('pim_catalog.repository.family');

if (null !== $family = $familyRepository->findOneByIdentifier('variant_family')) {
    $container->get('pim_catalog.remover.family')->remove($family);
}

$family = $familyFactory->create();
$familyUpdate->update(
    $family,
    [
        'code' => 'variant_family',
        'labels' => [
            'en_US' => 'Variant family',
        ],
        'attributes' => [
            'sku',
            'name',
            'price',
            'description',
            'main_color',
            'picture',
            'secondary_color',
            'clothing_size',
        ],
        'templates' => [
            [
                'code' => 'tshirt-globe',
                'attribute_sets' => [
                    [
                        'attributes' => [
                            'sku',
                            'name',
                            'price',
                            'description',
                        ],
                    ],
                    [
                        'attributes' => [
                            'main_color',
                            'picture',
                        ],
                        'axes' => ['main_color'],
                    ],
                    [
                        'attributes' => [
                            'clothing_size',
                        ],
                        'axes' => ['clothing_size'],
                    ],
                ],
            ],
        ],
        'attribute_as_label' => 'sku',
        'attribute_requirements' => [
            'ecommerce' => [
                'sku',
            ],
        ],
    ]
);

$familySaver->save($family);

$container->get('doctrine.orm.default_entity_manager')->refresh($family);

var_dump($family);