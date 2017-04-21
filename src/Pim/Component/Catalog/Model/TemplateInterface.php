<?php


namespace Pim\Component\Catalog\Model;


use Doctrine\Common\Collections\ArrayCollection;

interface TemplateInterface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     */
    public function setCode($code);

    /**
     * @return ArrayCollection
     */
    public function getAttributeSets();

    /**
     * @param ArrayCollection $attributeSets
     */
    public function setAttributeSets($attributeSets);
}