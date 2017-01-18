<?php


namespace Pim\Component\TemplateAttribute;


use Pim\Component\Catalog\Model\AttributeInterface;

class TemplateAttribute
{
    /** @var Brick[] */
    private $regularBricks = [];
    /** @var Brick[] */
    private $variantBricks = [];
    /** @var string */
    private $name;

    public function __construct(array $bricks, $name)
    {
        $this->name = $name;

        foreach ($bricks as $brick) {
            if (!$brick instanceof Brick) {
                throw new \InvalidArgumentException('Brick expected brp.');
            }

            if (!$brick->isVariant()) {
                $this->regularBricks[] = $brick;
            } else {
                $this->variantBricks[] = $brick;
            }

            if (count($this->variantBricks) > 2) {
                throw new \LogicException('Hey ho. 2 variant bricks is enough :)');
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

        return $attrs;
    }

    /**
     * Variant attributes indexed by attribute code
     *
     * @return AttributeInterface[]
     */
    public function getVariantAttributes()
    {
        $attrs = [];
        foreach ($this->variantBricks as $variantBrick) {
            foreach ($variantBrick->getVariantAttribute() as $attribute) {
                $attrs[$attribute->getCode()] = $attribute;
            }
        }

        return $attrs;
    }

    /**
     * @return Brick[]
     */
    public function getRegularBricks()
    {
        return $this->regularBricks;
    }

    /**
     * @return Brick[]
     */
    public function getVariantBricks()
    {
        return $this->variantBricks;
    }

    /*
    public function getVariantAttributes($level)
    {
        if ($level > count($this->variantBricks)) {
            throw new \InvalidArgumentException('Impossible to retrieve the attributes of this level');
        }

        return $this->variantBricks[$level - 1]->getAttributes();
    }
    */
}
