<?php

namespace Pim\Bundle\CatalogBundle\Filter;

use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Filter the product values according to locale codes provided in options.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueLocaleFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$productValue instanceof ProductValueInterface) {
            throw new \LogicException('This filter only handles objects of type "ProductValueInterface"');
        }

        $localeCodes = isset($options['locales']) ? $options['locales'] : [];
        $attribute = $productValue->getAttribute();

        return !empty($localeCodes) &&
            $attribute->isLocalizable() &&
            !in_array($productValue->getLocale(), $localeCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($objects, $type, array $options = [])
    {
        foreach ($objects as $key => $object) {
            if ($this->filterObject($object, $type, $options)) {
                $objects->removeKey($key);
            }
        }

        return $objects;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof ProductValueInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return $collection instanceof ProductValueCollectionInterface;
    }
}
