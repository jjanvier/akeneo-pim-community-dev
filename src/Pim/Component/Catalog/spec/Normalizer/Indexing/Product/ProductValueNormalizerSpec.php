<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductValueNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueNormalizer::class);
    }

    function it_support_values(ProductValueInterface $value)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($value, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($value, 'indexing')->shouldReturn(true);
    }

    function it_normalizes_simple_values($stdNormalizer, ProductValueInterface $value, AttributeInterface $attribute)
    {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $stdNormalizer->normalize($value, 'indexing', ['context'])->willReturn([
            'scope' => null,
            'locale' => null,
            'data' => 'foo'
        ]);

        $indexingValue = [];
        $indexingValue['attribute-text']['<all_channels>']['<all_locales>'] = 'foo';

        $this->normalize($value, 'indexing', ['context'])->shouldReturn($indexingValue);
    }

    function it_normalizes_scopable_values($stdNormalizer, ProductValueInterface $value, AttributeInterface $attribute)
    {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $stdNormalizer->normalize($value, 'indexing', ['context'])->willReturn([
            'scope' => 'ecommerce',
            'locale' => null,
            'data' => 'foo'
        ]);

        $indexingValue = [];
        $indexingValue['attribute-text']['ecommerce']['<all_locales>'] = 'foo';

        $this->normalize($value, 'indexing', ['context'])->shouldReturn($indexingValue);
    }

    function it_normalizes_localizable_values(
        $stdNormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $stdNormalizer->normalize($value, 'indexing', ['context'])->willReturn([
            'scope' => null,
            'locale' => 'fr',
            'data' => 'foo'
        ]);

        $indexingValue = [];
        $indexingValue['attribute-text']['<all_channels>']['fr'] = 'foo';

        $this->normalize($value, 'indexing', ['context'])->shouldReturn($indexingValue);
    }

    function it_normalizes_scopable_and_localizable_values(
        $stdNormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $stdNormalizer->normalize($value, 'indexing', ['context'])->willReturn([
            'scope' => 'ecommerce',
            'locale' => 'fr',
            'data' => 'foo'
        ]);

        $indexingValue = [];
        $indexingValue['attribute-text']['ecommerce']['fr'] = 'foo';

        $this->normalize($value, 'indexing', ['context'])->shouldReturn($indexingValue);
    }

    function it_normalizes_prices(
        $stdNormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_PRICE);
        $attribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);

        $stdNormalizer->normalize($value, 'indexing', ['context'])->willReturn(
            [
                'scope' => null,
                'locale' => null,
                'data' => [
                    [ 'amount' => '45.00', 'currency' => 'USD'],
                    [ 'amount' => '-56.53', 'currency' => 'EUR']
                ]
            ]
        );

        $indexingValue = [];
        $indexingValue['attribute-prices']['<all_channels>']['<all_locales>'] = [
            'USD' => [ 'amount' => '45.00', 'currency' => 'USD'],
            'EUR' => [ 'amount' => '-56.53', 'currency' => 'EUR']
        ];

        $this->normalize($value, 'indexing', ['context'])->shouldReturn($indexingValue);
    }

    function it_omits_prices_without_currency(
        $stdNormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_PRICE);
        $attribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);

        $stdNormalizer->normalize($value, 'indexing', ['context'])->willReturn(
            [
                'scope' => null,
                'locale' => null,
                'data' => [
                    [ 'amount' => '45.00', 'currency' => 'USD'],
                    [ 'amount' => '-56.53']
                ]
            ]
        );

        $indexingValue = [];
        $indexingValue['attribute-prices']['<all_channels>']['<all_locales>'] = [
            'USD' => [ 'amount' => '45.00', 'currency' => 'USD'],
        ];

        $this->normalize($value, 'indexing', ['context'])->shouldReturn($indexingValue);
    }
}
