<?php

namespace spec\Pim\Component\Catalog\Family;

use Pim\Component\Catalog\Family\Family;
use Pim\Component\Catalog\Family\AttributeSet;
use Pim\Component\Catalog\Family\AttributeSetCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Prophecy\Argument;

class FamilySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Family::class);
    }

    /**
     * QUESTIONS:
     * Should we use the word "simple"?
     * Should we use UUID ?
     */
    function it_creates_simple_family(AttributeSetInterface $simpleSet)
    {
        $this::createVariantFamily('tshirt', $simpleSet, ['en_US' => 'T-shirt'])->shouldReturn($this);
    }

    function it_creates_a_variant_family(AttributeSetInterface $model, AttributeSetInterface $color)
    {
        $this::createSimpleFamily('tshirt', [$model, $color], ['en_US' => 'T-shirt'])->shouldReturn($this);
    }

    /**
     * Should we create a custom exception
     */
    function it_thrown_an_exception_if_attribute_are_not_unique_attribute_set(
        AttributeSetInterface $model,
        AttributeSetInterface $color,
        AttributeInterface $attribute
    ) {
        $model->getAttributes()->willReturn([$attribute]);
        $color->getAttributes()->willReturn([$attribute]);

        $this->shouldThrow(\Exception::class)->during('createSimpleFamily', [[$model, $color]]);
    }

    function it_creates_a_family_without_translations(
        AttributeSetInterface $simpleSet,
        AttributeSetInterface $model,
        AttributeSetInterface $color
    ) {
        $this::createVariantFamily('tshirt', $simpleSet)->shouldReturn($this);
        $this::createSimpleFamily('tshirt', [$model, $color])->shouldReturn($this);
    }

    function it_is_a_family()
    {
        $this->shouldImplement(FamilyInterface::class);
    }


    function it_is_a_simple_family(AttributeSetInterface $model, AttributeSetInterface $color)
    {
        $this::createVariantFamily('tshirt', [$model, $color]);
        $this->isVariant()->shouldReturn(true);
    }

    function it_is_a_variant_family(AttributeSetInterface $simpleSet)
    {
        $this::createSimpleFamily('tshirt', $simpleSet);
        $this->isVariant()->shouldReturn(false);
    }

    function its_attribute_sets_defines_attributes_used_to_build_product_label(
        AttributeSetInterface $model,
        AttributeSetInterface $color,
        AttributeInterface $modelAttribute,
        AttributeInterface $colorAttribute
    ) {
        $this::createVariantFamily('tshirt', [$model, $color]);

        $model->getMainAttribute()->willReturn($modelAttribute);
        $color->getMainAttribute()->willReturn($colorAttribute);

        $this->getProductLabelAttributes()->shouldReturn([
            $model, $color
        ]);
    }

    /**
     * Third parameter could be the family requirement.
     */
    function it_adds_an_attribute_to_attribute_set(AttributeSetInterface $color, AttributeInterface $picture)
    {
        $this->addAttribute($color, $picture, true)->shouldReturn(null);
    }

    /**
     * No need to specify the attribute set because attribute are unique in a family
     */
    function it_remove_an_attribute_to_attribute_set(AttributeInterface $picture)
    {
        $this->removeAttribute($picture)->shouldReturn(null);
    }


    /**
     * Third parameter could be the family requirement.
     */
    function it_adds_an_requirement_to_attribute_set(AttributeSetInterface $color, AttributeInterface $picture)
    {
        $this->addRequirement($color, $picture, true)->shouldReturn(null);
    }

    /**
     * No need to specify the attribute set because attribute are unique in a family
     */
    function it_remove_an_requirement_to_attribute_set(AttributeInterface $picture)
    {
        $this->removeRequirement($picture)->shouldReturn(null);
    }

    function it_has_attributes(
        AttributeSetInterface $model,
        AttributeSetInterface $color,
        AttributeInterface $color,
        AttributeInterface $material,
        AttributeInterface $picture,
        AttributeInterface $modelName,
        AttributeInterface $style,
        AttributeInterface $description
    ) {
        $model->getAttributes()->willReturn([$modelName, $style, $description]);
        $color->getAttributes()->willReturn([$color, $material, $picture]);

        $this::createVariantFamily('tshirt', [$model, $color]);

        $this->getAttributes()->willreturn([$modelName, $style, $description, $color, $material, $picture]);
    }

    function it_has_attributes_required_for_the_product_completeness(
        AttributeSetInterface $model,
        AttributeSetInterface $color,
        AttributeInterface $color,
        AttributeInterface $modelName
    ) {
        $model->getRequiredAttributes()->willReturn([$modelName]);
        $color->getRequiredAttributes()->willReturn([$color]);

        $this::createVariantFamily('tshirt', [$model, $color]);

        $this->getRequiredAttributes()->willreturn($modelName, $color);
    }

    /**
     * Still relevant?
     */
    function it_has_a_code(AttributeSetInterface $simpleSet)
    {
        $this::createVariantFamily('tshirt',  ['en_US' => 'T-shirt'], $simpleSet);
        $this->getCode()->shouldReturn('tshirt');
    }

    /**
     * Still relevant?
     */
    function its_label_is_translatable(AttributeSet $simpleSet)
    {
        $this::createVariantFamily('tshirt',  ['en_US' => 'T-shirt'], $simpleSet);
        $this->getLabel('en_US')->shouldReturn('T-shirt');
    }
}
