<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelRepository extends EntityRepository implements
    CursorableRepositoryInterface
{
    /**
     * @param array $identifiers
     *
     * @return array
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.identifier IN (:identifiers)')
            ->setParameter('identifiers', $identifiers);

        return $qb->getQuery()->execute();
    }
}
