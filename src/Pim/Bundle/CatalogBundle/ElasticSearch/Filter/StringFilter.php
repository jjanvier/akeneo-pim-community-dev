<?php

namespace Pim\Bundle\CatalogBundle\ElasticSearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * String Filter
 */
class StringFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributeTypes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;

        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                static::class
            );
        }

        $this->checkLocaleAndScope($attribute, $locale, $scope);

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($options['field'], $value);
        }

        $attributePath = $this->getAttributePath($attribute, $locale, $scope);

        switch ($operator) {
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $attributePath => $value
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::NOT_EQUAL:
                $clause = [
                    'term' => [
                        $attributePath => $value
                    ]
                ];
                $this->clauses->addMustNotClause($clause);

                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath
                    ]
                ];
                $this->clauses->addMustNotClause($clause);

                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::CONTAINS:
                $clause = [
                    'query_string' => [
                        'default_field' => $attributePath,
                        'query' => '*' . $value . '*'
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::DOES_NOT_CONTAIN:
                $clause = [
                    'query_string' => [
                        'default_field' => $attributePath,
                        'query' => '*' . $value . '*'
                    ]
                ];
                $this->clauses->addMustNotClause($clause);

                break;
            case Operators::STARTS_WITH:
                $clause = [
                    'query_string' => [
                        'default_field' => $attributePath,
                        'query' => $value . '*'
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::ENDS_WITH:
                $clause = [
                    'query_string' => [
                        'default_field' => $attributePath,
                        'query' => '*' . $value
                    ]
                ];
                $this->clauses->addFilterClause($clause);
                
                break;
            default:
                throw new InvalidArgumentException('TODO');
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function checkValue($field, $value)
    {
        if (is_array($value)) {
            foreach ($value as $scalarValue) {
                $this->checkScalarValue($field, $scalarValue);
            }
        } else {
            $this->checkScalarValue($field, $value);
        }
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkScalarValue($field, $value)
    {
        if (!is_string($value) && null !== $value) {
            throw InvalidPropertyTypeException::stringExpected($field, static::class, $value);
        }
    }

    /**
     * Configure the option resolver
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['field']);
        $resolver->setDefined(['locale', 'scope']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeType(AttributeInterface $attribute)
    {
        if ('pim_catalog_text' === $attribute->getAttributeType()) {
            return '-text';
        } elseif ('pim_catalog_textarea' === $attribute->getAttributeType()) {
            return '-text_area';
        }

        throw new InvalidArgumentException('Unknown attribute type');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSuffixPath()
    {
        return '';
    }
}
