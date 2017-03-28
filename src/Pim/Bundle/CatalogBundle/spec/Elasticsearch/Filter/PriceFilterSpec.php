<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\PriceFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

class PriceFilterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper, CurrencyRepositoryInterface $currencyRepository)
    {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            $currencyRepository,
            ['pim_catalog_price_collection'],
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PriceFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                '<',
                '<=',
                Operators::EQUALS,
                '>=',
                '>',
                'EMPTY',
                'NOT EMPTY',
                '!=',
            ]
        );
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_price_collection_attribute(AttributeInterface $price, AttributeInterface $tags)
    {
        $price->getType()->willReturn('pim_catalog_price_collection');
        $tags->getType()->willReturn('pim_catalog_multiselect');

        $this->getAttributeTypes()->shouldReturn(
            [
                'pim_catalog_price_collection',
            ]
        );

        $this->supportsAttribute($price)->shouldReturn(true);
        $this->supportsAttribute($tags)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_lower_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.en_US.ecommerce.USD' => ['lt' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::LOWER_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_lower_or_equal_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.en_US.ecommerce.USD' => ['lte' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_equals(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'term' => [
                    'values.a_price-prices.en_US.ecommerce.USD' => 10,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::EQUALS,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'term' => [
                    'values.a_price-prices.en_US.ecommerce.USD' => 10,
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.en_US.ecommerce.USD',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::NOT_EQUAL,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_greater_or_equal_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.en_US.ecommerce.USD' => ['gte' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.en_US.ecommerce.USD' => ['gt' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::GREATER_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_empty_without_currency(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.en_US.ecommerce',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_EMPTY,
            [],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_empty_with_currency(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.en_US.ecommerce.USD',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_EMPTY,
            ['currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_not_empty_without_currency(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.en_US.ecommerce',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_NOT_EMPTY,
            [],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_not_empty_with_currency(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.en_US.ecommerce.USD',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_NOT_EMPTY,
            ['currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_throws_if_the_currency_is_not_supported(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['YEN']);
        $price->getCode()->willReturn('a_price');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter()->shouldNotBeCalled();

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'a_price',
                'currency',
                'The currency does not exist',
                PriceFilter::class,
                'USD'
            )
        )->during('addAttributeFilter', [$price, Operators::EQUALS, ['amount' => 12, 'currency' => 'USD'], 'en_US', 'ecommerce']);
    }

    function it_throws_an_exception_if_value_is_not_an_valid_array(
        AttributeInterface $attribute,
        SearchQueryBuilder $sqb
    ) {
        $attribute->getCode()->willReturn('a_price');
        $value = ['currency' => 'YEN'];

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'a_price',
                'amount',
                PriceFilter::class,
                $value
            )
        )->during('addAttributeFilter', [$attribute, Operators::EQUALS, $value]);

        $value = ['amount' => 459];
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'a_price',
                'currency',
                PriceFilter::class,
                $value
            )
        )->during('addAttributeFilter', [$attribute, Operators::EQUALS, $value]);

        $value = ['amount' => 'YEN', 'currency' => 'YEN'];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'a_price',
                'key "amount" has to be a numeric, "string" given',
                PriceFilter::class,
                $value
            )
        )->during('addAttributeFilter', [$attribute, Operators::EQUALS, $value]);

        $value = ['amount' => 132, 'currency' => 42];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'a_price',
                'key "currency" has to be a string, "integer" given',
                PriceFilter::class,
                $value
            )
        )->during('addAttributeFilter', [$attribute, Operators::EQUALS, $value]);
    }
}
