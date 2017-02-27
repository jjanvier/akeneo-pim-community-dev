<?php


namespace Pim\Bundle\CatalogBundle\ElasticSearch\Filter;


class Clauses
{
    // no scoring here (should be called 'filter_not'?)
    private $mustNotClauses = [];
    private $filterClauses = [];

    /**
     * @return array
     */
    public function getMustNotClauses()
    {
        return ['must_not' => $this->mustNotClauses];
    }

    /**
     * @return array
     */
    public function getFilterClauses()
    {
        return ['filter' => $this->filterClauses];
    }

    public function addMustNotClause(array $clause)
    {
        $this->mustNotClauses[] = $clause;
    }

    public function addFilterClause(array $clause)
    {
        $this->filterClauses[] = $clause;
    }
}
