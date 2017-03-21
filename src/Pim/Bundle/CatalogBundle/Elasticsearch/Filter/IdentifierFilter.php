<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Identifier filter for an Elasticsearch query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const IDENTIFIER_KEY = 'identifier';

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array                        $supportedFields
     * @param array                        $supportedOperators
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkValue($field, $value);

        switch ($operator) {
            case Operators::STARTS_WITH:
                $clause = [
                    'query_string' => [
                        'default_field' => self::IDENTIFIER_KEY,
                        'query'         => $value . '*',
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::CONTAINS:
                $clause = [
                    'query_string' => [
                        'default_field' => self::IDENTIFIER_KEY,
                        'query'         => '*' . $value . '*',
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::DOES_NOT_CONTAIN:
                $mustNotClause = [
                    'query_string' => [
                        'default_field' => self::IDENTIFIER_KEY,
                        'query'         => '*' . $value . '*',
                    ],
                ];

                $filterClause = [
                    'exists' => ['field' => self::IDENTIFIER_KEY],
                ];

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        self::IDENTIFIER_KEY => $value,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        self::IDENTIFIER_KEY => $value,
                    ],
                ];

                $filterClause = [
                    'exists' => [
                        'field' => self::IDENTIFIER_KEY,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields) || $field === $this->attributeRepository->getIdentifierCode();
    }

    /**
     * Checks the identifier is a string
     *
     * @param string $field
     * @param mixed  $value
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkValue($field, $value)
    {
        if (!is_string($value) && null !== $value) {
            throw InvalidPropertyTypeException::stringExpected($field, static::class, $value);
        }
    }
}
