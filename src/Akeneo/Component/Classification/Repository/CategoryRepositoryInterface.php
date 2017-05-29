<?php

namespace Akeneo\Component\Classification\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Gedmo\Tree\RepositoryInterface as TreeRepositoryInterface;

/**
 * Category repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryRepositoryInterface extends
    TreeRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
{
    /**
     * Get a collection of categories based on the array of id provided
     *
     * @param array $categoryIds
     *
     * @return Collection of categories
     */
    public function getCategoriesByIds(array $categoryIds = []);

    /**
     * Get a collection of categories based on the array of code provided
     *
     * @param array $categoryCodes
     *
     * @return Collection of categories
     */
    public function getCategoriesByCodes(array $categoryCodes = []);

    /**
     * Shortcut to get all children ids
     *
     * @param CategoryInterface $parent      the parent
     * @param bool              $includeNode true to include actual node in query result
     *
     * @return integer[]
     */
    public function getAllChildrenIds(CategoryInterface $parent, $includeNode = false);

    /**
     * Shortcut to get all children codes
     *
     * @param CategoryInterface $parent      the parent
     * @param bool              $includeNode true to include actual node in query result
     *
     * @return string[]
     */
    public function getAllChildrenCodes(CategoryInterface $parent, $includeNode = false);

    /**
     * Return the categories IDs from their codes. The categories are not hydrated.
     *
     * @param array $codes
     *
     * @return array
     */
    public function getCategoryIdsByCodes(array $codes);

    /**
     * Return the categories sorted by tree and ordered
     *
     * @return array
     */
    public function getOrderedAndSortedByTreeCategories();
}
