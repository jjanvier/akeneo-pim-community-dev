<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch\Sorter;

use Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Pim\Component\Catalog\Exception\InvalidDirectionException;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;

/**
 * IsAssociated sorter for an Elasticsearch query, used for association product grid.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsAssociatedSorter extends BaseFieldSorter implements FieldSorterInterface
{

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $channel = null)
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the sorter.');
        }

        switch ($direction) {
            case Directions::ASCENDING:
                $sortClause = [
                    $field => [
                        'order'   => 'ASC',
                        'missing' => '_first',
                        'unmapped_type'=> 'boolean',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortClause);

                break;
            case Directions::DESCENDING:
                $sortClause = [
                    $field => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                        'unmapped_type'=> 'boolean',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortClause);

                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return (strpos($field, 'is_associated') !== false);
    }
}
