<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
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
    protected $indexType;

    /**
     * @param NormalizerInterface $normalizer
     * @param Client              $indexer
     * @param string              $indexType
     */
    public function __construct(NormalizerInterface $normalizer, Client $indexer, $indexType)
    {
        $this->normalizer = $normalizer;
        $this->indexer = $indexer;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function index($product, array $options = [])
    {
        $this->validateProduct($product);
        $normalizedProduct = $this->normalizer->normalize($product, 'indexing');
        $this->validateProductNormalization($normalizedProduct);
        $this->indexer->index($this->indexType, $normalizedProduct['id'], $normalizedProduct);

        $this->indexAssociatedProducts([$product]);
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll(array $products, array $options = [])
    {
        $normalizedProducts = [];
        foreach ($products as $product) {
            $this->validateProduct($product);
            $normalizedProduct = $this->normalizer->normalize($product, 'indexing');
            $this->validateProductNormalization($normalizedProduct);
            $normalizedProducts[] = $normalizedProduct;
        }

        $this->indexer->bulkIndexes($this->indexType, $normalizedProducts, 'id');

        if (!isset($options['associated_products_already_indexed']) ||
            false === $options['associated_products_already_indexed']
        ) {
            $this->indexAssociatedProducts($products);
        }
    }

    /**
     * Index associated products.
     *
     * In ORM, the association is carried by the owner product. As a result,
     * when new associations are created, only owner products are saved in MySQL
     * then indexed in Elasticsearch.
     *
     * Problem is, the "is_associated" notion is to be carried by the associated
     * products, so they have to be re-indexed. The "associated_products_already_indexed"
     * option ensure we don't fall in an infinite indexation loop, preventing the
     * associated products of the associated products (and so on) to be indexed too.
     *
     * See {@see Pim\Component\Catalog\Normalizer\Indexing\Product\PropertiesNormalizer}
     * to understand how the Elasticsearch "is_associated" field is created.
     *
     * @param ProductInterface[] $products
     */
    private function indexAssociatedProducts(array $products)
    {
        $associatedProducts = [];
        foreach ($products as $product) {
            foreach ($product->getAssociations() as $association) {
                foreach ($association->getProducts() as $associatedProduct) {
                    if (!in_array($associatedProduct, $associatedProducts) &&
                        !in_array($associatedProduct, $products)
                    ) {
                        $associatedProducts[] = $associatedProduct;
                    }
                }
            }
        }

        if (!empty($associatedProducts)) {
            $this->indexAll($associatedProducts, ['associated_products_already_indexed' => true]);
        }
    }

    /**
     * @param mixed $product
     */
    private function validateProduct($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Only products "%s" can be indexed in the search engine, "%s" provided.',
                ProductInterface::class,
                ClassUtils::getClass($product)
            ));
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
