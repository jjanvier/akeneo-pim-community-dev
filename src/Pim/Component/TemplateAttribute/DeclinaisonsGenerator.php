<?php


namespace Pim\Component\TemplateAttribute;


use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Model\DeclinaisonValue;

class DeclinaisonsGenerator
{
    /** @var AttributeValuesResolver */
    private $valuesResolver;

    /**
     * @param TemplateAttribute $templateAttribute
     *
     * @return Declinaison[]
     */
    public function generate(TemplateAttribute $templateAttribute)
    {
        $varaintBricks = $templateAttribute->getVariantBricks();
        if (count($varaintBricks)) {
            return [];
        }

        $brickLevel1 = $varaintBricks[0];
        $brickLevel2 = isset($varaintBricks[1]) ? $varaintBricks[1] : null;
        $optionsLevel1 = $brickLevel1->getVariantAttribute()->getOptions();
        $optionsLevel2 = null !== $brickLevel2 ? $brickLevel2->getVariantAttribute()->getOptions() : [];

        foreach ($optionsLevel1 as $option1) {

            $declinaisonParent = new Declinaison($templateAttribute);
            if (null !== $brickLevel2) {
                foreach ($optionsLevel2 as $option2) {

                    $declinaisonChild = new Declinaison($templateAttribute, $declinaisonParent);
                }
            }
        }

    }

    private function generateCommonValuesFirstLevel(TemplateAttribute $templateAttribute)
    {
        $values = [];

        $expectedAttributes = $templateAttribute->getRegularAttributes();
        if (0 < count($templateAttribute->getVariantBricks())) {
            $brick = $templateAttribute->getVariantBricks()[0];
            $expectedAttributes = array_merge($expectedAttributes, $brick->getAttributes());
        }

        $requiredValues = $this->valuesResolver->resolveEligibleValues($expectedAttributes);
        foreach ($requiredValues as $requiredValue) {

            $value = new DeclinaisonValue();
            $value->setAttribute($expectedAttributes[$requiredValue['attribute']]);
            $value->setLocale($requiredValue['locale']);
            $value->setScope($requiredValue['scope']);

            $values[] = $value;
        }

        return $values;
    }
}
