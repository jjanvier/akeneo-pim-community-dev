<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\QueryBuilder;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Pim\Component\Catalog\Model\CategoryInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeQueryBuilder extends NestedTreeRepository
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * @param CategoryInterface $root
     * @param Collection        $categories
     *
     * @return QueryBuilder|null
     */
    public function getFilledTreeQB(CategoryInterface $root, Collection $categories)
    {
        $parentsIds = [];
        foreach ($categories as $category) {
            $categoryParentsIds = [];
            $path = $this->getPath($category);

            if ($path[0]->getId() === $root->getId()) {
                foreach ($path as $pathItem) {
                    $categoryParentsIds[] = $pathItem->getId();
                }
            }
            $parentsIds = array_merge($parentsIds, $categoryParentsIds);
        }
        $parentsIds = array_unique($parentsIds);
        if (empty($parentsIds)) {
            return null;
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id IN (:parentsIds) OR node.parent IN (:parentsIds)')
            ->setParameter('parentsIds', $parentsIds)
            ->orderBy('node.left');

        return $qb;
    }

    /**
     * @param int $parentId
     *
     * @return QueryBuilder
     */
    public function getChildrenQBByParentId($parentId)
    {
        $parent = $this->findCategory($parentId);

        return $this->getChildrenQueryBuilder($parent, true);
    }

    /**
     * @param $parentId
     * @param $selectNodeId
     *
     * @return QueryBuilder|null
     */
    public function getChildrenTreeQBByParentId($parentId, $selectNodeId)
    {
        $selectNode = $this->findCategory($selectNodeId);
        if (null === $selectNode) {
            return null;
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $selectPath = $this->getPath($selectNode);
        $parent = $this->findCategory($parentId);
        $qb = $this->getNodesHierarchyQueryBuilder($parent);

        // Remove the node itself from his ancestor
        array_pop($selectPath);

        $ancestorsIds = [];

        foreach ($selectPath as $ancestor) {
            $ancestorsIds[] = $ancestor->getId();
        }

        $qb->andWhere(
            $qb->expr()->in('node.' . $config['parent'], $ancestorsIds)
        );

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getTrees()
    {
        return $this->getChildrenQueryBuilder(null, true, 'created', 'DESC');
    }

    /**
     * @param int $id
     *
     * @return QueryBuilder
     */
    public function find($id)
    {
        $config = $this->listener->getConfiguration($this->_em, $this->getClassMetadata()->name);

        return $this->_em->createQueryBuilder()
            ->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id = :id')
            ->setParameter('id', $id);
    }

    /**
     * @param int $id
     *
     * @return mixed|null
     */
    private function findCategory($id)
    {
        $qb = $this->find($id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (UnexpectedResultException $e) {
            return null;
        }
    }
}
