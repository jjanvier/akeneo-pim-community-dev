<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

$c = $kernel->getContainer();

$saver = $c->get('pim_catalog.saver.product');
$builder = $c->get('pim_catalog.builder.product');
$updater = $c->get('pim_catalog.updater.product');
$calculator = $c->get('pim_catalog.completeness.calculator');

$aFamily = $c->get('pim_catalog.repository.family')->findOneByIdentifier('camcorders');
$aProduct = $builder->createProduct('foo', $aFamily->getCode());
$updater->update(
    $aProduct,
    [
        'description' => [
            [
                'locale' => 'fr_FR',
                'scope' => 'mobile',
                'data' => 'une belle (mais courte) description',
            ],
            [
                'locale' => 'en_US',
                'scope' => 'mobile',
                'data' => 'a beautiful (but short) description',
            ],
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
                'data' => 'a beautful description that is quite long because I want to tell you a story... Once upon a time..',
            ],
        ],
        'release_date' => [
            [
                'locale' => null,
                'scope' => 'ecommerce',
                'data' => '2017-06-23T11:24:44+02:00',
            ],

        ],
        'sensor_type' => [
            [
                'locale' => null,
                'scope' => null,
                'data' => 'ccd',
            ],

        ],
        'total_megapixels' => [
            [
                'locale' => null,
                'scope' => null,
                'data' => 67,
            ],
        ],
    ]
);

//$completenesses = $calculator->calculate($aProduct);

$saver->save($aProduct);
