<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FlexibleValuesInterface;

interface FlexibleValuesBuilderInterface
{
    /**
     * Creates required value(s) to add the attribute to the product
     *
     * @param FlexibleValuesInterface   $values
     * @param AttributeInterface $attribute
     */
    public function addAttribute(FlexibleValuesInterface $values, AttributeInterface $attribute);

    /**
     * Add or replace a product value.
     *
     * @param FlexibleValuesInterface   $values
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     * @param mixed              $data
     *
     * @return FlexibleValuesInterface
     */
    public function addOrReplaceValue(
        FlexibleValuesInterface $values,
        AttributeInterface $attribute,
        $locale,
        $scope,
        $data
    );
}
