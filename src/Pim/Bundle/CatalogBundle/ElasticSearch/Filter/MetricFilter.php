<?php

namespace Pim\Bundle\CatalogBundle\ElasticSearch\Filter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Metric Filter
 */
class MetricFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var OptionsResolver */
    protected $resolver;

    /** @var MeasureManager */
    protected $measureManager;

    /** @var MeasureConverter */
    protected $measureConverter;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param MeasureManager           $measureManager
     * @param MeasureConverter         $measureConverter
     * @param array                    $supportedAttributeTypes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->measureManager = $measureManager;
        $this->measureConverter = $measureConverter;
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
            $this->checkValue($attribute, $value);
            $value = $this->convertValue($attribute, $value);
        }

        $attributePath = $this->getAttributePath($attribute, $locale, $scope);

        switch ($operator) {
            case Operators::LOWER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['lt' => $value]
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['lte' => $value]
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $attributePath => $value
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['gte' => $value]
                    ]
                ];
                $this->clauses->addFilterClause($clause);

                break;
            case Operators::GREATER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['gt' => $value]
                    ]
                ];
                $this->clauses->addFilterClause($clause);

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
            case Operators::NOT_EQUAL:
                $clause = [
                    'term' => [
                        $attributePath => $value
                    ]
                ];
                $this->clauses->addMustNotClause($clause);

                break;
            default:
                throw new InvalidArgumentException('TODO');
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkValue(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attribute->getCode(), static::class, $data);
        }

        if (!array_key_exists('amount', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'amount',
                static::class,
                $data
            );
        }

        if (!array_key_exists('unit', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'unit',
                static::class,
                $data
            );
        }

        if (null !== $data['amount'] && !is_numeric($data['amount'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "amount" has to be a numeric, "%s" given', gettype($data['amount'])),
                static::class,
                $data
            );
        }

        if (!is_string($data['unit'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "unit" has to be a string, "%s" given', gettype($data['unit'])),
                static::class,
                $data
            );
        }

        if (!array_key_exists(
            $data['unit'],
            $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily())
        )) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'unit',
                sprintf(
                    'The unit does not exist in the attribute\'s family "%s"',
                    $attribute->getMetricFamily()
                ),
                static::class,
                $data['unit']
            );
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $data
     *
     * @return float
     */
    protected function convertValue(AttributeInterface $attribute, array $data)
    {
        $this->measureConverter->setFamily($attribute->getMetricFamily());

        return $this->measureConverter->convertBaseToStandard($data['unit'], $data['amount']);
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
        if ('pim_catalog_metric' === $attribute->getAttributeType()) {
            return '-metric';
        }

        throw new InvalidArgumentException('Unknown attribute type');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSuffixPath()
    {
        return '.base_data';
    }
}
