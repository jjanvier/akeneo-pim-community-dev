<?php

declare(strict_types=1);

namespace Acme\Bundle\AppBundle\Entity;

use Pim\Component\Catalog\Model\Product as PimProduct;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product extends PimProduct
{
    /** @var string */
    private $test;

    /**
     * @return string
     */
    public function getTest(): string
    {
        return $this->test;
    }

    /**
     * @param string $test
     */
    public function setTest(string $test): void
    {
        $this->test = $test;
    }
}
