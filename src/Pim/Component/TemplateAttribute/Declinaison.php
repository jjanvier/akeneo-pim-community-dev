<?php


namespace Pim\Component\TemplateAttribute;

/**
 * SHouldn't we call them ProductTemplate instead?
 */
class Declinaison
{
    /** @var TemplateAttribute */
    private $templateAttribute;

    /** @var Declinaison */
    private $parent;

    /** @var Declinaison */
    private $child;

    /** @var DeclinaisonValueInterface[] */
    private $values;

    public function __construct(TemplateAttribute $templateAttribute, array $values, Declinaison $parent = null)
    {
        $this->templateAttribute = $templateAttribute;
        $this->values = $values;
        $this->parent = $parent;
    }

    public function isRoot()
    {
        return null === $this->parent;
    }

    public function isLeaf()
    {
        return null === $this->child;
    }

    /**
     * @return TemplateAttribute
     */
    public function getTemplateAttribute()
    {
        return $this->templateAttribute;
    }

    /**
     * @return DeclinaisonValueInterface[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return Declinaison
     */
    public function getParent()
    {
        return $this->parent;
    }


}
