<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\AbstractFieldFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\IdentifierFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class IdentifierFilterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith(
            $attributeRepository,
            ['identifier'],
            [
                'STARTS WITH',
                'CONTAINS',
                'DOES NOT CONTAIN',
                '=',
                '!=',
                'IN LIST',
                'NOT IN LIST'
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IdentifierFilter::class);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'STARTS WITH',
                'CONTAINS',
                'DOES NOT CONTAIN',
                '=',
                '!=',
                'IN LIST',
                'NOT IN LIST'
            ]

        );
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(true);
        $this->supportsOperator('EMPTY')->shouldReturn(false);
    }

    function it_supports_identifier_field($attributeRepository)
    {
        $attributeRepository->getIdentifierCode()->shouldNotBeCalled();
        $this->supportsField('identifier')->shouldReturn(true);
    }

    function it_supports_attribute_identifier($attributeRepository)
    {
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $this->supportsField('sku')->shouldReturn(true);
        $this->supportsField('my_identifier')->shouldReturn(false);

    }

    function it_adds_a_filter_with_operator_starts_with(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => 'sku-*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::STARTS_WITH, 'sku-', null, null, []);
    }

    function it_adds_a_filter_with_operator_contains(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => '*001*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::CONTAINS, '001', null, null, []);
    }

    function it_adds_a_filter_with_operator_not_contains(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'identifier',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => '*001*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::DOES_NOT_CONTAIN, '001', null, null, []);
    }

    function it_adds_a_filter_with_operator_equals(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'term' => [
                    'identifier' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::EQUALS, 'sku-001', null, null, []);
    }

    function it_adds_a_filter_with_operator_not_equal(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            [
                'term' => [
                    'identifier' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'identifier',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::NOT_EQUAL, 'sku-001', null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['identifier', Operators::EQUALS, 'sku-001', null,  null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string_with_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'identifier',
                IdentifierFilter::class,
                ['sku-001']
            )
        )->during('addFieldFilter', ['identifier', Operators::EQUALS, ['sku-001'], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'identifier',
                IdentifierFilter::class,
                'sku-001'
            )
        )->during('addFieldFilter', ['identifier', Operators::IN_LIST, 'sku-001', null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                IdentifierFilter::class
            )
        )->during('addFieldFilter', ['identifier', Operators::IN_CHILDREN_LIST, 'sku-001', null, null, []]);
    }
}
