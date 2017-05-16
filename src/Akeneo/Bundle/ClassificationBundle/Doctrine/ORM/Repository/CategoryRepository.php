<?php

namespace Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Category repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRepository extends NestedTreeRepository implements
    IdentifiableObjectRepositoryInterface,
    CategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCategoriesByIds(array $categoriesIds = [])
    {
        if (empty($categoriesIds)) {
            return new ArrayCollection();
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id IN (:categoriesIds)');

        $qb->setParameter('categoriesIds', $categoriesIds);

        $result = $qb->getQuery()->getResult();
        $result = new ArrayCollection($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByCodes(array $categoriesCodes = [])
    {
        if (empty($categoriesCodes)) {
            return new ArrayCollection();
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.code IN (:categoriesCodes)');

        $qb->setParameter('categoriesCodes', $categoriesCodes);

        $result = $qb->getQuery()->getResult();
        $result = new ArrayCollection($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * everywhere
     */
    public function getAllChildrenIds(CategoryInterface $parent, $includeNode = false)
    {
        $categoryQb = $this->getAllChildrenQueryBuilder($parent, $includeNode);
        $rootAlias = current($categoryQb->getRootAliases());
        $rootEntity = current($categoryQb->getRootEntities());
        $categoryQb->select($rootAlias.'.id');
        $categoryQb->resetDQLPart('from');
        $categoryQb->from($rootEntity, $rootAlias, $rootAlias.'.id');

        return array_keys($categoryQb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildrenCodes(CategoryInterface $parent, $includeNode = false)
    {
        $categoryQb = $this->getAllChildrenQueryBuilder($parent, $includeNode);
        $rootAlias = current($categoryQb->getRootAliases());
        $rootEntity = current($categoryQb->getRootEntities());
        $categoryQb->select($rootAlias.'.code');
        $categoryQb->resetDQLPart('from');
        $categoryQb->from($rootEntity, $rootAlias, $rootAlias.'.id');

        $categories = $categoryQb->getQuery()->execute(null, AbstractQuery::HYDRATE_SCALAR);
        $codes = [];
        foreach ($categories as $category) {
            $codes[] = $category['code'];
        }

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIdsByCodes(array $categoriesCodes)
    {
        if (empty($categoriesCodes)) {
            return [];
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node.id')
            ->from($config['useObjectClass'], 'node')
            ->where('node.code IN (:categoriesCodes)');

        $qb->setParameter('categoriesCodes', $categoriesCodes);

        $categories = $qb->getQuery()->execute(null, AbstractQuery::HYDRATE_SCALAR);
        $ids = [];
        foreach ($categories as $category) {
            $ids[] = (int) $category['id'];
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderedAndSortedByTreeCategories()
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder = $queryBuilder->orderBy('c.root')->addOrderBy('c.left');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Shortcut to get all children query builder
     *
     * @param CategoryInterface $category    the requested node
     * @param bool              $includeNode true to include actual node in query result
     *
     * @return QueryBuilder
     */
    protected function getAllChildrenQueryBuilder(CategoryInterface $category, $includeNode = false)
    {
        return $this->getChildrenQueryBuilder($category, false, null, 'ASC', $includeNode);
    }
}
