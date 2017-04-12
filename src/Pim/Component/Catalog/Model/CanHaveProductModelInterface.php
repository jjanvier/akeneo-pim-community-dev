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
}
