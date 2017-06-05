<?php


namespace Pim\Component\TemplateAttribute;


use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Impossible to generate all the products of a template is the variant attributes
 * of the bricks are not simple option.
 *
 * But I'm quite sure it's useless to generate all the products directly in the PIM.
 * See that with the PO. How are all products created in the PIM? Just from the import? Manaually?
 */
class ProductsGenerator
{
    /** @var ProductBuilderInterface */
    private $builder;

    /** @var BulkSaverInterface */
    private $saver;

    public function __construct(
        ProductBuilderInterface $builder,
        BulkSaverInterface $saver
    ) {
        $this->builder = $builder;
        $this->saver = $saver;
    }

    /**
     * Here we try to generate all possible products given a product template.
     * It's possible only if axis of the template are simple select :(
     * If it's not the case, how could we generate the products?
     *
     * @param TemplateAttribute $templateAttribute
     *
     * @return ProductInterface[]
     */
    public function generate(TemplateAttribute $templateAttribute)
    {
        $products = [];

        foreach ($this->getOptionsCombinations($templateAttribute) as $optionsCombination) {
            $identifier = $this->generateIdentifier($templateAttribute, $optionsCombination);
            $product = $this->builder->createProduct($identifier, $templateAttribute);
            foreach ($optionsCombination as $option) {
                $value = $this->builder->addProductValue($product, $option->getAttribute());
                $value->setOption($option);
            }

            $this->builder->addMissingProductValues($product);
            $products[] =$product;
        }

        $this->saver->saveAll($products);

        return $products;
    }

    /**
     * @param TemplateAttribute $templateAttribute
     *
     * @return array
     */
    private function getOptionsCombinations(TemplateAttribute $templateAttribute)
    {
        if (empty($templateAttribute->getVariantAttributes())) {
            return [[]];
        }

        $options = [];
        foreach ($templateAttribute->getVariantAttributes() as $variantAttribute) {
            //TODO: shitty let's imagine we have 10000 options in the attribute
            $options[$variantAttribute->getCode()] = $variantAttribute->getOptions()->toArray();
        }

        return $this->generateCombinations($options);
    }

    /**
     * Comes from http://stackoverflow.com/questions/8567082/how-to-generate-in-php-all-combinations-of-items-in-multiple-arrays
     *
     * @param array $array
     *
     * @return array
     */
    private function generateCombinations(array $array) {
        foreach (array_pop($array) as $value) {
            if (count($array)) {
                foreach ($this->generateCombinations($array) as $combination) {
                    yield array_merge([$value], $combination);
                };
            } else {
                yield [$value];
            }
        }
    }

    /**
     * @param TemplateAttribute $templateAttribute
     * @param array             $optionsCombination
     *
     * @return string
     */
    private function generateIdentifier(TemplateAttribute $templateAttribute, array $optionsCombination)
    {
        $identifier = str_replace(' ', '_', $templateAttribute->getName()) . '-sku-' . uniqid();

        foreach ($optionsCombination as $option) {
            $identifier .= '-' . (string) $option;
        }

        return $identifier;
    }
}
