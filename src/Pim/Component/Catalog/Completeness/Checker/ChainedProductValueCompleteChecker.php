<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Chained checker that contains all the product value completeness checkers.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal for internal use only, please use the \Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface
 *           to calculate the completeness on a product
 */
class ChainedProductValueCompleteChecker implements ProductValueCompleteCheckerInterface
{
    /** @var ProductValueCompleteCheckerInterface[] */
    protected $productValueCheckers = [];

    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ProductValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        foreach ($this->productValueCheckers as $productValueChecker) {
            if ($productValueChecker->supportsValue($productValue, $channel, $locale)
                && !$productValueChecker->isComplete($productValue, $channel, $locale)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(
        ProductValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        foreach ($this->productValueCheckers as $productValueChecker) {
            if ($productValueChecker->supportsValue($productValue, $channel, $locale)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ProductValueCompleteCheckerInterface $checker
     */
    public function addProductValueChecker(ProductValueCompleteCheckerInterface $checker)
    {
        $this->productValueCheckers[] = $checker;
    }
}
