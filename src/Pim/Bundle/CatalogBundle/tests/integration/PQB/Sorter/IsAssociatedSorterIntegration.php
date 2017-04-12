<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsAssociatedSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSortDescendant()
    {
        $result = $this->executeSorter([['is_associated.PACK.foo_bar', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'bar',
            'foo_baz',
            'foo',
            'baz',
            'foo_bar',
        ]);

        $result = $this->executeSorter([['is_associated.SUBSTITUTION.foo_bar', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'baz',
            'bar',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.UPSELL.foo_bar', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'baz',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.X_SELL.foo_bar', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'baz',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.PACK.foo_baz', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'baz',
            'foo',
            'bar',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.SUBSTITUTION.foo_baz', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'baz',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.UPSELL.foo_baz', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'bar',

            'foo',
            'baz',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.X_SELL.foo_baz', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'bar',
            'foo_bar',

            'foo',
            'baz',
            'foo_baz',
        ]);
    }

    public function testSortAscendant()
    {
        $result = $this->executeSorter([['is_associated.PACK.foo_bar', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'baz',
            'foo_bar',
            'bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.SUBSTITUTION.foo_bar', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'bar',
            'foo_bar',
            'foo_baz',
            'foo',
            'baz',
        ]);

        $result = $this->executeSorter([['is_associated.UPSELL.foo_bar', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'baz',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.X_SELL.foo_bar', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'baz',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.PACK.foo_baz', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'foo_bar',
            'foo_baz',
            'baz',
        ]);

        $result = $this->executeSorter([['is_associated.SUBSTITUTION.foo_baz', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'baz',
            'foo_bar',
            'foo_baz',
        ]);

        $result = $this->executeSorter([['is_associated.UPSELL.foo_baz', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'baz',
            'foo_bar',
            'foo_baz',
            'bar',
        ]);

        $result = $this->executeSorter([['is_associated.X_SELL.foo_baz', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'baz',
            'foo_baz',
            'bar',
            'foo_bar',
        ]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['is_associated.PACK.foo', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('foo', []);
            $this->createProduct('bar', []);
            $this->createProduct('baz', []);

            $fooBar = $this->createProduct('foo_bar', []);
            $fooBaz = $this->createProduct('foo_baz', []);

            $this->updateProduct($fooBar, [
                'associations' => [
                    'PACK' => [
                        'groups'   => [],
                        'products' => ['bar', 'foo_baz'],
                    ],
                    'SUBSTITUTION' => [
                        'groups'   => [],
                        'products' => ['foo', 'baz'],
                    ],
                ],
            ]);

            $this->updateProduct($fooBaz, [
                'associations' => [
                    'PACK' => [
                        'groups'   => [],
                        'products' => ['baz'],
                    ],
                    'UPSELL' => [
                        'groups'   => [],
                        'products' => ['bar'],
                    ],
                    'X_SELL' => [
                        'groups'   => [],
                        'products' => ['bar', 'foo_bar'],
                    ],
                ],
            ]);
        }
    }
}
