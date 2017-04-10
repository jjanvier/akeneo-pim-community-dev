<?php

namespace spec\Pim\Component\Catalog\Family;

use Pim\Component\Catalog\Family\AttributeSet;
use Pim\Component\Catalog\Family\AttributeSetInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;

class AttributeSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeSet::class);
    }

    function it_is_an_attribute_set()
    {
        $this->shouldImplement(AttributeSetInterface::class);
    }

    function it_creates_a_simple_attribute_set(
        AttributeInterface $modelName,
        AttributeInterface $style,
        AttributeInterface $description
    ) {
        $this::createSimpleAttributeSet($modelName, [$style, $description], [$style])
            ->shouldHaveType(AttributeSet::class);
    }

    function it_creates_a_variant_attribute_set(
        AttributeInterface $color,
        AttributeInterface $material,
        AttributeInterface $picture
    ) {
        $this::createVariantAttributeSet($color, [$material, $picture], [$picture])
            ->shouldHaveType(AttributeSet::class);
    }

    function its_main_attribute_is_required_by_default(
        AttributeInterface $color,
        AttributeInterface $material,
        AttributeInterface $picture,
        AttributeInterface $modelName,
        AttributeInterface $style,
        AttributeInterface $description
    ) {
        $this::createSimpleAttributeSet($modelName, [$style, $description], [$style]);
        $this->getRequiredAttributes()->willReturn([$modelName, $style]);

        $this::createVariantAttributeSet($color, [$material, $picture], [$picture]);
        $this->getRequiredAttributes()->willReturn([$color, $picture]);
    }

    function it_has_attributes(
        AttributeInterface $modelName,
        AttributeInterface $style,
        AttributeInterface $description
    ) {
        $this::createSimpleAttributeSet($modelName, [$style, $description], [$style])->shouldHaveType(AttributeSet::class);

        $this->getAttributes()->willreturn($modelName, $style, $description);
    }

    function it_has_attributes_required_for_the_product_completeness(
        AttributeInterface $modelName,
        AttributeInterface $style,
        AttributeInterface $description
    ) {
        $this::createSimpleAttributeSet($modelName, [$style, $description], [$style])->shouldHaveType(AttributeSet::class);

        $this->getRequiredAttributes()->willreturn($modelName, $style);
    }

    /**
     * TODO: That method does not match any word defined in ubiquitous language
     */
    function it_has_a_main_attribute(
        AttributeInterface $modelName,
        AttributeInterface $materiel,
        AttributeInterface $picture
    ) {
        $this::createSimpleAttributeSet($modelName, [$materiel, $picture]);

        $this->getMainAttribute()->willReturn($modelName);
    }

    function it_adds_an_attribute(AttributeInterface $attribute)
    {
        $this->addAttribute($attribute)->shouldReturn(null);
    }

    function it_removes_an_attribute(AttributeInterface $attribute)
    {
        $this->removeAttribute($attribute)->shouldReturn(null);
    }

    function it_adds_an_requirement(AttributeInterface $attribute)
    {
        $this->addRequirement($attribute)->shouldReturn(null);
    }

    /**
     * Should we create a custom exception
     */
    function it_throw_an_exception_if_the_attribute_does_exist(
        AttributeInterface $modelName,
        AttributeInterface $materiel,
        AttributeInterface $picture
    ) {
        $this::createSimpleAttributeSet($modelName, [$materiel]);

        $this->shouldThrow(\Exception::class)
            ->during('addRequirement', [$picture]);
    }

    function it_removes_an_requirement(AttributeInterface $attribute)
    {
        $this->removeRequirement($attribute)->shouldReturn(null);
    }
}
