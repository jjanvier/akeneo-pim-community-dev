<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a text area product value
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextAreaNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface &&
            AttributeTypes::BACKEND_TYPE_TEXT === $data->getAttribute()->getBackendType() &&
            'indexing' === $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ProductValueInterface $productValue)
    {
        $cleanedData = str_replace('\r', '', $productValue->getData());
        $cleanedData = str_replace('\n', '', $cleanedData);
        $cleanedData = strip_tags(html_entity_decode($cleanedData));

        return $cleanedData;
    }
}
