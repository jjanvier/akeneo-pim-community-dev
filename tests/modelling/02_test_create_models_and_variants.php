<?php

// test that a model, its submodels and variants can be created and saved
// here we create a root tshirt model, with 6 color submodels
// we have then 6 product variants by color submodel
//
// in this example, the product variants have no family
// the purpose is just to test we can link a product to a model, and a model to another

use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;

$loader = require_once __DIR__ . '/../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$attributeRepository = $container->get('pim_catalog.repository.attribute');
$mainColor = $attributeRepository->findOneByIdentifier('main_color');
$secondaryColor = $attributeRepository->findOneByIdentifier('secondary_color');
$size = $attributeRepository->findOneByIdentifier('clothing_size');

$mainColorCodes = array_map(function($value) { return $value->getCode(); }, $mainColor->getOptions()->toArray());
$secondaryColorCodes = array_map(function($value) { return $value->getCode(); }, $secondaryColor->getOptions()->toArray());
$sizeCodes = array_map(function($value) { return $value->getCode(); }, $size->getOptions()->toArray());

const IDENTIFIER_MODEL = '501_graphic';

dropProductModelsAndProducts($mainColorCodes);

$rootModel = createModel(
    IDENTIFIER_MODEL,
    [
        'name'        => [
            ['data' => 'LEVI\'S® 501 GRAPHIC TEE', 'locale' => null, 'scope' => null],
        ],
        'price'       => [
            [
                'data'   => [['amount' => 29, 'currency' => 'EUR'], ['amount' => 34, 'currency' => 'USD'],],
                'locale' => null,
                'scope'  => null
            ],
        ],
        'description' => [
            [
                'data'   => 'Ce t-shirt basique affiche une coupe standard et présente une sérigraphie sur le devant.',
                'locale' => 'fr_FR',
                'scope'  => 'ecommerce'
            ],
            ['data' => 'cool tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce'],
        ],
    ]
);

foreach ($mainColorCodes as $mainColorCode) {
    $identifier = IDENTIFIER_MODEL . '_' . $mainColorCode;

    switch ($mainColorCode) {
        case 'black':
            $data = [
                'picture' => [
                    ['data' => __DIR__ . '/fixtures/levis501-black.jpg', 'locale' => null, 'scope' => null],
                ],
                'main_color' => [
                    ['data' => 'black', 'locale' => null, 'scope' => null],
                ],
                'secondary_color' => [
                    ['data' => 'red', 'locale' => null, 'scope' => null],
                ],
            ];
            break;
        case 'white':
            $data = [
                'picture' => [
                    ['data' => __DIR__ . '/fixtures/levis501-white.jpg', 'locale' => null, 'scope' => null],
                ],
                'main_color' => [
                    ['data' => 'white', 'locale' => null, 'scope' => null],
                ],
                'secondary_color' => [
                    ['data' => 'blue', 'locale' => null, 'scope' => null],
                ],
            ];
            break;
        default:
            $data = [
                'main_color' => [
                    ['data' => $mainColorCode, 'locale' => null, 'scope' => null],
                ],
                'secondary_color' => [
                    ['data' => $secondaryColorCodes[array_rand($secondaryColorCodes)], 'locale' => null, 'scope' => null],
                ],
            ];
    }

    $colorModel = createModel($identifier, $data, $rootModel);
    foreach ($sizeCodes as $sizeCode) {
        createProduct(
            [
                'clothing_size' => [
                    ['data' => $sizeCode, 'locale' => null, 'scope' => null],
                ],
            ],
            $colorModel
        );
    }
}

function createModel($identifier, array $data, $parent = null)
{
    global $container;

    $updater = $container->get('pim_catalog.updater.flexible_values');
    $saver = $container->get('pim_catalog.saver.product_model');

    $productModel = new ProductModel();
    $productModel->setIdentifier($identifier);
    $productModel->setCreated(new DateTime());
    $productModel->setUpdated(new DateTime());

    $updater->update($productModel, $data);
    if (null !== $parent) {
        $productModel->setModel($parent);
    }

    $saver->save($productModel);

    return $productModel;
}

function createProduct(array $values, ProductModelInterface $productModel)
{
    global $container;

    $productBuilder = $container->get('pim_catalog.builder.product');
    $productUpdater = $container->get('pim_catalog.updater.product');
    $productSaver = $container->get('pim_catalog.saver.product');

    $product = $productBuilder->createProduct(uniqid($productModel->getIdentifier() . '_'));
    $productUpdater->update($product, ['values' => $values]);
    $product->setModel($productModel);
    $productSaver->save($product);

    return $product;
}

function dropProductModelsAndProducts(array $mainColorCodes)
{
    global $container;

    $modelRepository = $container->get('pim_catalog.repository.product_model');
    $modelRemover = $container->get('pim_catalog.remover.product_model');
    $productRemover = $container->get('pim_catalog.remover.product');
    $pqbFactory = $container->get('pim_catalog.query.product_query_builder_factory');

    $modelIdentifiers = array_merge([IDENTIFIER_MODEL], array_map(function($color) { return IDENTIFIER_MODEL . '_' . $color; }, $mainColorCodes));

    foreach ($modelIdentifiers as $identifier) {
        $existingModel = $modelRepository->findOneBy(['identifier' => $identifier]);
        if (null !== $existingModel) {
            $modelRemover->remove($existingModel);

            $pqb = $pqbFactory->create();
            $pqb->addFilter('identifier', \Pim\Component\Catalog\Query\Filter\Operators::CONTAINS, $identifier);
            foreach ($pqb->execute() as $product) {
                $productRemover->remove($product);
            }
        }
    }
}
