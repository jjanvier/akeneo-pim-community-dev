<?php

namespace tests\integration\Pim\Component\Catalog\Normalizer\Indexing;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\DateSanitizer;
use Akeneo\Test\Integration\TestCase;

/**
 * Integration tests to verify data from database are well formatted in the indexing format
 */
class ProductIndexingIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }

    public function testEmptyDisabledProduct()
    {
        $expected = [
            'identifier'    => 'bar',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => false,
            'values'        => [
                'sku-varchar' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'bar'
                    ]
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $this->assertIndexingFormat('bar', $expected);
    }

    public function testEmptyEnabledProduct()
    {
        $expected = [
            'identifier'    => 'baz',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku-varchar' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'baz'
                    ]
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $this->assertIndexingFormat('baz', $expected);
    }

    public function testProductWithAllAttributes()
    {
        $expected =
            [
                'identifier'    => 'foo',
                'family'        => 'familyA',
                'groups'        => ['groupA', 'groupB'],
                'variant_group' => 'variantA',
                'categories'    => ['categoryA1', 'categoryB'],
                'enabled'       => true,
                'values'        => [

            'sku-varchar' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'a_file-media' => [
                '<all_channels>' => [
                    '<all_locales>' => '8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt'
                ],
            ],
            'an_image-media' => [
                '<all_channels>' => [
                    '<all_locales>' => '3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg'
                ],
            ],
            'a_date-date' => [
                '<all_channels>' => [
                    '<all_locales>' => '2016-06-13T00:00:00+02:00'
                ],
            ],
            'a_metric-metric' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount' => '987654321987.1234',
                        'unit'   => 'KILOWATT',
                        // TODO: here maybe we should have a "987654321987123.4", but the measure converter
                        // TODO: returns a double that is too big, and we didn't change that
                        // TODO: see TIP-695
                        'base_data' => 9.8765432198712e+14,
                        'base_unit' => 'WATT',
                        'family'    => 'Power',
                    ]
                ],
            ],
            'a_metric_without_decimal-metric' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount'    => 98,
                        'unit'      => 'CENTIMETER',
                        'base_data' => 0.98,
                        'base_unit' => 'METER',
                        'family'    => 'Length',
                    ],
                ],
            ],
            'a_metric_without_decimal_negative-metric' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount'    => -20,
                        'unit'      => 'CELSIUS',
                        'base_data' => 253.15,
                        'base_unit' => 'KELVIN',
                        'family'    => 'Temperature',
                    ],
                ],
            ],
            'a_metric_negative-metric' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount' => '-20.5000',
                        'unit'   => 'CELSIUS',
                        // TODO: here maybe we should have a string, but the measure converter returns a double,
                        // TODO: and we didn't change that
                        // TODO: see TIP-695
                        'base_data' => 252.65,
                        'base_unit' => 'KELVIN',
                        'family'    => 'Temperature',
                    ],
                ],
            ],
            'a_multi_select-options' => [
                '<all_channels>' => [
                    '<all_locales>' => ['optionA', 'optionB'],
                ],
            ],
            'a_number_float-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '12.5678',
                ],
            ],
            'a_number_float_negative-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '-99.8732',
                ],
            ],
            'a_number_integer-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => 42,
                ],
            ],
            'a_number_integer_negative-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => -42,
                ],
            ],
            'a_price-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'USD' => ['amount' => '45.00', 'currency' => 'USD'],
                        'EUR' => ['amount' => '56.53', 'currency' => 'EUR']
                    ],
                ],
            ],
            'a_price_without_decimal-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'USD' => ['amount' => -45, 'currency' => 'USD'],
                        'EUR' => ['amount' => 56, 'currency' => 'EUR']
                    ],
                ],
            ],
            'a_ref_data_multi_select-reference_data_options' => [
                '<all_channels>' => [
                    '<all_locales>' => ['fabricA', 'fabricB'],
                ],
            ],
            'a_ref_data_simple_select-reference_data_option' => [
                '<all_channels>' => [
                    '<all_locales>' => 'colorB',
                ],
            ],
            'a_simple_select-option' => [
                '<all_channels>' => [
                    '<all_locales>' => 'optionB',
                ],
            ],
            'a_text-varchar' => [
                '<all_channels>' => [
                    '<all_locales>' => 'this is a text',
                ],
            ],
            'a_text_area-text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'this is a very very very very very long  text',
                ],
            ],
            'a_yes_no-boolean' => [
                '<all_channels>' => [
                    '<all_locales>' => true,
                ],
            ],
            'a_localizable_image-media' => [
                '<all_channels>' => [
                    'en_US' => '7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg',
                    'fr_FR' => '0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg',
                ],
            ],
            'a_scopable_price-prices' => [
                'ecommerce' => [
                    '<all_locales>' => [
                        'EUR' => ['amount' => '15.00', 'currency' => 'EUR'],
                        'USD' => ['amount' => '20.00', 'currency' => 'USD'],
                    ],
                ],
                'tablet' => [
                    '<all_locales>' => [
                        'EUR' => ['amount' => '17.00', 'currency' => 'EUR'],
                        'USD' => ['amount' => '24.00', 'currency' => 'USD'],
                    ],
                ],
            ],
            'a_localized_and_scopable_text_area-text' => [
                'ecommerce' => [
                    'en_US' => 'a text area for ecommerce in English',
                ],
                'tablet' => [
                    'en_US' => 'a text area for tablets in English',
                    'fr_FR' => 'une zone de texte pour les tablettes en français',

                ],
            ],
        ],

            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'   => ['groups' => [], 'products' => ['bar', 'baz']],
                'UPSELL' => ['groups' => ['groupA'], 'products' => []],
                'X_SELL' => ['groups' => ['groupB'], 'products' => ['bar']],
            ],
        ];

        $this->assertIndexingFormat('foo', $expected);
    }

    /**
     * @param string $identifier
     * @param array  $expected
     */
    private function assertIndexingFormat($identifier, array $expected)
    {
        $repository = $this->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier($identifier);

        $serializer = $this->get('pim_serializer');
        $result = $serializer->normalize($product, 'indexing');
        $result = $this->sanitizeDateFields($result);

        $expected = $this->sanitizeDateFields($expected);

        $this->assertSame($expected, $result);
    }

    /**
     * Replaces dates fields (created/updated) in the $data array by self::DATE_FIELD_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeDateFields(array $data)
    {
        $data['created'] = DateSanitizer::sanitize($data['created']);
        $data['updated'] = DateSanitizer::sanitize($data['updated']);

        return $data;
    }
}
