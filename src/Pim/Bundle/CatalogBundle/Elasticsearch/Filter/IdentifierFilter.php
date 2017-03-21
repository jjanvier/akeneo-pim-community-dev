<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

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

        $this->checkValue($field, $operator, $value);

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

            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        self::IDENTIFIER_KEY => $value
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        self::IDENTIFIER_KEY => $value
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($clause);
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
     * Checks the identifier is a string or an array depending on the operator
     *
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     *
     */
    protected function checkValue($field, $operator, $value)
    {
        if (Operators::IN_LIST === $operator || Operators::NOT_IN_LIST === $operator) {
            FieldFilterHelper::checkArray($field, $value, self::class);
        } elseif (in_array($operator, $this->supportedOperators)) {
            FieldFilterHelper::checkString($field, $value, self::class);
        }
    }
}
