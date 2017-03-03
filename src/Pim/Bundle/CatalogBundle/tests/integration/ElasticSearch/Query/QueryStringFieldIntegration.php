<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\ElasticSearch\Query;

use Elasticsearch\ClientBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text research are consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryStringFieldIntegration extends KernelTestCase
{
    /** TODO: Also could be generated from configuration */
    const INDEX_NAME = 'product_index_test';

    /** TODO: Maybe get this from configuration ? */
    const PRODUCT_TYPE = 'pim_catalog_product';

    /** Client */
    private $ESClient;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->ESClient = ClientBuilder::create()->build();
    }

    public function setUp()
    {
        parent::setUp();

        $this->resetIndex();
        $this->addProducts();
    }

    public function testStartWithOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-text',
                            'query'         => 'an*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_2', 'product_5']);
    }

    public function testStartWithOperatorWithWhiteSpace()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-text',
                            'query'         => 'My\\ product*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1']);
    }

    public function testEndWithOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-text.reverse',
                            'query'         => 'name*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_5']);
    }

    public function testEndWithOperatorWithWhiteSpace()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-text.reverse',
                            'query'         => 'this\\ name*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3']);
    }

    public function testContainsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-text',
                            'query'         => '*Love*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6', 'product_7', 'product_8']);
    }

    public function testContainsOperatorWithWhiteSpace()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-text',
                            'query'         => '*Love\\ this*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6']);
    }

    public function testDoesNotContainOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [

                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'name-text',
                            'query'         => '*Love*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = $this->createSearchQuery(
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'missing' => ['field' => 'name-text'],
                        ],
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_4']);
    }

    public function testSortAscending()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'missing' => ['field' => 'name-text'],

                    ],
                ],
            ],
        ]);
    }

    /**
     * Resets the index used for the integration tests query
     */
    private function resetIndex()
    {
        if ($this->ESClient->indices()->exists(['index' => self::INDEX_NAME])) {
            $this->ESClient->indices()->delete(['index' => self::INDEX_NAME]);
        }

        $this->ESClient->indices()->create($this->getProductIndexConfiguration());
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    private function addProducts()
    {
        $products = [
            [
                'sku_ident' => 'product_1',
                'name-text' => 'My product',
            ],
            [
                'sku_ident' => 'product_2',
                'name-text' => 'Another product',
            ],
            [
                'sku_ident' => 'product_3',
                'name-text' => 'Yeah, love this name',
            ],
            [
                'sku_ident' => 'product_4',
                'name-text' => '',
            ],
            [
                'sku_ident' => 'product_5',
                'name-text' => 'And an uppercase NAME',
            ],
            [
                'sku_ident' => 'product_6',
                'name-text' => 'Love this product',
            ],
            [
                'sku_ident' => 'product_7',
                'name-text' => 'I.love.dots',
            ],
            [
                'sku_ident' => 'product_8',
                'name-text' => 'I-love.dots',
            ],

        ];

        foreach ($products as $product) {
            $this->indexProduct($product);
        }
    }

    /**
     * TODO: From ElasticSearchBundle but could be generated from the global configuration
     *
     * Returns the full configuration for a product index
     *
     * @return array
     */
    private function getProductIndexConfiguration()
    {
        return [
            'index' => self::INDEX_NAME,
            'body'  => [
                'mappings' => [
                    'pim_catalog_product' => [
                        'dynamic_templates' => [
                            [
                                'text_area' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields'   => [
                                            'raw'     => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_area_raw',
                                            ],
                                            'reverse' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_area_reversed',
                                            ],
                                        ],
                                        'type'     => 'string',
                                        'analyzer' => 'pim_text_area_analyzer',
                                    ],
                                    'match'              => '*-text_area',
                                ],
                            ],
                            [
                                'text' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields'   => [
                                            'raw'     => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                            'reverse' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_reversed',
                                            ],
                                        ],
                                        'type'     => 'string',
                                        'analyzer' => 'pim_text_analyzer',
                                    ],
                                    'match'              => '*-text',
                                ],
                            ],
                            [
                                'ident' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields'   => [
                                            'raw'     => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                            'reverse' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_reversed',
                                            ],
                                        ],
                                        'type'     => 'string',
                                        'analyzer' => 'pim_text_analyzer',
                                    ],
                                    'match'              => '*-ident',
                                ],
                            ],
                            [
                                'media' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields'   => [
                                            'reverse' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_reversed',
                                            ],
                                        ],
                                        'type'     => 'string',
                                        'analyzer' => 'pim_text_analyzer',
                                    ],
                                    'match'              => '*-media',
                                ],
                            ],
                            [
                                'date' => [
                                    'match_mapping_type' => 'date',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'date',
                                        'format' => 'dateOptionalTime',
                                    ],
                                    'match'              => '*-date',
                                ],
                            ],
                            [
                                'number' => [
                                    'match_mapping_type' => 'long',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'double',
                                    ],
                                    'match'              => '*-number',
                                ],
                            ],
                            [
                                'metric' => [
                                    'match_mapping_type' => 'double',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'double',
                                    ],
                                    'match'              => '*-metric',
                                ],
                            ],
                            [
                                'bool' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'boolean',
                                    ],
                                    'match'              => '*-bool',
                                ],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'analysis' => [
                        'char_filter' => [
                            'newline_pattern' => [
                                'pattern'     => '\\n',
                                'type'        => 'pattern_replace',
                                'replacement' => '',
                            ],
                        ],
                        'analyzer'    => [
                            'pim_text_analyzer'      => [
                                'filter'    => [
                                    'lowercase',
                                ],
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                            ],
                            'pim_text_reversed'      => [
                                'filter'    => [
                                    'lowercase',
                                    'reverse',
                                ],
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                            ],
                            'pim_text_area_analyzer' => [
                                'filter'      => [
                                    'standard',
                                ],
                                'char_filter' => 'html_strip',
                                'type'        => 'custom',
                                'tokenizer'   => 'standard',
                            ],
                            'pim_text_area_raw'      => [
                                'filter'      => [
                                    'lowercase',
                                ],
                                'char_filter' => [
                                    'html_strip',
                                    'newline_pattern',
                                ],
                                'type'        => 'custom',
                                'tokenizer'   => 'keyword',
                            ],
                            'pim_text_area_reversed' => [
                                'filter'      => [
                                    'lowercase',
                                    'reverse',
                                ],
                                'char_filter' => [
                                    'html_strip',
                                    'newline_pattern',
                                ],
                                'type'        => 'custom',
                                'tokenizer'   => 'keyword',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function indexProduct($product)
    {
        $params = [];
        $params['index'] = self::INDEX_NAME;
        $params['type'] = self::PRODUCT_TYPE;

        $productBody = [];

        foreach ($product as $field => $value) {
            $matches = [];
            if (preg_match('/^(.*)-option$/', $field, $matches)) {
//                $attributeCode = $matches[1];
//                $optionParams = $this->currentOptions[$attributeCode][$value];
//                unset($optionParams['code']);
//                $productBody[$field] = $optionParams;
            } elseif (preg_match('/^(.*)-options$/', $field, $matches)) {
//                $attributeCode = $matches[1];
//                $options = explode(',', $value);
//                $optionsParams = [];
//                foreach ($options as $option) {
//                    $optionParams = $this->currentOptions[$attributeCode][trim($option)];
//                    unset($optionParams['code']);
//                    $optionsParams[] = $optionParams;
//                }
//                $productBody[$field] = $optionsParams;
            } elseif (preg_match('/^(.*)-metric$/', $field, $matches)) {
                $productBody[$field] = floatval($value);
            } elseif (preg_match('/^(.*)-number$/', $field, $matches)) {
                $productBody[$field] = floatval($value);
            } else {
                $productBody[$field] = $value;
            }
        }

        $params['body'] = $productBody;

        $this->ESClient->index($params);
        $this->ESClient->indices()->refresh();
    }

    /**
     * Prepare a search query with the given clause
     *
     * @param array $searchClause
     * @param array $sortClauses
     *
     * @return array
     */
    private function createSearchQuery(array $searchClause, array $sortClauses = [])
    {
        $searchQuery = [
            'index' => self::INDEX_NAME,
            'type'  => self::PRODUCT_TYPE,
            'body'  => [],
        ];

        if (!empty($searchClause)) {
            $searchQuery['body'] = $searchClause;
        }


        if (!empty($sortClause)) {
            $searchQuery['body']['sort'] = $sortClauses;
        }

        return $searchQuery;
    }

    /**
     * Executes the given query and returns the list of skus found.
     *
     * @param array $query
     *
     * @return array
     */
    private function getSearchQueryResults(array $query)
    {
        $skus = [];
        $response = $this->ESClient->search($query);

        foreach ($response['hits']['hits'] as $hit) {
            $skus[] = $hit['_source']['sku_ident'];
        }

        return $skus;
    }

    /**
     * Checks that the products found are effectively expected
     *
     * @param array $productsFound
     * @param array $expectedProducts
     */
    private function assertProducts(array $productsFound, array $expectedProducts)
    {
        $this->assertCount(count($expectedProducts), $productsFound);
        foreach ($expectedProducts as $productExpected) {
            $this->assertContains($productExpected, $productsFound);
        }
    }
}
