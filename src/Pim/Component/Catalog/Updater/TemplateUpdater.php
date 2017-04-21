<?php


namespace Pim\Component\Catalog\Updater;


use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\TemplateInterface;

class TemplateUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    private $attributeSetUpdater;

    /** @var SimpleFactoryInterface */
    private $attributeSetFactory;

    public function __construct(
        SimpleFactoryInterface $attributeSetFactory,
        ObjectUpdaterInterface $attributeSetUpdater
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetUpdater = $attributeSetUpdater;
    }

    /**
     * {@inheritdoc}
     *
     * @param TemplateInterface $template
     */
    public function update($template, array $data, array $options = [])
    {
        if (!$template instanceof TemplateInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($template),
                TemplateInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($template, $field, $value);
        }
    }

    /**
     * @param TemplateInterface $template
     * @param string            $field
     * @param mixed             $value
     */
    private function setData(TemplateInterface $template, $field, $value)
    {
        switch ($field) {
            case 'attribute_sets':
                $attributeSets = new ArrayCollection();
                foreach ($value as $attributeSetValues) {
                    // Only work for create, how can we manage update?
                    $attributeSet = $this->attributeSetFactory->create();
                    $attributeSet->setTemplate($template);

                    $this->attributeSetUpdater->update($attributeSet, $attributeSetValues);
                    $attributeSets->add($attributeSet);
                }

                $template->setAttributeSets($attributeSets);
                break;
            case 'code':
                $template->setCode($value);
                break;
        }
    }
}