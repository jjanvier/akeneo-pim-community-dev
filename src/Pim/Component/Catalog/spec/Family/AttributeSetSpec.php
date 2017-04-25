<?php

namespace spec\Pim\Component\Catalog\Family;

use Pim\Component\Catalog\Family\AttributeSet;
use Pim\Component\Catalog\Family\AttributeSetInterface;
use Pim\Component\Catalog\Family\TemplateInterface;
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

    /**
     * Template or not?
     */
    function it_creates_a_simple_attribute_set(
        TemplateInterface $template,
        AttributeInterface $style,
        AttributeInterface $description
    ) {
        $attributeSet = $this::createSimpleAttributeSet([$style, $description]);
        $attributeSet->shouldHaveType(AttributeSet::class);
        $attributeSet->getAttributes()->shouldReturn([$style, $description]);
    }

    function it_creates_a_variant_attribute_set(
        AttributeInterface $color,
        AttributeInterface $material,
        AttributeInterface $picture
    ) {
        $attributeSet = $this::createVariantAttributeSet([$material, $picture, $color], $color);
        $attributeSet->shouldHaveType(AttributeSet::class);
        $attributeSet->getAttributes()->shouldReturn([$material, $picture]);
    }

    function it_throws_an_exception_if_the_axis_does_not_belong_to_attributes( AttributeInterface $color,
        AttributeInterface $material,
        AttributeInterface $picture
    ) {
        $this->shouldThrow(\Exception::class)->during('createVariantAttributeSet', [[$material, $picture], $color]);
    }

    function it_throws_an_exception_if_the_attributes_does_not_belong_to_the_family( AttributeInterface $color,
        AttributeInterface $material,
        AttributeInterface $picture
    ) {


        $this->shouldThrow(\Exception::class)->during('createVariantAttributeSet', [[$material, $picture], $color]);
    }

    function it_has_axes(
        AttributeInterface $color,
        AttributeInterface $materiel,
        AttributeInterface $picture
    ) {
        $this::createSimpleAttributeSet([$materiel, $picture]);

        $this->getAxes()->willReturn($color);
    }

    /**
     * Throw exception if attribute does not belong to the family
     */
    function its_attributes_are_mutuable(
        AttributeInterface $color,
        AttributeInterface $materiel,
        AttributeInterface $picture
    ) {
        $this::createSimpleAttributeSet([$materiel, $picture]);

        $this->addAttribute($color)->shouldReturn(null);
        $this->getAttributes()->shouldReturn([$materiel, $picture, $color]);

        $this->removeAttribute($color)->shouldReturn(null);
        $this->getAttributes()->shouldReturn([$materiel, $picture]);
    }

//    function it_adds_an_requirement(AttributeInterface $attribute)
//    {
//        $this->addRequirement($attribute)->shouldReturn(null);
//    }

    /**
     * Should we create a custom exception
     */
//    function it_throw_an_exception_if_the_attribute_does_exist(
//        AttributeInterface $modelName,
//        AttributeInterface $materiel,
//        AttributeInterface $picture
//    ) {
//        $this::createSimpleAttributeSet($modelName, [$materiel]);
//
//        $this->shouldThrow(\Exception::class)
//            ->during('addRequirement', [$picture]);
//    }
//
//    function it_removes_an_requirement(AttributeInterface $attribute)
//    {
//        $this->removeRequirement($attribute)->shouldReturn(null);
//    }
}
