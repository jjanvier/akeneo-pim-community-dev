<?php

namespace Pim\Component\ReferenceData\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\ReferenceData\Exception\InvalidReferenceDataException;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Factory that creates simple-select and multi-select product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionProductValueFactory implements ProductValueFactoryInterface
{
    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     * @param LoggerInterface                          $logger
     * @param string                                   $productValueClass
     * @param string                                   $supportedAttributeType
     */
    public function __construct(
        ReferenceDataRepositoryResolverInterface $repositoryResolver,
        LoggerInterface $logger,
        $productValueClass,
        $supportedAttributeType
    ) {
        $this->repositoryResolver = $repositoryResolver;
        $this->logger = $logger;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new $this->productValueClass(
            $attribute,
            $channelCode,
            $localeCode,
            $this->getReferenceDataCollection($attribute, $data)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks if data is valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data || [] === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('array key "%s" expects a string as value, "%s" given', $key, gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Gets a collection of reference data from an array of codes.
     *
     * @param AttributeInterface $attribute
     * @param array              $referenceDataCodes
     *
     * @throws InvalidReferenceDataException
     * @return array
     */
    protected function getReferenceDataCollection(AttributeInterface $attribute, array $referenceDataCodes)
    {
        $collection = [];

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        foreach ($referenceDataCodes as $referenceDataCode) {
            if (null !== $referenceData = $this->getReferenceData($attribute, $repository, $referenceDataCode)) {
                $collection[] = $referenceData;
            }
        }

        if (empty($collection) && !empty($referenceDataCodes)) {
            throw InvalidReferenceDataException::validEntityCodeExpected(
                $attribute->getCode(),
                'reference data code',
                sprintf('The reference data "%s" do not exist', $attribute->getReferenceDataName()),
                static::class,
                implode(',', $referenceDataCodes)
            );
        }

        return $collection;
    }

    /**
     * Finds a reference data by code.
     *
     * @todo TIP-684: When deleting one element of the collection, we will end up throwing the exception.
     *       Problem is, when loading a product value from single storage, it will be skipped because of
     *       one reference data, when the others in the collection could be valid. So the value will not
     *       be loaded at all, when what we want is the value to be loaded minus the wrong reference data.
     *
     * @param AttributeInterface               $attribute
     * @param ReferenceDataRepositoryInterface $repository
     * @param string                           $referenceDataCode
     *
     * @return ReferenceDataInterface
     */
    protected function getReferenceData(
        AttributeInterface $attribute,
        ReferenceDataRepositoryInterface $repository,
        $referenceDataCode
    ) {
        $referenceData = $repository->findOneBy(['code' => $referenceDataCode]);

        if (null === $referenceData) {
            $this->logger->warning(
                sprintf(
                    'Tried to load a product value for the attribute "%s" '.
                    'with a reference data "%s" that does not exist.',
                    $attribute->getCode(),
                    $referenceDataCode
                )
            );
        }

        return $referenceData;
    }
}
