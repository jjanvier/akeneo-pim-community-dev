<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeRepository extends NestedTreeRepository
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
     * {@inheritdoc}
     *
     * used in category manager
     * and product controller to list categories. need permissions
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
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

        return $this->getTreeFromParents($parentsIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenByParentId($parentId)
    {
        $parent = $this->find($parentId);

        return $this->getChildren($parent, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenGrantedByParentId(CategoryInterface $parent, array $grantedCategoryIds = [])
    {
        return $this->getChildrenQueryBuilder($parent, true)
            ->andWhere('node.id IN (:ids)')
            ->setParameter('ids', $grantedCategoryIds)
            ->getQuery()
            ->getResult();
    }
    /**
     * {@inheritdoc}
     *
     * tree. need permissions
     */
    public function getChildrenTreeByParentId($parentId, $selectNodeId = false, array $grantedCategoryIds = [])
    {
        $children = [];

        if ($selectNodeId === false) {
            $parent = $this->find($parentId);
            $children = $this->childrenHierarchy($parent);
        } else {
            $selectNode = $this->find($selectNodeId);
            if ($selectNode != null) {
                $meta = $this->getClassMetadata();
                $config = $this->listener->getConfiguration($this->_em, $meta->name);

                $selectPath = $this->getPath($selectNode);
                $parent = $this->find($parentId);
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

                if (!empty($grantedCategoryIds)) {
                    $qb->andWhere('node.id IN (:ids)')
                        ->setParameter('ids', $grantedCategoryIds);
                }

                $nodes = $qb->getQuery()->getResult();
                $children = $this->buildTreeNode($nodes);
            }
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrees()
    {
        return $this->getChildren(null, true, 'created', 'DESC');
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantedTrees(array $grantedCategoryIds = [])
    {
        $qb = $this->getChildrenQueryBuilder(null, true, 'created', 'DESC');
        $result = $qb
            ->andWhere('node.id IN (:ids)')
            ->setParameter('ids', $grantedCategoryIds)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isAncestor(CategoryInterface $parentNode, CategoryInterface $childNode)
    {
        $sameRoot = $parentNode->getRoot() === $childNode->getRoot();

        $isAncestor = $childNode->getLeft() > $parentNode->getLeft()
                      && $childNode->getRight() < $parentNode->getRight();

        return $sameRoot && $isAncestor;
    }

    /**
     * {@inheritdoc}
     *
     * should be a protected
     */
    protected function getTreeFromParents(array $parentsIds)
    {
        if (count($parentsIds) === 0) {
            return [];
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id IN (:parentsIds) OR node.parent IN (:parentsIds)')
            ->orderBy('node.left');

        $qb->setParameter('parentsIds', $parentsIds);

        $nodes = $qb->getQuery()->getResult();

        return $this->buildTreeNode($nodes);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildTreeNode(array $nodes)
    {
        $vectorMap = [];
        $tree = [];
        $childrenIndex = $this->repoUtils->getChildrenIndex();

        foreach ($nodes as $node) {
            if (!isset($vectorMap[$node->getId()])) {
                // Node does not exist, and none of his children has
                // already been in the loop, so we create it.
                $vectorMap[$node->getId()] = [
                    'item'         => $node,
                    $childrenIndex => []
                ];
            } else {
                // Node already existing in the map because a child has been
                // added to his children array. We still need to add the node
                // itself, as only its children property has been created.
                $vectorMap[$node->getId()]['item'] = $node;
            }

            if ($node->getParent() != null) {
                if (!isset($vectorMap[$node->getParent()->getId()])) {
                    // The parent does not exist in the map, create its
                    // children property
                    $vectorMap[$node->getParent()->getId()] = [
                        $childrenIndex => []
                    ];
                }

                $vectorMap[$node->getParent()->getId()][$childrenIndex][] =& $vectorMap[$node->getId()];
            } else {
                $tree[$node->getId()] =& $vectorMap[$node->getId()];
            }
        }

        if (empty($tree)) {
            // No node found with getParent() == null, meaning the absolute tree
            // root was not part of the set. We try to find the lowest level nodes
            // or a node without item part, meaning that it's a referenced parent but without
            // the node present itself in the set
            $nodeIt = 0;
            $foundItemLess = false;
            $nodeIds = array_keys($vectorMap);
            $nodesByLevel = [];

            while ($nodeIt < count($nodeIds) && !$foundItemLess) {
                $nodeId = $nodeIds[$nodeIt];
                $nodeEntry = $vectorMap[$nodeId];

                if (isset($nodeEntry['item'])) {
                    //$nodesByLevel[$nodeEntry['item']->getLevel()][] = $nodeIds[$i];
                } else {
                    $tree =& $vectorMap[$nodeId][$childrenIndex];
                }
                $nodeIt++;
            }
            // $tree still empty there, means we need to pick the lowest level nodes as tree roots
            if (empty($tree)) {
                $lowestLevel = min(array_keys($nodesByLevel));
                foreach ($nodesByLevel[$lowestLevel] as $nodeId) {
                    $tree[$nodeId] =& $vectorMap[$nodeId];
                }
            }
        }

        return $tree;
    }
}
