<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Check if a product price collection data is filled in or not.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal for internal use only, please use the \Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface
 *           to calculate the completeness on a product
 */
class PriceCompleteChecker implements ProductValueCompleteCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ProductValueInterface $productValue,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        $expectedCurrencies = $this->getCurrencyCodesToCheck($channel);

        foreach ($expectedCurrencies as $currency) {
            foreach ($productValue->getData() as $price) {
                if ($price->getCurrency() === $currency && null === $price->getData()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(ProductValueInterface $productValue)
    {
        return AttributeTypes::PRICE_COLLECTION === $productValue->getAttribute()->getAttributeType();
    }

    /**
     * @param ChannelInterface|null $channel
     *
     * @return array
     */
    private function getCurrencyCodesToCheck(ChannelInterface $channel = null)
    {
        if (null === $channel) {
           return [];
        }

        return array_map(
            function ($currency) { return $currency->getCode(); },
            $channel->getCurrencies()->toArray()
        );
    }
}
