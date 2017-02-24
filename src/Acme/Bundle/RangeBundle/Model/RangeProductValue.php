<?php

namespace Acme\Bundle\RangeBundle\Model;

use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * A product value that can only contains range data.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RangeProductValue extends AbstractProductValue implements ProductValueInterface
{
    /** @var Range */
    protected $range;

    /**
     * @return Range
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @param Range $range
     */
    protected function setRange(Range $range)
    {
        $this->range = $range;
    }
}
