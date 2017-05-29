<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\Product;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\AttributeTypes;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeRepository extends EntityRepository
{
    /**
     * @param EntityManager $em
     * @param string        $className
     */
    public function __construct(EntityManager $em, $className)
    {
        parent::__construct($em, $em->getClassMetadata($className));
    }

    /**
     * {@inheritdoc}
     *
     * used in variant group values validator. need permission
     */
    public function findUniqueAttributeCodes()
    {
        $codes = $this
            ->createQueryBuilder('a')
            ->select('a.code')
            ->andWhere('a.unique = ?1')
            ->setParameter(1, true)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            function ($data) {
                return $data['code'];
            },
            $codes
        );
    }

    /**
     * {@inheritdoc}
     *
     * used in subscriber. not sure about permission
     */
    public function findAllAxesQB()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere(
                $qb->expr()->in(
                    'a.type',
                    [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT]
                )
            )
            ->andWhere($qb->expr()->neq('a.scopable', 1))
            ->andWhere($qb->expr()->neq('a.localizable', 1));

        return $qb;
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
     *
     * used in datagrid. need permission
     */
    public function getAttributesAsArray($withLabel = false, $locale = null, array $ids = [])
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att')
            ->from($this->_entityName, 'att', 'att.code');
        if (!empty($ids)) {
            $qb->andWhere('att.id IN (:ids)')->setParameter('ids', $ids);
        }
        $results = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);

        if ($withLabel) {
            $labelExpr = 'COALESCE(NULLIF(trans.label, \'\'), CONCAT(CONCAT(\'[\', att.code), \']\'))';
            $groupLabelExpr = 'COALESCE(NULLIF(gtrans.label, \'\'), CONCAT(CONCAT(\'[\', g.code), \']\'))';

            $qb = $this->_em->createQueryBuilder()
                ->select('att.code', sprintf('%s as label', $labelExpr))
                ->from($this->_entityName, 'att')
                ->leftJoin('att.translations', 'trans', 'WITH', 'trans.locale = :locale')
                ->leftJoin('att.group', 'g')
                ->leftJoin('g.translations', 'gtrans', 'WITH', 'gtrans.locale = :locale')
                ->addSelect('g.sortOrder')
                ->addSelect(sprintf('%s as groupLabel', $groupLabelExpr))
                ->setParameter('locale', $locale);
            if (!empty($ids)) {
                $qb->andWhere('att.id IN (:ids)')->setParameter('ids', $ids);
            }
            $attributes = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);
            foreach ($attributes as $data) {
                $results[$data['code']]['label'] = $data['label'];
                $results[$data['code']]['group'] = $data['groupLabel'];
                $results[$data['code']]['groupOrder'] = $data['sortOrder'];
            }
        }

        return $results;
    }

    /**
     * Get ids of attributes usable in grid
     *
     * TODO: should be extracted in an enrich bundle repository
     *
     * @param array $codes
     * @param array $groupIds
     *
     * @return array
     */
    public function getAttributeIdsUseableInGrid($codes = null, $groupIds = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att.id')
            ->from($this->_entityName, 'att', 'att.id');

        if (is_array($codes) && !empty($codes)) {
            $qb->andWhere("att.code IN (:codes)");
            $qb->setParameter('codes', $codes);
        }

        if (is_array($groupIds) && !empty($groupIds)) {
            $qb->andWhere("att.group IN (:groupIds)");
            $qb->setParameter('groupIds', $groupIds);
        } elseif (is_array($groupIds)) {
            return [];
        }

        $qb->andWhere('att.useableAsGridFilter = :useableInGrid');
        $qb->setParameter('useableInGrid', 1);

        $result = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);

        return array_keys($result);
    }
}
