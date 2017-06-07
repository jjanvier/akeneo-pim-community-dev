<?php


namespace Pim\Component\Catalog\Builder;


use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FlexibleValuesInterface;

class FlexibleValuesBuilder implements FlexibleValuesBuilderInterface
{
    /** @var AttributeValuesResolver */
    protected $valuesResolver;
    /** @var ProductValueFactory */
    protected $productValueFactory;

    public function __construct(
        AttributeValuesResolver $valuesResolver,
        ProductValueFactory $productValueFactory
    ) {
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(FlexibleValuesInterface $values, AttributeInterface $attribute)
    {
        $requiredValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

        foreach ($requiredValues as $value) {
            $this->addOrReplaceValue($values, $attribute, $value['locale'], $value['scope'], null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addOrReplaceValue(
        FlexibleValuesInterface $values,
        AttributeInterface $attribute,
        $locale,
        $scope,
        $data
    ) {
        $valuesValue = $values->getValue($attribute->getCode(), $locale, $scope);
        if (null !== $valuesValue) {
            $values->removeValue($valuesValue);
        }

        $valuesValue = $this->productValueFactory->create($attribute, $scope, $locale, $data);
        $values->addValue($valuesValue);

        // TODO: TIP-722: This is a temporary fix, Product identifier should be used only as a field
        if (AttributeTypes::IDENTIFIER === $attribute->getType() && null !== $data) {
            $values->setIdentifier($valuesValue);
        }

        return $valuesValue;
    }
}
