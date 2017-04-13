<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product indexer, define custom logic and options for product indexing in the search engine.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIndexer implements IndexerInterface, BulkIndexerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var Client */
    protected $indexer;

    /** @var string */
    protected $productIndexType;

    /** @var */
    private $productModelIndexType;

    /**
     * @param NormalizerInterface $normalizer
     * @param Client              $indexer
     * @param string              $productIndexType
     * @param                     $productModelIndexType
     */
    public function __construct(NormalizerInterface $normalizer, Client $indexer, $productIndexType, $productModelIndexType)
    {
        $this->normalizer = $normalizer;
        $this->indexer = $indexer;
        $this->productIndexType = $productIndexType;
        $this->productModelIndexType = $productModelIndexType;
    }

    /**
     * {@inheritdoc}
     */
    public function index($product, array $options = [])
    {
        $this->validateProduct($product);
        $indexType = $product instanceof ProductInterface ? $this->productIndexType : $this->productModelIndexType;

        $normalizedProduct = $this->normalizer->normalize($product, 'indexing');
        $this->indexer->index($indexType, $product->getIdentifier(), $normalizedProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll(array $products, array $options = [])
    {
        $normalizedProducts = [];
        $indexType = current($products) instanceof ProductInterface ? $this->productIndexType : $this->productModelIndexType;

        foreach ($products as $product) {
            $this->validateProduct($product);
            $normalizedProducts[$product->getIdentifier()] = $this->normalizer->normalize($product, 'indexing');
        }

        $this->indexer->bulkIndexes($indexType, $normalizedProducts, 'identifier');
    }

    /**
     * @param mixed $product
     */
    protected function validateProduct($product)
    {
        if (!$product instanceof ProductInterface && !$product instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Only products "Pim\Component\Catalog\Model\ProductInterface" can be indexed in the search engine, "%s" provided.',
                    ClassUtils::getClass($product)
                )
            );
        }
    }
}
