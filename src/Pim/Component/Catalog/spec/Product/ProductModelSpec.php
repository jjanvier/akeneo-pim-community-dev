<?php

namespace spec\Pim\Component\Catalog\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Product\ProductModel;
use Pim\Component\Catalog\Product\ProductModelInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelSpec extends ObjectBehavior
{
    /**
     * Question:
     * - Difference with a product? Do we need that model? Should we use the product?
     * - Completeness?
     * - Do we need more information like categorization? Could be interesting case for permission.
     */
    function let(
        FamilyInterface $family,
        ProductValueCollectionInterface $productValueCollection,
        ArrayCollection $productModels
    ) {
        $this->beConstructedWith($family, $productValueCollection, $productModels);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModel::class);
    }

    function it_is_a_product_model()
    {
        $this->shouldImplement(ProductModelInterface::class);
    }

    function its_values_are_mutable(ProductValueInterface $sku, ProductValueInterface $name)
    {
        $this->addValue($sku)->shouldReturn(null);
        $this->addValue($name)->shouldReturn(null);

        $values = $this->getValue();
        $values->shouldHaveType(ProductValueInterface::class);
        $values->count()->shouldReturn(2);

        $this->removeValue($sku)->shouldReturn(null);
        $values->count()->shouldReturn(1);
    }

    /**
     * How can we manage the last level?
     * Could we use the same model for product and product model?
     */
    function it_has_product_models($productModels)
    {
        $this->getProductModels()->shouldReturn($productModels);
    }

    /**
     * Tech tip: composite pattern
     * TODO: look at the form component
     */
    function its_product_models_are_mutable(ProductModelInterface $blueTshirt, ProductModelInterface $redTshirt)
    {
        $this->addValue($blueTshirt)->shouldReturn(null);
        $this->addValue($redTshirt)->shouldReturn(null);

        $values = $this->getValue();
        // Should we need to create a custom collection ?
        $values->shouldHaveType(ArrayCollection::class);
        $values->count()->shouldReturn(2);

        $this->removeValue($blueTshirt)->shouldReturn(null);
        $values->count()->shouldReturn(1);
    }

    /**
     * Question: Do you need to family at this place?
     */
    function it_has_a_family($family)
    {
        $this->getFamily()->shouldReturn($family);
    }
}
