<?php

namespace Pim\Component\Catalog\Event;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\Event;

class FulfilledValueEvent extends Event
{
    /** @var string */
    private $productId;

    /** @var ProductValueInterface */
    private $value;

    public function __construct(string $productId, ProductValueInterface $value)
    {
        $this->productId = $productId;
        $this->value = $value;
    }
}
