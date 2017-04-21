<?php


namespace Pim\Bundle\CatalogBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeSetInterface;
use Pim\Component\Catalog\Model\TemplateInterface;

class AttributeSet implements AttributeSetInterface
{
    /** @var int */
    private $id;

    /** @var ArrayCollection */
    private $attributes;

    /** @var ArrayCollection */
    private $axes;

    /** @var TemplateInterface */
    private $template;

    public function __construct()
    {
        $this->axes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return ArrayCollection
     */
    public function getAxes()
    {
        return $this->axes;
    }

    /**
     * @param AttributeInterface $axes
     */
    public function setAxes($axes)
    {
        $this->axes = $axes;
    }

    /**
     * @return TemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param TemplateInterface $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}