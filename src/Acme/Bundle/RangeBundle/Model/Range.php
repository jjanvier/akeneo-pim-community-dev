<?php

namespace Acme\Bundle\RangeBundle\Model;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Range
{
    /** @var float */
    protected $fromData;

    /** @var float */
    protected $toData;

    /**
     * @param float $fromData
     * @param float $toData
     */
    public function __construct($fromData, $toData)
    {
        $this->fromData = $fromData;
        $this->toData = $toData;
    }

    /**
     * @return float
     */
    public function getFromData()
    {
        return $this->fromData;
    }

    /**
     * @return float
     */
    public function getToData()
    {
        return $this->toData;
    }
}
