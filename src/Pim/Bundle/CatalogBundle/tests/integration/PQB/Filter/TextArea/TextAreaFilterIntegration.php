<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAreaFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('cat', [
                'values' => [
                    'a_text_area' => [['data' => 'cat', 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('cattle', [
                'values' => [
                    'a_text_area' => [['data' => 'cattle', 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('dog', [
                'values' => [
                    'a_text_area' => [['data' => 'dog', 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('best_dog', [
                'values' => [
                    'a_text_area' => [['data' => 'my dog is the most beautiful', 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorStartsWith()
    {
        $result = $this->execute([['a_text_area', Operators::STARTS_WITH, 'at']]);
        $this->assert($result, []);

        $result = $this->execute([['a_text_area', Operators::STARTS_WITH, 'cat']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_text_area', Operators::STARTS_WITH, 'cats']]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['a_text_area', Operators::CONTAINS, 'at']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_text_area', Operators::CONTAINS, 'cat']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_text_area', Operators::CONTAINS, 'most beautiful']]);
        $this->assert($result, ['best_dog']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['a_text_area', Operators::DOES_NOT_CONTAIN, 'at']]);
        $this->assert($result, ['dog', 'best_dog','empty_product']);

        $result = $this->execute([['a_text_area', Operators::DOES_NOT_CONTAIN, 'other']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog', 'empty_product']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_text_area', Operators::EQUALS, 'cats']]);
        $this->assert($result, []);

        $result = $this->execute([['a_text_area', Operators::EQUALS, 'cat']]);
        $this->assert($result, ['cat']);

        $result = $this->execute([['a_text_area', Operators::EQUALS, 'my dog is the most beautiful']]);
        $this->assert($result, ['best_dog']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_text_area', Operators::IS_EMPTY, null]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_text_area', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_text_area', Operators::NOT_EQUAL, 'dog']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_text_area', Operators::NOT_EQUAL, 'cat']]);
        $this->assert($result, ['cattle', 'dog', 'best_dog']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_text" expects a string as data, "array" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['a_text_area', Operators::NOT_EQUAL, [[]]]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_text" is not supported or does not support operator ">="
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['a_text_area', Operators::GREATER_OR_EQUAL_THAN, 'dog']]);
    }
}
