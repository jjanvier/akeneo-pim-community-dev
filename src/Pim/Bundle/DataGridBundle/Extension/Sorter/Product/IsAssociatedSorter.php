<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * Is associated sorter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsAssociatedSorter implements SorterInterface
{
    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param AssociationTypeRepositoryInterface $associationTypeRepository
     * @param ProductRepositoryInterface         $productRepository
     */
    public function __construct(
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->associationTypeRepository = $associationTypeRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $parameters = $datasource->getParameters();

        $associationType = isset($parameters['associationType']) ?
            $this->associationTypeRepository->find($parameters['associationType']) :
            null;

        if (null === $associationType) {
            throw new \LogicException(
                sprintf(
                    '"is_associated" sorter expects a valid association type ID, %s provided',
                    isset($parameters['associationType']) ? '"'.$parameters['associationType'].'"' : 'none'
                ),
                0,
                static::class
            );
        }

        $currentProduct = isset($parameters['product']) ?
            $this->productRepository->find($parameters['product']) :
            null;

        if (null === $currentProduct) {
            throw new \LogicException(
                sprintf(
                    '"is_associated" sorter expects a valid product ID, %s provided',
                    isset($parameters['product']) ? '"'.$parameters['product'].'"' : 'none'
                ),
                0,
                static::class
            );
        }

        $datasource->getProductQueryBuilder()->addSorter(
            sprintf(
                'is_associated.%s.%s',
                $associationType->getCode(),
                $currentProduct->getIdentifier()
            ),
            $direction
        );
    }
}
