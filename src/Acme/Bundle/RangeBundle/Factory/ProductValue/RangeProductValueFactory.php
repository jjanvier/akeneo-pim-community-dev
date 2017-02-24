<?php

namespace Acme\Bundle\RangeBundle\Factory\ProductValue;

use Acme\Bundle\RangeBundle\Factory\RangeFactory;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RangeProductValueFactory implements ProductValueFactoryInterface
{
    /** @var RangeFactory */
    protected $rangeFactory;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param RangeFactory $rangeFactory
     * @param string       $productValueClass
     * @param string       $supportedAttributeType
     */
    public function __construct(RangeFactory $rangeFactory, $productValueClass, $supportedAttributeType)
    {
        $this->rangeFactory = $rangeFactory;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = ['from_data' => null, 'to_data' => null];
        }

        $value = new $this->productValueClass(
            $attribute,
            $channelCode,
            $localeCode,
            $this->rangeFactory->createRange($data['from_data'], $data['to_data'])
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks the data.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidArgumentException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'range',
                'factory',
                gettype($data)
            );
        }

        if (!array_key_exists('from_data', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'from_data',
                'range',
                'factory',
                implode(', ', array_keys($data))
            );
        }

        if (!array_key_exists('to_data', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'to_data',
                'range',
                'factory',
                implode(', ', array_keys($data))
            );
        }
    }
}
