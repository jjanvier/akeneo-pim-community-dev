<?php

namespace Pim\Component\ReferenceData\Normalizer\Indexing\Product;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\AbstractProductValueNormalizer;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer extends AbstractProductValueNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface &&
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION === $data->getAttribute()->getBackendType() &&
            'indexing' === $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ProductValueInterface $productValue)
    {
        $data = $productValue->getData();
        if ($data instanceof ReferenceDataInterface) {
            return $data->getCode();
        }

        return null;
    }
}
