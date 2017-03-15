<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductValuesNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValuesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValuesNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_indexing_format_and_collection_values()
    {
        $attribute = new Attribute();
        $attribute->setCode('attribute');
        $attribute->setBackendType('text');
        $realValue = new ProductValue($attribute, null, null, null);

        $valuesCollection = new ProductValueCollection([$realValue]);
        $valuesArray = [$realValue];
        $emptyValuesCollection = new ProductValueCollection();
        $randomCollection = new ArrayCollection([new \stdClass()]);
        $randomArray = [new \stdClass()];

        $this->supportsNormalization($valuesCollection, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($valuesArray, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($emptyValuesCollection, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($randomCollection, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($randomArray, 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($valuesCollection, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_collection_of_product_values_in_indexing_format(
        $serializer,
        ProductValueInterface $textValue,
        AttributeInterface $textAttribute,
        ProductValueInterface $descriptionEcommerceFrValue,
        ProductValueInterface $descriptionEcommerceEnValue,
        ProductValueInterface $descriptionPrintFrValue,
        AttributeInterface $descriptionAttribute,
        ProductValueCollectionInterface $values,
        \ArrayIterator $valuesIterator
    ) {
        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, true, true, false);
        $valuesIterator->current()->willReturn(
            $textValue,
            $descriptionEcommerceFrValue,
            $descriptionEcommerceEnValue,
            $descriptionPrintFrValue
        );
        $valuesIterator->next()->shouldBeCalled();

        $textValue->getAttribute()->willReturn($textAttribute);
        $descriptionEcommerceFrValue->getAttribute()->willReturn($descriptionAttribute);

        $textAttribute->getCode()->willReturn('text');
        $descriptionAttribute->getCode()->willReturn('description');

        $rawTextValue = [];
        $rawTextValue['text']['<all_channels>']['<all_locales>'] = 'foo';

        $serializer
            ->normalize($textValue, 'indexing', [])
            ->shouldBeCalled()
            ->willReturn($rawTextValue);

        $rawDescriptionEcommerceFr = [];
        $rawDescriptionEcommerceFr['description']['ecommerce']['fr'] = 'desc eco fr';

        $serializer
            ->normalize($descriptionEcommerceFrValue, 'indexing', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionEcommerceFr);

        $rawDescriptionEcommerceEn = [];
        $rawDescriptionEcommerceEn['description']['ecommerce']['en'] = 'desc eco en';

        $serializer
            ->normalize($descriptionEcommerceEnValue, 'indexing', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionEcommerceEn);

        $rawDescriptionPrintFr = [];
        $rawDescriptionPrintFr['description']['print']['fr'] = 'desc print fr';

        $serializer
            ->normalize($descriptionPrintFrValue, 'indexing', [])
            ->shouldBeCalled()
            ->willReturn($rawDescriptionPrintFr);

        $this
            ->normalize($values, 'indexing')
            ->shouldReturn(
                [
                    'text'   => [
                        '<all_channels>' => [
                            '<all_locales>' => 'foo',
                        ],
                    ],
                    'description'  => [
                        'ecommerce' => [
                            'fr' => 'desc eco fr',
                            'en' => 'desc eco en',
                        ],
                        'print' => [
                            'fr' => 'desc print fr',
                        ],
                    ],
                ]
            );
    }
}
