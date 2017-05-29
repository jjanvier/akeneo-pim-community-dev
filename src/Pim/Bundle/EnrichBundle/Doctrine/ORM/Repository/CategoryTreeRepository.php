<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnexpectedResultException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\QueryBuilder\CategoryTreeQueryBuilder;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeRepository implements CategoryTreeRepositoryInterface
{
    protected $qb;

    /**
     * @param CategoryTreeQueryBuilder $qb
     */
    public function __construct($qb)
    {
        $this->qb = $qb;
    }

    /**
     * @param $id
     *
     * @return mixed|null
     */
    public function find($id)
    {
        try {
            return $this->qb->find($id)->getQuery()->getSingleResult();
        } catch (UnexpectedResultException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
    {
        $qb = $this->qb->getFilledTreeQB($root, $categories);
        $nodes = $qb->getQuery()->getResult();

        return $this->buildTreeNode($nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenByParentId($parentId)
    {
        return $this->qb->getChildrenQBByParentId($parentId)->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenTreeByParentId($parentId, $selectNodeId = false, array $grantedCategoryIds = [])
    {
        $qb = $this->qb->getChildrenTreeQBByParentId($parentId, $selectNodeId);
        if (null === $qb) {
            return null;
        }

        return $this->buildTreeNode($qb->getQuery()->getResult());
    }

    /**
     * {@inheritdoc}
     */
    public function getTrees()
    {
        return $this->qb->getTrees()->getQuery()->getResult();
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
     */
    protected function buildTreeNode(array $nodes)
    {
        $vectorMap = [];
        $tree = [];
        $childrenIndex = $this->qb->getRepoUtils()->getChildrenIndex();

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
