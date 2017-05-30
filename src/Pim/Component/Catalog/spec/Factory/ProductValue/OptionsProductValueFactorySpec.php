<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidOptionException;
use Pim\Component\Catalog\Factory\ProductValue\OptionsProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\ProductValue\ScalarProductValue;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class OptionsProductValueFactorySpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $attributeOptionRepository, LoggerInterface $logger)
    {
        $this->beConstructedWith(
            $attributeOptionRepository,
            $logger,
            ScalarProductValue::class,
            'pim_catalog_multiselect'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionsProductValueFactory::class);
    }

    function it_supports_multiselect_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_multiselect')->shouldReturn(true);
    }

    function it_creates_an_empty_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            []
        );

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            []
        );

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foo')->willReturn($option1);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.bar')->willReturn($option2);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['foo', 'bar']
        );

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOptions([$option1, $option2]);
    }

    function it_creates_a_localizable_and_scopable_multi_select_product_value(
        $attributeOptionRepository,
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foo')->willReturn($option1);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.bar') ->willReturn($option2);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['foo', 'bar']
        );

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveTheOptions([$option1, $option2]);
    }

    function it_throws_an_exception_if_provided_data_is_not_an_array(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::arrayExpected(
            'multi_select_attribute',
            OptionsProductValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foobar']);
    }

    function it_throws_an_exception_if_provided_data_is_not_an_array_of_strings(
        $attributeOptionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'multi_select_attribute',
            'one of the options is not a string, "integer" given',
            OptionsProductValueFactory::class,
            [42]
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', [42]]);
    }

    function it_throws_an_exception_if_option_does_not_exist(
        $attributeOptionRepository,
        $logger,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foobar')->willReturn(null);

        $logger
            ->warning('Tried to load a product value for the attribute "multi_select_attribute" with an option "foobar" that does not exist.')
            ->shouldBeCalled();

        $exception = InvalidOptionException::validEntityCodeExpected(
            'multi_select_attribute',
            'code',
            'The options do not exist',
            OptionsProductValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', ['foobar']]);
    }

    function it_logs_a_warning_if_one_option_does_not_exist(
        $attributeOptionRepository,
        $logger,
        AttributeInterface $attribute,
        AttributeOptionInterface $option1
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.foo')->willReturn($option1);
        $attributeOptionRepository->findOneByIdentifier('multi_select_attribute.bar') ->willReturn(null);

        $logger
            ->warning('Tried to load a product value for the attribute "multi_select_attribute" with an option "bar" that does not exist.')
            ->shouldBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['foo', 'bar']
        );

        $productValue->shouldReturnAnInstanceOf(ScalarProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveTheOptions([$option1]);
    }

    public function getMatchers()
    {
        return [
            'haveAttribute'  => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable'  => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'     => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'     => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'    => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'        => function ($subject) {
                return is_array($subject->getData()) && empty($subject->getData());
            },
            'haveTheOptions' => function ($subject, $expectedOptions) {
                $result = false;
                $data = $subject->getData();
                foreach ($data as $option) {
                    $result = in_array($option, $expectedOptions);
                }

                return $result && count($data) === count($expectedOptions);
            },
        ];
    }
}
