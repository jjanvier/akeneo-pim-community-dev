<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\FlexibleValuesInterface;

class FlexibleValuesUpdater implements ObjectUpdaterInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /**
     * @param PropertySetterInterface $propertySetter
     */
    public function __construct(PropertySetterInterface $propertySetter)
    {
        $this->propertySetter = $propertySetter;
    }

    public function update($object, array $values, array $options = [])
    {
        //TODO: throw exception if not FlexibleValuesInterface

        $this->checkProductValuesData($values);
        $this->updateProductValues($object, $values);

        return $this;
    }

    /**
     * Sets the product values,
     *  - always set values related to family's attributes
     *  - sets optional values (not related to family's attributes) when a data is provided
     *  - sets optional values (not related to family's attributes) with empty data if value already exists
     *
     * @param FlexibleValuesInterface $flexibleValues
     * @param array            $values
     */
    protected function updateProductValues(FlexibleValuesInterface $flexibleValues, array $values)
    {
        //TODO: we don't care anymore about the optional attributes
        /*
        $family = $flexibleValues->getFamily();
        $authorizedCodes = (null !== $family) ? $family->getAttributeCodes() : [];
        */

        foreach ($values as $code => $value) {
            //TODO: we don't care anymore about the optional attributes
            //$isFamilyAttribute = in_array($code, $authorizedCodes);
            $isFamilyAttribute = true;

            foreach ($value as $data) {
                $hasValue = $flexibleValues->getValue($code, $data['locale'], $data['scope']);
                $providedData = ('' === $data['data'] || [] === $data['data'] || null === $data['data']) ? false : true;

                if ($isFamilyAttribute || $providedData || $hasValue) {
                    $options = ['locale' => $data['locale'], 'scope' => $data['scope']];
                    $this->propertySetter->setData($flexibleValues, $code, $data['data'], $options);
                }
            }
        }
    }

    /**
     * Check the structure of the product values.
     *
     * @param mixed $flexibleValues
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkProductValuesData($flexibleValues)
    {
        if (!is_array($flexibleValues)) {
            throw InvalidPropertyTypeException::arrayExpected('values', static::class, $flexibleValues);
        }

        foreach ($flexibleValues as $code => $values) {
            if (!is_array($values)) {
                throw InvalidPropertyTypeException::arrayExpected($code, static::class, $values);
            }

            foreach ($values as $productValue) {
                if (!is_array($productValue)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $code,
                        'one of the product values is not an array.',
                        static::class,
                        $values
                    );
                }

                if (!array_key_exists('locale', $productValue)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'locale', static::class, $productValue);
                }

                if (!array_key_exists('scope', $productValue)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'scope', static::class, $productValue);
                }

                if (!array_key_exists('data', $productValue)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'data', static::class, $productValue);
                }

                if (null !== $productValue['locale'] && !is_string($productValue['locale'])) {
                    $message = 'Property "%s" expects a product value with a string as locale, "%s" given.';

                    throw new InvalidPropertyTypeException(
                        $code,
                        $productValue['locale'],
                        static::class,
                        sprintf($message, $code, gettype($productValue['locale'])),
                        InvalidPropertyTypeException::STRING_EXPECTED_CODE
                    );
                }

                if (null !== $productValue['scope'] && !is_string($productValue['scope'])) {
                    $message = 'Property "%s" expects a product value with a string as scope, "%s" given.';

                    throw new InvalidPropertyTypeException(
                        $code,
                        $productValue['scope'],
                        static::class,
                        sprintf($message, $code, gettype($productValue['scope'])),
                        InvalidPropertyTypeException::STRING_EXPECTED_CODE
                    );
                }
            }
        }
    }
}
