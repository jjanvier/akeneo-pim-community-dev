<?php

namespace spec\Pim\Component\Catalog\Product;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Product\Product;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Product::class);
    }

    function it_is_a_product()
    {
        $this->shouldImplement(ProductInterface::class);
    }
}
