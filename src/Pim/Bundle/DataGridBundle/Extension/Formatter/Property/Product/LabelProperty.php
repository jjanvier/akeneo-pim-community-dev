<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\Product;

use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\FieldProperty;

/**
 * Able to render the label of a product property. Can be a field (e.g family) or a product value (e.g a pim_catalog_simpleselect)
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelProperty extends FieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        $locale = 'fr_FR'; // tmp

        if (isset($value[$locale])) {
            return $value[$locale];
        }
    }
}
