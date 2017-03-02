<?php

namespace Pim\Bundle\CatalogBundle\ElasticSearch\Filter;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Family filter
 */
class FamilyFilter extends AbstractFilter implements FieldFilterInterface
{
    /**
     * @param string[] $supportedFields
     * @param string[] $supportedOperators
     */
    public function __construct(array $supportedFields = [], array $supportedOperators = [])
    {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        'family' => $value
                    ]
                ];
                $this->clauses->addFilterClause($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        'family' => $value
                    ]
                ];
                $this->clauses->addMustNotClause($clause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => 'family'
                    ]
                ];
                $this->clauses->addMustNotClause($clause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => 'family'
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
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeType(AttributeInterface $attribute)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSuffixPath()
    {
        return '';
    }
}
