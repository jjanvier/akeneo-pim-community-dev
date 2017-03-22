<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->resetIndex();

            $this->createProduct('foo', []);
            $this->createProduct('bar', []);
            $this->createProduct('baz', []);
        }
    }

    public function getCreatedProductsDate()
    {
        $dates = [];

        $repository = $this->get('pim_api.repository.product');
        $remover = $this->get('pim_catalog.remover.product');

        $remover->remove($repository->findOneByIdentifier('foo'));
        $remover->remove($repository->findOneByIdentifier('bar'));
        $remover->remove($repository->findOneByIdentifier('baz'));

        $dates['before_first'] = new \DateTime('now', new \DateTimeZone('UTC'));
        sleep(2);
        $this->createProduct('foo', []);
        sleep(2);
        $dates['before_second'] = new \DateTime('now', new \DateTimeZone('UTC'));
        sleep(2);
        $this->createProduct('bar', []);
        sleep(2);
        $dates['before_third'] = new \DateTime('now', new \DateTimeZone('UTC'));
        sleep(2);
        $this->createProduct('baz', []);
        sleep(2);
        $dates['after_all'] = new \DateTime('now', new \DateTimeZone('UTC'));

        return $dates;
    }

    public function testOperatorInferior()
    {
        $dates = $this->getCreatedProductsDate();

        $result = $this->execute([['updated', Operators::LOWER_THAN, $dates['before_first']]]);
        $this->assert($result, []);

        $result = $this->execute([['updated', Operators::LOWER_THAN, $dates['before_second']]]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['updated', Operators::LOWER_THAN, $dates['before_third']]]);
        $this->assert($result, ['foo', 'bar']);

        $result = $this->execute([['updated', Operators::LOWER_THAN, $dates['after_all']]]);
        $this->assert($result, ['foo', 'bar', 'baz']);
    }

    public function testOperatorEquals()
    {
        $barProduct = $this->get('pim_api.repository.product')->findOneByIdentifier('bar');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $result = $this->execute([['updated', Operators::EQUALS, $now->format('Y-m-d H:i:s')]]);
        $this->assert($result, []);

        $result = $this->execute([['updated', Operators::EQUALS, $barProduct->getUpdated()]]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorSuperior()
    {
        $dates = $this->getCreatedProductsDate();

        $result = $this->execute([['updated', Operators::GREATER_THAN, $dates['before_second']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['updated', Operators::GREATER_THAN, $dates['before_first']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['updated', Operators::IS_EMPTY, null]]);
        $this->assert($result, []);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['updated', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorDifferent()
    {
        $fooProduct = $this->get('pim_api.repository.product')->findOneByIdentifier('bar');
        $updatedAt = $fooProduct->getUpdated();
        $updatedAt->setTimezone(new \DateTimeZone('UTC'));

        $result = $this->execute([['updated', Operators::NOT_EQUAL, $updatedAt]]);
        $this->assert($result, ['foo', 'baz']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->execute([['updated', Operators::NOT_EQUAL, $currentDate->format('Y-m-d H:i:s')]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorBetween()
    {
        $date = $this->getCreatedProductsDate();

        $result = $this->execute([['updated', Operators::BETWEEN, [$date['before_second'], $date['after_all']]]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['updated', Operators::BETWEEN, [$date['before_second'], $date['before_third']]]]);
        $this->assert($result, ['bar']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->execute([['updated', Operators::BETWEEN, [$date['after_all'], $currentDate]]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $date = $this->getCreatedProductsDate();

        $result = $this->execute([['updated', Operators::NOT_BETWEEN, [$date['before_second'], $date['after_all']]]]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['updated', Operators::NOT_BETWEEN, [$date['before_second'], $date['before_third']]]]);
        $this->assert($result, ['baz', 'foo']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->execute([['updated', Operators::NOT_BETWEEN, [$date['after_all'], $currentDate]]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "updated" expects an array with valid data, should contain 2 strings with the format "Y-m-d H:i:s".
     */
    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->execute([['updated', Operators::BETWEEN, []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "updated" expects a string with the format "Y-m-d H:i:s" as data, "2016-12-12T00:00:00" given.
     */
    public function testErrorDataIsMalformedWithISODate()
    {
        $this->execute([['updated', Operators::EQUALS, '2016-12-12T00:00:00']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "updated" is not supported or does not support operator "IN CHILDREN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['updated', Operators::IN_CHILDREN_LIST, '2016-08-29 00:00:01']]);
    }
}
