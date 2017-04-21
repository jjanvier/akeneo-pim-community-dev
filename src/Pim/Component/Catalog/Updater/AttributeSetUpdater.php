<?php


namespace Pim\Component\Catalog\Updater;


use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeSetInterface;

class AttributeSetUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeSetInterface $template
     */
    public function update($template, array $data, array $options = [])
    {
        if (!$template instanceof AttributeSetInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($template),
                AttributeSetInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($template, $field, $value);
        }
    }

    /**
     * @param AttributeSetInterface $attributeSet
     * @param                       $field
     * @param                       $value
     *
     * @throws \Exception
     */
    private function setData(AttributeSetInterface $attributeSet, $field, $value)
    {
        switch ($field) {
            case 'attributes':
                $attributes = new ArrayCollection();
                foreach ($value as $attribute) {
                    if (null === $attribute = $this->attributeRepository->findOneByIdentifier($attribute)) {
                        // TODO-CM: wrong exception
                        throw new \Exception(sprintf('Invalid attribute attribute set %s', $attribute));
                    }
                    $attributes->add($attribute);
                }

                $attributeSet->setAttributes($attributes);
                break;
            case 'axes':
                $axes = new ArrayCollection();
                foreach ($value as $attribute) {
                    if (null === $attribute = $this->attributeRepository->findOneByIdentifier($attribute)) {
                        // TODO-CM: wrong exception
                        throw new \Exception(sprintf('Invalid attribute code for axis %s', $attribute));
                    }

                    $axes->add($attribute);
                }

                $attributeSet->setAxes($axes);
                break;
        }
    }
}