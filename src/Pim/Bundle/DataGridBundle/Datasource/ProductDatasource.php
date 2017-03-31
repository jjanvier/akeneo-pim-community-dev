<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product datasource, allows to prepare query builder from repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDatasource extends Datasource
{
    /** @var ProductQueryBuilderInterface */
    protected $pqb;
    /** @var NormalizerInterface */
    private $normalizer;

    /**
     * @param ObjectManager                       $om
     * @param HydratorInterface                   $hydrator
     * @param ProductQueryBuilderFactoryInterface $factory
     */
    public function __construct(
        ObjectManager $om,
        HydratorInterface $hydrator,
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $normalizer
    ) {
        $this->om = $om;
        $this->hydrator = $hydrator;
        $this->factory = $factory;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $options = [
            'locale_code'              => $this->getConfiguration('locale_code'),
            'scope_code'               => $this->getConfiguration('scope_code'),
            'attributes_configuration' => $this->getConfiguration('attributes_configuration'),
            'current_group_id'         => $this->getConfiguration('current_group_id', false),
            'association_type_id'      => $this->getConfiguration('association_type_id', false),
            'current_product'          => $this->getConfiguration('current_product', false)
        ];

        if (method_exists($this->qb, 'setParameters')) {
            QueryBuilderUtility::removeExtraParameters($this->qb);
        }

        $rows = [];
        $cursor = $this->getProductQueryBuilder()->getProductsSearchAfter(10, $this->getParameters()['identifier']);
        $context = ['locale' => $options['locale_code'], 'channel' => $options['scope_code']];
        foreach ($cursor as $product) {
            $poo = array_merge(
                $this->normalizer->normalize($product, 'datagrid', $context),
                ['id' => $product->getId(), 'dataLocale' => $this->getConfiguration('locale_code')]
            );

            $rows[] = new ResultRecord($poo);
        }

        return $rows;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    public function getProductQueryBuilder()
    {
        return $this->pqb;
    }

    /**
     * @param string $method the query builder creation method
     * @param array  $config the query builder creation config
     *
     * @return Datasource
     */
    protected function initializeQueryBuilder($method, array $config = [])
    {
        $factoryConfig['repository_parameters'] = $config;
        $factoryConfig['repository_method'] = $method;
        $factoryConfig['default_locale'] = $this->getConfiguration('locale_code');
        $factoryConfig['default_scope'] = $this->getConfiguration('scope_code');

        $this->pqb = $this->factory->create($factoryConfig);
        $this->qb = $this->pqb->getQueryBuilder();

        return $this;
    }
}
