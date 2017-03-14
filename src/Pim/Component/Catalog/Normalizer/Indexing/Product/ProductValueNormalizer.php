<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value to the indexing format.
 * This format is based on the standard format but differs from the following:
 *      - the attribute code is suffixed by its backend type
 *      - value is indexed by attribute type + backend type...
 *      - ...then by channel...
 *      - ... and finally by locale
 *
 * If the attribute related to the value is not scopable, here the value will be indexed with the key "<all_channels>".
 * If the attribute related to the value is not localizable, here the value will be indexed with the key "<all_locales>".
 *
 * For instance, imagine we have the following value normalized to the standard format:
 *  "auto_exposure" => array:1 [
 *      0 => array:3 [
 *          "locale" => null
 *          "scope" => null
 *          "data" => true
 *      ]
 *  ]
 * where the attribute "a_number_float" (non localizable and non scopable)  is a "pim_catalog_number" attribute.
 *
 * Here we'll have:
 *  "auto_exposure-boolean" => array:1 [
 *      0 => array:1 [
 *          "<all_channels>" => array:1 [
 *              "<all_locales>" => true
 *          ]
 *      ]
 *  ]
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
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
    public function normalize($value, $format = null, array $context = [])
    {
        $stdValue = $this->stdNormalizer->normalize($value, $format, $context);

        $attribute = $value->getAttribute()->getCode();
        $attributeBackendType = $value->getAttribute()->getBackendType();
        $channel = null !== $stdValue['scope'] ? $stdValue['scope'] : '<all_channels>';
        $locale = null !== $stdValue['locale'] ? $stdValue['locale'] : '<all_locales>';

        $indexingValue = [];
        $indexingValue[$attribute . '-' . $attributeBackendType][$channel][$locale] = $stdValue['data'];

        return $indexingValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && 'indexing' === $format;
    }
}
