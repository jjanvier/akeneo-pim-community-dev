<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Repository for attribute entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepository extends EntityRepository implements
    IdentifiableObjectRepositoryInterface,
    AttributeRepositoryInterface
{
    /** @var string $identifierCode */
    protected $identifierCode;

    /**
     * {@inheritdoc}
     */
    public function findAllInDefaultGroup()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->innerJoin('a.group', 'g')
            ->where('g.code != :default_code')
            ->orderBy('a.code')
            ->setParameter(':default_code', AttributeGroup::DEFAULT_GROUP_CODE);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findMediaAttributeCodes()
    {
        $codes = $this
            ->createQueryBuilder('a')
            ->select('a.code')
            ->andWhere('a.type IN (:file_type, :image_type)')
            ->setParameters(
                [
                    ':file_type'  => AttributeTypes::FILE,
                    ':image_type' => AttributeTypes::IMAGE,
                ]
            )
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
     */
    public function findAvailableAxes($locale)
    {
        $query = $this->findAllAxesQB()
            ->select('a.id')
            ->addSelect('COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', a.code, \']\')) as label')
            ->leftJoin('a.translations', 't')
            ->andWhere('t.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('t.label')
            ->getQuery();

        $axis = [];
        foreach ($query->getArrayResult() as $code) {
            $axis[$code['id']] = $code['label'];
        }

        return $axis;
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
     * no need permission
     */
    public function getIdentifier()
    {
        return $this->findOneBy(['type' => AttributeTypes::IDENTIFIER]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode()
    {
        if (null === $this->identifierCode) {
            $code = $this->createQueryBuilder('a')
                ->select('a.code')
                ->andWhere('a.type = :type')
                ->setParameter('type', AttributeTypes::IDENTIFIER)
                ->setMaxResults(1)
                ->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);

            $this->identifierCode = $code;
        }

        return $this->identifierCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypeByCodes(array $codes)
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.code, a.type')
            ->where('a.code IN (:codes)')
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getArrayResult();

        $attributes = [];
        if (!empty($results)) {
            foreach ($results as $attribute) {
                $attributes[$attribute['code']] = $attribute['type'];
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * used for asset.
     */
    public function getAttributeCodesByType($type)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a.code')
            ->where($qb->expr()->eq('a.type', ':type'))
            ->setParameter(':type', $type);

        $result = $qb->getQuery()->getScalarResult();

        if (null === $result) {
            return [];
        } else {
            return array_map('current', $qb->getQuery()->getScalarResult());
        }
    }

    /**
     * {@inheritdoc}
     *
     * used to normalize attribute in standard format. no need permission
     */
    public function getAttributeCodesByGroup(AttributeGroupInterface $group)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a.code')
            ->where($qb->expr()->eq('a.group', ':group'))
            ->setParameter(':group', $group);

        $result = $qb->getQuery()->getScalarResult();

        if (null === $result) {
            return [];
        }

        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    /**
     * {@inheritdoc}
     *
     * used to normalize family in standard format. no need permission
     */
    public function findAttributesByFamily(FamilyInterface $family)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a, g')
            ->join('a.group', 'g')
            ->innerJoin('a.families', 'f', 'WITH', 'f.id = :family')
            ->setParameter(':family', $family->getId());

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * used for analytics bundle. no need permission
     */
    public function countAll()
    {
        $count = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }
}
