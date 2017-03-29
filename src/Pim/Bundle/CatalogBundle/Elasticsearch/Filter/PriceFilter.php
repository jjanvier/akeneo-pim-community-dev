<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Price filter for an Elasticsearch query
 *
 * The IS_EMPTY Operator is now deprecated, please use IS_EMPTY_ON_ALL_LOCALES instead
 * The IS_NOT_EMPTY Operator is now deprecated, please use IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY instead
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /**
     * @param AttributeValidatorHelper    $attrValidatorHelper
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param array                       $supportedAttributeTypes
     * @param array                       $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyRepositoryInterface $currencyRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->currencyRepository = $currencyRepository;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkLocaleAndChannel($attribute, $locale, $channel);

        if (Operators::IS_EMPTY === $operator || Operators::IS_NOT_EMPTY === $operator) {
            if (!array_key_exists('amount', $value)) {
                $value['amount'] = null;
            }
            if (!array_key_exists('currency', $value)) {
                $value['currency'] = null;
            } else {
                $this->checkCurrency($attribute, $value);
            }
        } else {
            $this->checkValue($attribute, $value);
        }

        $attributePath = $this->getAttributePathForCurrency($attribute, $locale, $channel, $value);

        switch ($operator) {
            case Operators::LOWER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['lt' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['lte' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $attributePath => $value['amount'],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        $attributePath => $value['amount'],
                    ],
                ];
                $filterClause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['gte' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::GREATER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['gt' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Checks that the value is correctly set
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

        if (!array_key_exists('currency', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'currency',
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

        $this->checkCurrency($attribute, $data);
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $data
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkCurrency(AttributeInterface $attribute, $data)
    {
        if (!array_key_exists('currency', $data) || !is_string($data['currency'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "currency" has to be a string, "%s" given', gettype($data['currency'])),
                static::class,
                $data
            );
        }

        if ('' === $data['currency'] || null !== $data['currency']) {
            throw InvalidPropertyException::valueNotEmptyExpected(
                $attribute->getCode(),
                'currency',
                'The currency does not exist',
                static::class,
                $data['currency']
            );
        }

        if (!in_array($data['currency'], $this->currencyRepository->getActivatedCurrencyCodes())) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'currency',
                'The currency does not exist',
                static::class,
                $data['currency']
            );
        }
    }

    /**
     * Returns the attribute path with the currency if it exists
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $channel
     * @param array              $value
     *
     * @return string
     */
    protected function getAttributePathForCurrency($attribute, $locale, $channel, array $value)
    {
        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        if (null !== $value['currency'] && '' !== $value['currency']) {
            $attributePath .= '.' . $value['currency'];
        }

        return $attributePath;
    }
}
