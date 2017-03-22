<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * DateTime filter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var IdentifiableObjectRepositoryInterface
     */
    protected $jobInstanceRepository;

    /**
     * @var JobRepositoryInterface
     */
    protected $jobRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param JobRepositoryInterface $jobRepository
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobRepositoryInterface $jobRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobRepository = $jobRepository;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkValue($operator, $field, $value);

        switch ($operator) {
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $field => $this->getFormattedDate($value)
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::LOWER_THAN:
                $clause = [
                    'range' => [
                        $field => ['lt' => $this->getFormattedDate($value)]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::GREATER_THAN:
                $clause = [
                    'range' => [
                        $field => ['gt' => $this->getFormattedDate($value)]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::BETWEEN:
                $values = array_values($value);
                $clause = [
                    'range' => [
                        $field => [
                            'gte' => $this->getFormattedDate($values[0]),
                            'lte' => $this->getFormattedDate($values[1])
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::NOT_BETWEEN:
                $values = array_values($value);
                $betweenClause = [
                    'range' => [
                        $field => [
                            'gte' => $this->getFormattedDate($values[0]),
                            'lte' => $this->getFormattedDate($values[1])
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($betweenClause);
                $this->searchQueryBuilder->addFilter($this->getExistsClause($field));

                break;
            case Operators::IS_EMPTY:
                $this->searchQueryBuilder->addMustNot($this->getExistsClause($field));

                break;
            case Operators::IS_NOT_EMPTY:
                $this->searchQueryBuilder->addFilter($this->getExistsClause($field));

                break;
            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        $field => $this->getFormattedDate($value)
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($this->getExistsClause($field));

                break;
            case Operators::SINCE_LAST_N_DAYS:
                return $this->addFieldFilter(
                    $field,
                    Operators::GREATER_THAN,
                    new \DateTime(sprintf('%s days ago', $value), new \DateTimeZone('UTC')),
                    $locale,
                    $scope,
                    $options
                );
            case Operators::SINCE_LAST_JOB:
                $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($value);
                $lastCompletedJobExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);
                if (null === $lastCompletedJobExecution) {
                    return $this;
                }

                return $this->addFieldFilter(
                    $field,
                    Operators::GREATER_THAN,
                    $lastCompletedJobExecution->getStartTime()->setTimezone(new \DateTimeZone('UTC')),
                    $locale,
                    $scope,
                    $options
                );
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * @param string $field
     *
     * @return array
     */
    protected function getExistsClause($field)
    {
        return [
            'exists' => ['field' => $field]
        ];
    }

    /**
     * @param $operator
     * @param $field
     * @param $value
     */
    protected function checkValue($operator, $field, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
            case Operators::LOWER_THAN:
            case Operators::GREATER_THAN:
            case Operators::NOT_EQUAL:
                FieldFilterHelper::checkDateTime($field, $value, static::DATETIME_FORMAT, static::class);

                break;
            case Operators::BETWEEN:
            case Operators::NOT_BETWEEN:
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                }

                if (2 !== count($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('should contain 2 strings with the format "%s"', static::DATETIME_FORMAT),
                        static::class,
                        $value
                    );
                }

                foreach ($value as $singleValue) {
                    FieldFilterHelper::checkDateTime($field, $singleValue, static::DATETIME_FORMAT, static::class);
                }

                break;
            case Operators::SINCE_LAST_JOB:
                if (!is_string($value)) {
                    throw InvalidPropertyTypeException::stringExpected($field, static::class, $value);
                }

                break;
            case Operators::SINCE_LAST_N_DAYS:
                if (!is_numeric($value)) {
                    throw InvalidPropertyTypeException::numericExpected($field, static::class, $value);
                }

                break;
            case Operators::IS_EMPTY:
            case Operators::IS_NOT_EMPTY:
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }

    /**
     * @param string|\DateTime $value
     *
     * @return int
     */
    protected function getFormattedDate($value) {
        if (!$value instanceof \DateTime) {
            $value = \DateTime::createFromFormat(static::DATETIME_FORMAT, $value);
        }

        $value->setTimezone(new \DateTimeZone('UTC'));

        return $value->format('c');
    }
}
