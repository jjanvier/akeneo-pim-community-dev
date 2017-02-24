<?php

namespace Acme\Bundle\RangeBundle\Factory;

use Acme\Bundle\RangeBundle\Model\Range;

/**
 * Creates a range object.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RangeFactory
{
    /** @var string */
    protected $productValueClass;

    /**
     * @param string $productValueClass
     */
    public function __construct($productValueClass)
    {
        $this->productValueClass = $productValueClass;
    }

    /**
     * @param float $fromData
     * @param float $toData
     *
     * @return Range
     */
    public function createRange($fromData, $toData)
    {
        return new $this->productValueClass($fromData, $toData);
    }
}
