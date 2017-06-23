<?php

namespace Pim\Component\Catalog\Event;

use Symfony\Component\EventDispatcher\Event;

class ProductUncompletedForChannelAndLocale extends Event
{
    /** @var int */
    private $productId;
    /** @var int */
    private $channelId;
    /** @var int */
    private $localeId;

    public function __construct(int $productId, int $channelId, int $localeId)
    {
        $this->productId = $productId;
        $this->channelId = $channelId;
        $this->localeId = $localeId;
    }
}
