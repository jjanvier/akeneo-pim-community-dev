<?php

namespace Pim\Bundle\CatalogBundle\ElasticSearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\FilterInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

abstract class AbstractFilter implements FilterInterface
{
    /** @var array */
    protected $clauses;

    /** @var array */
    protected $supportedOperators;

    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var string[] */
    protected $supportedAttributeTypes;

    /** @var array */
    protected $supportedFields;

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes()
    {
        return $this->supportedAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributeTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->supportedFields;
    }

    public function setQueryBuilder($queryBuilder)
    {
        // TODO: no sense now, should be dropped from the interface
    }

    /**
     * Check locale and scope are valid
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @throws InvalidPropertyException
     */
    protected function checkLocaleAndScope(AttributeInterface $attribute, $locale, $scope)
    {
        try {
            $this->attrValidatorHelper->validateLocale($attribute, $locale);
            $this->attrValidatorHelper->validateScope($attribute, $scope);
        } catch (\LogicException $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }
    }
}
