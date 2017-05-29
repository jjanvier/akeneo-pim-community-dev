<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;

/**
 * Repository interface for attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRepositoryInterface extends
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
{
    /**
     * Find all attributes that belongs to the default group
     *
     * @deprecated avoid the hydration of attributes as objects (perf), use from controller, will be removed in 1.8
     *
     * @return AttributeInterface[]
     */
    public function findAllInDefaultGroup();

    /**
     * Find media attribute codes
     *
     * @return string[]
     */
    public function findMediaAttributeCodes();

    /**
     * Get the identifier attribute
     * Only one identifier attribute can exists
     *
     * @return AttributeInterface
     */
    public function getIdentifier();

    /**
     * Get the identifier code
     *
     * @return string
     */
    public function getIdentifierCode();

    /**
     * Get attribute type by code attributes
     *
     * @param array $codes
     *
     * @return array
     */
    public function getAttributeTypeByCodes(array $codes);

    /**
     * Get attribute codes by attribute type
     *
     * @param string $type
     *
     * @return string[]
     */
    public function getAttributeCodesByType($type);

    /**
     * Get attribute codes by attribute group
     *
     * @param AttributeGroupInterface $group
     *
     * @return string[]
     */
    public function getAttributeCodesByGroup(AttributeGroupInterface $group);

    /**
     * Get attributes by family
     *
     * @param FamilyInterface $family
     *
     * @return AttributeInterface[]
     */
    public function findAttributesByFamily(FamilyInterface $family);

    /**
     * Return the number of existing attributes
     *
     * @return int
     */
    public function countAll();
}
