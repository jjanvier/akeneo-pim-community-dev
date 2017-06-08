<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Akeneo\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;

class ProductModel implements ProductModelInterface
{
    /** @var int|string */
    private $id;

    /** @var array */
    private $rawValues;

    /**
     * Not persisted. Loaded on the fly via the $rawValues.
     *
     * @var ProductValueCollectionInterface
     */
    private $values;

    /** @var FamilyInterface $family */
    private $family;

    /** @var string */
    private $identifier;

    /** @var ProductModelInterface */
    private $parentModel;

    /** @var Collection of ProductModelInterface */
    private $childrenModels;

    /** @var int */
    private $left;

    /** @var int */
    private $level;

    /** @var int */
    private $right;

    /** @var int */
    private $root;

    private $categories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ProductValueCollection();
        $this->childrenModels = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function addValue(ProductValueInterface $value)
    {
        $this->values->add($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeValue(ProductValueInterface $value)
    {
        $this->values->remove($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedAttributeCodes()
    {
        return $this->values->getAttributesKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null)
    {
        return $this->values->getByCodes($attributeCode, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValues()
    {
        return $this->rawValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValues(array $rawValues)
    {
        $this->rawValues = $rawValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute, $this->values->getAttributes(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family = null)
    {
        if (null !== $family) {
            $this->familyId = $family->getId();
        }
        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->values->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(ProductValueCollectionInterface $values)
    {
        $this->values = $values;

        return $this;
    }

    public function setModel(ProductModelInterface $model)
    {
        $this->parentModel = $model;

        return $this;
    }

    public function getModel()
    {
        return $this->parentModel;
    }

    public function hasModel()
    {
       return null !== $this->parentModel;
    }

    public function isRootModel()
    {
       return !$this->hasModel();
    }

    /**
     * {@inheritdoc}
     */
    public function addChildModel(ProductModelInterface $child)
    {
        $child->setModel($this);
        $this->childrenModels[] = $child;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChildModel(ProductModelInterface $child)
    {
        $this->childrenModels->removeElement($child);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildrenModels()
    {
        return count($this->getChildrenModels()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenModels()
    {
        return $this->childrenModels;
    }

    public function getAllValues()
    {
        $values = $this->getValues();

        return $this->getParentValuesByRecursion($this, $values);

    }

    public function getParentValues() {

        return $this->getParentValuesByRecursion($this);

    }

    private function getParentValuesByRecursion(
        CanHaveProductModelInterface $canHaveProductModel,
        ProductValueCollectionInterface $values = null
    ) {
        if (null === $values) {
            $values = new ProductValueCollection();
        }

        if (false === $canHaveProductModel->hasModel()) {
            return $values;
        }

        $parentModel = $canHaveProductModel->getModel();
        foreach ($parentModel->getValues() as $value) {
            $values->add($value);
        }

        return $this->getParentValuesByRecursion($parentModel, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function addCategory(BaseCategoryInterface $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategory(BaseCategoryInterface $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryCodes()
    {
        $codes = [];
        foreach ($this->getCategories() as $category) {
            $codes[] = $category->getCode();
        }
        sort($codes);

        return $codes;
    }

}
