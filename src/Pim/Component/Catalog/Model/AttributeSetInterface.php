<?php


namespace Pim\Component\Catalog\Model;


use Doctrine\Common\Collections\ArrayCollection;

interface AttributeSetInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return ArrayCollection
     */
    public function getAttributes();

    /**
     * @param ArrayCollection $attributes
     */
    public function setAttributes($attributes);

    /**
     * @return AttributeInterface
     */
    public function getAxes();

    /**
     * @param AttributeInterface $axes
     */
    public function setAxes($axes);

    /**
     * @return TemplateInterface
     */
    public function getTemplate();

    /**
     * @param TemplateInterface $template
     */
    public function setTemplate($template);
}