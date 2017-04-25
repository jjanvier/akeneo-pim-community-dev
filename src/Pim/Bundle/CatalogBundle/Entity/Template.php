<?php


namespace Pim\Bundle\CatalogBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\TemplateInterface;

class Template implements TemplateInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var ArrayCollection */
    private $attributeSets;

    /** @var FamilyInterface */
    private $family;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributeSets()
    {
        return $this->attributeSets;
    }

    /**
     * @param ArrayCollection $attributeSets
     */
    public function setAttributeSets($attributeSets)
    {
        $this->attributeSets = $attributeSets;
    }

    /**
     * @return FamilyInterface
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param FamilyInterface $family
     */
    public function setFamily($family)
    {
        $this->family = $family;
    }
}