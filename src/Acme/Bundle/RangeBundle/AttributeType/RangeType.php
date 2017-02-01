<?php

namespace Acme\Bundle\RangeBundle\AttributeType;

use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RangeType extends AbstractAttributeType
{
    const RANGE = 'acme_type_range';
    const BACKEND_TYPE_RANGE = 'range';

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
                'numberMin' => [
                    'name'      => 'numberMin',
                    'fieldType' => 'pim_number'
                ],
                'numberMax' => [
                    'name'      => 'numberMax',
                    'fieldType' => 'pim_number'
                ],
                'decimalsAllowed' => [
                    'name'      => 'decimalsAllowed',
                    'fieldType' => 'switch',
                    'options'   => [
                        'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                    ]
                ]
            ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::RANGE;
    }
}
