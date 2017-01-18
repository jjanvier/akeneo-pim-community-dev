<?php

namespace Pim\Component\TemplateAttribute;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\TemplateAttribute\Declinaison;

/**
 * declainaison value interface
 *
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DeclinaisonValueInterface
{
    /**
     * Set text data
     *
     * @param string $text
     *
     * @return DeclinaisonValueInterface
     */
    public function setText($text);

    /**
     * Set datetime data
     *
     * @param \Datetime $datetime
     *
     * @return DeclinaisonValueInterface
     */
    public function setDatetime($datetime);

    /**
     * Set integer data
     *
     * @param int $integer
     *
     * @return DeclinaisonValueInterface
     */
    public function setInteger($integer);

    /**
     * Set id
     *
     * @param int|string $id
     *
     * @return DeclinaisonValueInterface
     */
    public function setId($id);

    /**
     * Get related option, used for simple select to set single option
     *
     * @return AttributeOptionInterface
     */
    public function getOption();

    /**
     * Get media
     *
     * @return FileInfoInterface
     */
    public function getMedia();

    /**
     * Get decimal data
     *
     * @return float
     */
    public function getDecimal();

    /**
     * Set decimal data
     *
     * @param float $decimal
     *
     * @return DeclinaisonValueInterface
     */
    public function setDecimal($decimal);

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     *
     * @throws \LogicException
     *
     * @return DeclinaisonValueInterface
     */
    public function setAttribute(AttributeInterface $attribute = null);

    /**
     * Add option, used for multi select to add many options
     *
     * @param AttributeOptionInterface $option
     *
     * @return DeclinaisonValueInterface
     */
    public function addOption(AttributeOptionInterface $option);

    /**
     * Get boolean data
     *
     * @return bool
     */
    public function getBoolean();

    /**
     * Set options, used for multi select to set many options
     *
     * @param ArrayCollection $options
     *
     * @return DeclinaisonValueInterface
     */
    public function setOptions($options);

    /**
     * Set prices, used for multi select to set many prices
     *
     * @param ArrayCollection $prices
     *
     * @return DeclinaisonValueInterface
     */
    public function setPrices($prices);

    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Set varchar data
     *
     * @param string $varchar
     *
     * @return DeclinaisonValueInterface
     */
    public function setVarchar($varchar);

    /**
     * Set boolean data
     *
     * @param bool $boolean
     *
     * @return DeclinaisonValueInterface
     */
    public function setBoolean($boolean);

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get metric
     *
     * @return MetricInterface
     */
    public function getMetric();

    /**
     * Get date data
     *
     * @return \Datetime
     */
    public function getDate();

    /**
     * Get attribute
     *
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * Get declinaison
     *
     * @return Declinaison
     */
    public function getDeclinaison();

    /**
     * Set media
     *
     * @param FileInfoInterface $media
     *
     * @return DeclinaisonValueInterface
     */
    public function setMedia(FileInfoInterface $media = null);

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrices();

    /**
     * Get options, used for multi select to retrieve many options
     *
     * @return ArrayCollection
     */
    public function getOptions();

    /**
     * Get used locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set metric
     *
     * @param MetricInterface $metric
     *
     * @return DeclinaisonValueInterface
     */
    public function setMetric(MetricInterface $metric);

    /**
     * Add price (removing the older one)
     *
     * @param ProductPriceInterface $price
     *
     * @return DeclinaisonValueInterface
     */
    public function addPrice(ProductPriceInterface $price);

    /**
     * Get varchar data
     *
     * @return string
     */
    public function getVarchar();

    /**
     * Remove price
     *
     * @param ProductPriceInterface $price
     *
     * @return DeclinaisonValueInterface
     */
    public function removePrice(ProductPriceInterface $price);

    /**
     * Check if the value contains data
     *
     * @return bool
     */
    public function hasData();

    /**
     * Set used scope
     *
     * @param string $scope
     */
    public function setScope($scope);

    /**
     * Remove an option
     *
     * @param AttributeOptionInterface $option
     *
     * @return DeclinaisonValueInterface
     */
    public function removeOption(AttributeOptionInterface $option);

    /**
     * Get text data
     *
     * @return string
     */
    public function getText();

    /**
     * Set data
     *
     * @param mixed $data
     *
     * @return DeclinaisonValueInterface
     */
    public function setData($data);

    /**
     * Set option, used for simple select to set single option
     *
     * @param AttributeOptionInterface $option
     *
     * @return DeclinaisonValueInterface
     */
    public function setOption(AttributeOptionInterface $option = null);

    /**
     * Get the price matching the given currency
     *
     * @param string $currency
     *
     * @return null|ProductPriceInterface
     */
    public function getPrice($currency);

    /**
     * Set date data
     *
     * @param \Datetime $date
     *
     * @return DeclinaisonValueInterface
     */
    public function setDate($date);

    /**
     * Add data
     *
     * @param mixed $data
     *
     * @return DeclinaisonValueInterface
     */
    public function addData($data);

    /**
     * Set used locale
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * @return bool
     */
    public function isRemovable();

    /**
     * Get used scope
     *
     * @return string $scope
     */
    public function getScope();

    /**
     * Get datetime data
     *
     * @return \Datetime
     */
    public function getDatetime();

    /**
     * @param Declinaison $declinaison
     *
     * @return DeclinaisonValueInterface
     *
     */
    public function setDeclinason(Declinaison $declinaison);

    /**
     * Get integer data
     *
     * @return int
     */
    public function getInteger();

    /**
     * @return string
     */
    public function __toString();
}
