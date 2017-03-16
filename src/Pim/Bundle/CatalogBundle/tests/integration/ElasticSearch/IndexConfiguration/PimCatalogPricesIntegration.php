<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogPricesIntegration extends AbstractPimCatalogIntegration
{
    public function testLowerThanOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.USD' => ['lt' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2']);

        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.EUR' => ['lt' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3']);
    }

    public function testLowerOrEqualThanOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.USD' => ['lte' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_4']);

        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.EUR' => ['lte' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_4']);
    }

    public function testEqualsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'a_price-prices.USD' => 10,
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_4']);
    }

    public function testNotEqualsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'a_price-prices.USD' => 10,
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'a_price-prices.USD',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_6']
        );
    }

    public function testGreaterOrEqualThanOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.USD' => ['gte' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_4', 'product_6']);

        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.EUR' => ['gte' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_4']);
    }

    public function testGreaterThanOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.USD' => ['gt' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6']);

        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'a_price-prices.EUR' => ['gt' => 10],
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2']);
    }

    public function testEmptyOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'a_price-prices.USD',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_5', 'product_7']);

        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'a_price-prices.CNY',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testNotEmptyOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'a_price-prices.EUR',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_3', 'product_4']);

        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'a_price-prices.CNY',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_7']);
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addProducts()
    {
        $products = [
            [
                'sku-varchar'        => 'product_1',
                'a_price-prices.USD' => '5',
                'a_price-prices.EUR' => '15.55',
            ],
            [
                'sku-varchar'    => 'product_2',
                'a_price-prices' => [
                    'USD' => '5',
                    'EUR' => '15.55',
                ],
            ],
            [
                'sku-varchar'    => 'product_3',
                'a_price-prices' => [
                    'USD' => '16',
                    'EUR' => '6.60',
                ],
            ],
            [
                'sku-varchar'    => 'product_4',
                'a_price-prices' => [
                    'USD' => '10',
                    'EUR' => '10',
                ],
            ],
            [
                'sku-varchar' => 'product_5',
            ],
            [
                'sku-varchar'    => 'product_6',
                'a_price-prices' => [
                    'USD' => '150',
                ],
            ],
            [
                'sku-varchar'    => 'product_7',
                'a_price-prices' => [
                    'CNY' => '150',
                ],
            ],
        ];

        $this->indexProducts($products);
    }
}
