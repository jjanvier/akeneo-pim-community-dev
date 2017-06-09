<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
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
class ProductIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface
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
        $this->validateProductNormalization($normalizedProduct);
        $this->indexer->index($indexType, $normalizedProduct['id'], $normalizedProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $normalizedProducts = [];
        $indexType = current($products) instanceof ProductInterface ? $this->productIndexType : $this->productModelIndexType;

        foreach ($products as $product) {
            $this->validateProduct($product);
            $normalizedProduct = $this->normalizer->normalize($product, 'indexing');
            $this->validateProductNormalization($normalizedProduct);
            $normalizedProducts[] = $normalizedProduct;
        }

        $this->indexer->bulkIndexes($indexType, $normalizedProducts, 'id', Refresh::waitFor());
    }

    /**
     * {@inheritdoc}
     */
    public function remove($productId, array $options = [])
    {
        // TODO: WARNING, HERE WE HARDCORE PRODUCT MODEL INDEX TYPE, TOFIX
//        $this->indexer->delete($this->indexType, $productId);
        $this->indexer->delete($this->productModelIndexType, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $productIds, array $options = [])
    {
        // TODO: WARNING, HERE WE HARDCORE PRODUCT MODEL INDEX TYPE, TOFIX
//        $this->indexer->bulkDelete($this->indexType, $productIds);
        $this->indexer->bulkDelete($this->productModelIndexType, $productIds);
    }

    /**
     * @param mixed $product
     */
    private function validateProduct($product)
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

    /**
     * @param array $product
     */
    private function validateProductNormalization(array $product)
    {
        if (!isset($product['id'])) {
            throw new \InvalidArgumentException('Only products with an ID can be indexed in the search engine.');
        }
    }
}
