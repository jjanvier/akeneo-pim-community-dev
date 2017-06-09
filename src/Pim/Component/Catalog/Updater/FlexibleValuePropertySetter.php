<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FlexibleValuesInterface;
use Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface;
use Pim\Component\Catalog\Updater\Setter\SetterRegistryInterface;

class FlexibleValuePropertySetter implements PropertySetterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param SetterRegistryInterface               $setterRegistry
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        SetterRegistryInterface $setterRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->setterRegistry = $setterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($object, $field, $data, array $options = [])
    {
        if (!$object instanceof FlexibleValuesInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($object),
                FlexibleValuesInterface::class
            );
        }

        $setter = $this->setterRegistry->getSetter($field);
        if (null === $setter) {
            throw UnknownPropertyException::unknownProperty($field);
        }

        if (!$setter instanceof AttributeSetterInterface) {
            //TODO: throw exception, should not happen
        }

        $attribute = $this->getAttribute($field);
        $setter->setAttributeData($object, $attribute, $data, $options);

        return $this;
    }

    /**
     * @param string $code
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute($code)
    {
        return $this->attributeRepository->findOneByIdentifier($code);
    }
}
