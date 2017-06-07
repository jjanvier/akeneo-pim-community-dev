<?php

namespace Pim\Component\Catalog\Model;

interface CanHaveProductModelInterface
{
    /**
     * @param ProductModelInterface $model
     *
     * @return CanHaveProductModelInterface
     */
    public function setModel(ProductModelInterface $model);

    /**
     * @return ProductModelInterface
     */
    public function getModel();

    /**
     * @return bool
     */
    public function hasModel();

    /**
     * Get all the parent values.
     * This method is recursive, meaning, you can have the values of the parent,
     * grandparent and great-grandparent.
     *
     * @return ProductValueCollectionInterface
     */
    public function getParentValues();

    /**
     * Get all the values, including yours and the one your parents.
     * This method is recursive, meaning, you can have the values of the parent,
     * grandparent and great-grandparent.
     *
     * @return ProductValueCollectionInterface
     */
    public function getAllValues();
}
