<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Doctrine\Common\Collections\Collection;

interface ProductModelInterface extends
    FlexibleValuesInterface,
    CanHaveProductModelInterface,
    CategoryAwareInterface
{
    /**
     * Get the ID of the model
     *
     * @return int|string
     */
    public function getId();

    /**
     * Set id
     *
     * @param int|string $id
     *
     * @return ProductModelInterface
     */
    public function setId($id);

    /**
     * Get the identifier of the model
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string $identifier
     *
     * @return ProductModelInterface
     *
     */
    public function setIdentifier($identifier);

    /**
     * Set family
     *
     * @param FamilyInterface $family
     *
     * @return ProductInterface
     */
    public function setFamily(FamilyInterface $family = null);

    /**
     * Get family
     *
     * @return FamilyInterface
     */
    public function getFamily();

    /**
     * @return bool
     */
    public function isRootModel();

    /**
     * @param ProductModelInterface $child
     */
    public function addChildModel(ProductModelInterface $child);

    /**
     * @param ProductModelInterface $child
     */
    public function removeChildModel(ProductModelInterface $child);

    /**
     * @return bool
     */
    public function hasChildrenModels();

    /**
     * @return Collection of ProductModelInterface
     */
    public function getChildrenModels();
}
