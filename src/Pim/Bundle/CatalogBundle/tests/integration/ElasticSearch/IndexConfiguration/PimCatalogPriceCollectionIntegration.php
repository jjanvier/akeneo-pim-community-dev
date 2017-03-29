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
class PimCatalogPriceCollectionIntegration extends AbstractPimCatalogIntegration
{
    public function testLowerThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.USD' => ['lt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.EUR' => ['lt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3']);
    }

    public function testLowerOrEqualThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.USD' => ['lte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_4']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.EUR' => ['lte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_4']);
    }

    public function testEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.USD' => '10',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_4']);
    }

    public function testNotEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.USD' => 10,
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_locales>.<all_channels>.USD',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_6']
        );
    }

    public function testGreaterOrEqualThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.USD' => ['gte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_4', 'product_6']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.EUR' => ['gte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_4']);
    }

    public function testGreaterThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.USD' => ['gt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.a_price-prices.<all_locales>.<all_channels>.EUR' => ['gt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2']);
    }

    /**
     * Same as testEmptyOperator test.
     */
    public function testEmptyOnAllCurrenciesOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_locales>.<all_channels>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_5']);
    }

    public function testEmptyForCurrencyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_locales>.<all_channels>.USD',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_5', 'product_7']);

        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_locales>.<all_channels>.CNY',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    /**
     * Same as testNotEmptyOperator
     */
    public function testNotEmptyOnAtLeastOneCurrencyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_locales>.<all_channels>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_6', 'product_7']
        );
    }

    public function testNotEmptyForCurrencyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_locales>.<all_channels>.EUR',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_3', 'product_4']);

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.a_price-prices.<all_locales>.<all_channels>.CNY',
                        ],
                    ],
                ],
            ],
        ];

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
                'identifier' => 'product_1',
                'values'     => [
                    'a_price-prices' => [
                        '<all_locales>' => [
                            '<all_channels>' => [
                                'USD' => '5',
                                'EUR' => '15.55',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'a_price-prices' => [
                        '<all_locales>' => [
                            '<all_channels>' => [
                                'USD' => '5',
                                'EUR' => '15.55',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'a_price-prices' => [
                        '<all_locales>' => [
                            '<all_channels>' => [
                                'USD' => '16',
                                'EUR' => '6.60',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'a_price-prices' => [
                        '<all_locales>' => [
                            '<all_channels>' => [
                                'USD' => '10',
                                'EUR' => '10',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'a_price-prices' => [
                        '<all_locales>' => [
                            '<all_channels>' => [
                                'USD' => '150',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [
                    'a_price-prices' => [
                        '<all_locales>' => [
                            '<all_channels>' => [
                                'CNY' => '150',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->indexProducts($products);
    }
}
