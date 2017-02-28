<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;

class SimpleCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_supports_all_product_values(
        ProductValueInterface $productValue
    ) {
        $this->supportsValue($productValue)->shouldReturn(true);
    }

    public function it_checks_empty_lists(
        ProductValueInterface $productValue,
        Collection $collection
    ) {
        $productValue->getData()->willReturn([]);
        $this->isComplete($productValue)->shouldReturn(false);

        $productValue->getData()->willReturn([null, '']);
        $this->isComplete($productValue)->shouldReturn(false);

        $productValue->getData()->willReturn($collection);
        $this->isComplete($productValue)->shouldReturn(false);

        $collection->add(null);
        $collection->add('');
        $productValue->getData()->willReturn($collection);
        $this->isComplete($productValue)->shouldReturn(false);
    }

    public function it_checks_complete_lists(
        ProductValueInterface $productValue,
        Collection $collection
    ) {
        $productValue->getData()->willReturn([null, 'bar']);
        $this->isComplete($productValue)->shouldReturn(true);

        $collection->getIterator()->willReturn(new \ArrayIterator([null, 'bar']));
        $collection->count()->willReturn(2);
        $productValue->getData()->willReturn($collection);
        $this->isComplete($productValue)->shouldReturn(true);
    }

    public function it_checks_incomplete_scalars(ProductValueInterface $productValue)
    {
        $productValue->getData()->willReturn(null);
        $this->isComplete($productValue)->shouldReturn(false);

        $productValue->getData()->willReturn('');
        $this->isComplete($productValue)->shouldReturn(false);
    }

    public function it_checks_complete_scalars(ProductValueInterface $productValue)
    {
        $productValue->getData()->willReturn(false);
        $this->isComplete($productValue)->shouldReturn(true);

        $productValue->getData()->willReturn(0);
        $this->isComplete($productValue)->shouldReturn(true);

        $productValue->getData()->willReturn(0.0);
        $this->isComplete($productValue)->shouldReturn(true);

        $productValue->getData()->willReturn('foo');
        $this->isComplete($productValue)->shouldReturn(true);
    }
}
