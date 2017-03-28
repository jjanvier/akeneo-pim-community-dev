<?php


namespace Pim\Component\Catalog\Query;


use Akeneo\Bundle\ElasticsearchBundle\Client;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;

class Results implements \Iterator
{
    protected $esQuery;
    protected $esClient;
    protected $products = [];
    protected $em;

    public function __construct(EntityManagerInterface $em, Client $esClient, array $esQuery)
    {
        $this->em = $em;
        $this->esClient = $esClient;
        $this->esQuery = $esQuery;

        $this->products = $this->getNextProducts($esQuery);
    }

    public function current()
    {
        return current($this->products);
    }

    public function next()
    {
        $currentProduct = $this->current();

        if (false === next($this->products)) {
            // try to load next page
            $searchAfterIdentifier = $currentProduct->getIdentifier();
            $this->products = $this->getNextProducts($this->esQuery, $searchAfterIdentifier);
            $this->rewind();
        }
    }

    public function key()
    {
        return key($this->products);
    }

    public function valid()
    {
        return !empty($this->products);
    }

    public function rewind()
    {
        reset($this->products);
    }

    private function getNextProducts(array $esQuery, $searchAfter = null)
    {
        $identifiers = $this->getNextIdentifiers($esQuery, $searchAfter);
        if (empty($identifiers)) {
            return [];
        }

        return $this->loadProductsFromIdentifiers($identifiers);
    }

    private function getNextIdentifiers(array $esQuery, $searchAfter = null)
    {
        $esQuery['size'] = 2;
        $esQuery['sort'] = ['_uid' => 'desc'];

        if (null !== $searchAfter) {
            $esQuery['search_after'] = ['pim_catalog_product#' . $searchAfter];
        }

        $response = $this->esClient->search('pim_catalog_product', $esQuery);

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        return $identifiers;
    }

    /**
     * @param array $identifiers
     *
     * @return ProductInterface[]
     */
    private function loadProductsFromIdentifiers(array $identifiers)
    {
        $qb = $this->getInternalQueryBuilder();
        $qb->where('p.identifier IN (:identifiers)');
        $qb->setParameter('identifiers', $identifiers);

        return $qb->getQuery()->execute();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getInternalQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p');
    }

}
