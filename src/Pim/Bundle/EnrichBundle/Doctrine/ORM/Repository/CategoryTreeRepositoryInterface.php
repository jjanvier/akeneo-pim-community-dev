<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryTreeRepositoryInterface
{
    public function getFilledTree(CategoryInterface $root, Collection $categories);

    public function getChildrenByParentId($parentId);

    public function getChildrenTreeByParentId($parentId, $selectNodeId = false, array $grantedCategoryIds = []);

    public function getTrees();

    public function isAncestor(CategoryInterface $parentNode, CategoryInterface $childNode);
}
