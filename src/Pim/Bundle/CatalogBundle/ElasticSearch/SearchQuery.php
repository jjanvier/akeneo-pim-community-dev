<?php

namespace Pim\Bundle\CatalogBundle\ElasticSearch;

/**
 * This stateful class holds the multiple parts of an Elastic Search search query.
 *
 * In two different arrays, it keeps track of the conditions where:
 * - a property should be equal to a value (ES filter clause)
 * - a property should *not* be equal to a value (ES must_not clause)
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal This class is used by the ProductQueryBuilder to create an ES search query.
 */
class SearchQuery
{
    /** @var array */
    private $mustNotClauses = [];

    /** @var array */
    private $filterClauses = [];

    /**
     * Adds a filter clause to the query
     *
     * @param array $clause
     */
    public function addMustNot(array $clause)
    {
        $this->mustNotClauses[] = $clause;
    }

    /**
     * Adds a must_not clause to the query
     *
     * @param array $clause
     */
    public function addFilter(array $clause)
    {
        $this->filterClauses[] = $clause;
    }

    /**
     * Returns an Elastic search Query
     *
     * @param array $source
     *
     * @return array
     */
    public function getQuery(array $source = [])
    {
        if (empty($source)) {
            $source = ['identifier'];
        }

        $searchQuery = [
            '_source' => $source,
            'query'   => [],
        ];

        if (!empty($this->filterClauses)) {
            $searchQuery['query']['bool']['filter'] = $this->filterClauses;
        }

        if (!empty($this->mustNotClauses)) {
            $searchQuery['query']['bool']['must_not'] = $this->mustNotClauses;
        }

        return $searchQuery;
    }
}
