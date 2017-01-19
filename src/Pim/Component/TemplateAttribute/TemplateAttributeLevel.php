<?php

namespace Pim\Component\TemplateAttribute;

use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Each TemplateAttributeLevel is compound by several regular bricks
 * and one variant brick.
 */
class TemplateAttributeLevel
{
    /** @var Brick[] */
    private $regularBricks = [];
    /** @var Brick */
    private $variantBrick;

    /**
     * @param Brick[] $regularBricks
     * @param Brick|null $variantBrick
     */
    public function __construct(array $regularBricks, Brick $variantBrick = null)
    {
        foreach ($regularBricks as $brick) {
            if (!$brick instanceof Brick) {
                throw new \InvalidArgumentException('Brick expected bro.');
            }

            if ($brick->isVariant()) {
                throw new \InvalidArgumentException('U think I woult not see you are a variant brick huh?');
            }
        }

        if (null !== $variantBrick && !$variantBrick->isVariant()) {
            throw new \InvalidArgumentException('U think I woult not see you are a regular brick huh?');
        }

        $this->regularBricks = $regularBricks;
        $this->variantBrick = $variantBrick;
    }

    /**
     * Regular attributes indexed by attribute code
     *
     * @return AttributeInterface[]
     */
    public function getRegularAttributes()
    {
        $attrs = [];
        foreach ($this->regularBricks as $regularBrick) {
            foreach ($regularBrick->getAttributes() as $attribute) {
                $attrs[$attribute->getCode()] = $attribute;
            }
        }

        if (null !== $this->variantBrick) {
            foreach ($this->variantBrick->getAttributes() as $attribute) {
                $attrs[$attribute->getCode()] = $attribute;
            }
        }

        return $attrs;
    }

    /**
     * @return null|AttributeInterface
     */
    public function getVariantAttribute()
    {
        if (null === $this->variantBrick) {
            return null;
        }

        return $this->variantBrick->getVariantAttribute();
    }

    public function __toString()
    {
        $string = sprintf('This level has %d regular bricks.', count($this->regularBricks));

        if (null !== $this->variantBrick) {
            $string .= sprintf(
                ' It also has a variant brick (variant attribute %s).',
                $this->getVariantAttribute()->getCode()
            );
        }

        $string .= sprintf(' Attributes %s.', implode(', ', array_keys($this->getRegularAttributes())));

        return $string;
    }
}
