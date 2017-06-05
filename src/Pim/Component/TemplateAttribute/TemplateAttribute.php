<?php

namespace Pim\Component\TemplateAttribute;

use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Each TemplateAttribute is compound by levels.
 */
class TemplateAttribute
{
    /** @var TemplateAttributeLevel[] */
    private $levels;
    /** @var string */
    private $name;

    public function __construct(array $levels, $name)
    {
        $this->name = $name;

        foreach ($levels as $level) {
            if (!$level instanceof TemplateAttributeLevel) {
                throw new \InvalidArgumentException('Level expected bro.');
            }
        }

        $this->levels = $levels;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return all the attributes, indexed by code, (regulars + variant) of a template level.
     *
     * @param int $level
     *
     * @return AttributeInterface[]
     */
    public function getAttributesByLevel($level)
    {
        if ($level > count($this->levels)) {
            throw new \InvalidArgumentException('Impossible to retrieve the attributes of this level');
        }

        $attrs = $this->levels[$level - 1]->getRegularAttributes();
        $variantAttr = $this->levels[$level - 1]->getVariantAttribute();

        if (null !== $variantAttr) {
            $attrs[$variantAttr->getCode()] = $variantAttr;
        }

        return $attrs;
    }


    /**
     * Return all the attributes, indexed by code, (regulars + variant) of a template.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes()
    {
        $attrs = [];

        for ($i=0; $i<count($this->levels); $i++) {
            $attrs = array_merge($attrs, $this->getAttributesByLevel($i+1));
        }

        return $attrs;
    }

    public function __toString()
    {
        $string = "\n\n" . sprintf('Template "%s" - %d levels',  $this->name, count($this->levels));
        for ($i=1; $i<=count($this->levels); $i++) {
            $string .= "\n" . sprintf('Level %d: %s', $i, (string) $this->levels[$i-1]);
        }

        return $string;
    }
}
