<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductPriceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product price to the indexing format.
 * This format is based on the standard format but differs from the following:
 *      - the data is indexed by currency
 *
 * For instance, if in the standard we have:
 *  0 => array:2 [
 *      "amount" => "45.00"
 *      "currency" => "USD"
 *  ]
 *  1 => array:2 [
 *      "amount" => "-56.53"
 *      "currency" => "EUR"
 *  ]
 *
 * Here we'll have:
 *  "USD" => array:2 [
 *      "amount" => "45.00"
 *      "currency" => "USD"
 *  ]
 *  "EUR" => array:2 [
 *      "amount" => "-56.53"
 *      "currency" => "EUR"
 *  ]
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->stdNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($price, $format = null, array $context = [])
    {
        $stdPrices = $this->stdNormalizer->normalize($price, $format, $context);

        $indexingPrices = [];
        foreach ($stdPrices as $stdPrice) {
            if (isset($stdPrice['currency'])) {
                $indexingPrices[$stdPrice['currency']] = $stdPrice;
            }
        }

        return $indexingPrices;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductPriceInterface && 'indexing' === $format;
    }
}
