<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text area research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogNumberIntegration extends AbstractPimCatalogIntegration
{
    public function testLessThanOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'box_quantity-decimal' => ['lt' => 10]
                        ]
                    ]
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLessThanOrEqualsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'box_quantity-decimal' => ['lte' => 10]
                        ]
                    ]
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testEqualsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'box_quantity-decimal' => 100.666
                        ]
                    ]
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3']);
    }

    public function testGreaterThanOrEqualsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'box_quantity-decimal' => ['gte' => 10]
                        ]
                    ]
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'box_quantity-decimal' => ['gt' => 10]
                        ]
                    ]
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_4']);
    }

    public function testEmptyOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'box_quantity-decimal'
                        ]
                    ]
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_7']);
    }

    public function testNotEmptyOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'box_quantity-decimal'
                        ]
                    ]
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addProducts()
    {
        $products = [
            [
                'sku-varchar'          => 'product_1',
                'box_quantity-decimal' => 10.0,
            ],
            [
                'sku-varchar'          => 'product_2',
                'box_quantity-decimal' => 1,
            ],
            [
                'sku-varchar'          => 'product_3',
                'box_quantity-decimal' => 100.666,
            ],
            [
                'sku-varchar'          => 'product_4',
                'box_quantity-decimal' => 25.89,
            ],
            [
                'sku-varchar'          => 'product_5',
                'box_quantity-decimal' => 3.9000,
            ],
            [
                'sku-varchar'          => 'product_6',
                'box_quantity-decimal' => 7,
            ],
            [
                'sku-varchar' => 'product_7',
            ],
        ];

        $this->indexProducts($products);
    }
}