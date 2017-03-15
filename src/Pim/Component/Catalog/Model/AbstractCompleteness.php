<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Abstract product completeness entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCompleteness implements CompletenessInterface
{
    /** @var int|string */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var ChannelInterface */
    protected $channel;

    /** @var LocaleInterface */
    protected $locale;

    /** @var Collection */
    protected $missingAttributes;

    /** @var Collection */
    protected $filledInAttributes;

    /** @var int */
    protected $missingCount;

    /** @var int */
    protected $filledInCount;

    /** @var int */
    protected $requiredCount;

    /** @var int */
    protected $ratio;

    /**
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     * @param Collection       $missingAttributes
     * @param Collection       $filledInAttributes
     * @param int              $missingCount
     * @param int              $filledInCount
     * @param int              $requiredCount
     */
    public function __construct(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale,
        Collection $missingAttributes,
        Collection $filledInAttributes,
        $missingCount,
        $filledInCount,
        $requiredCount
    ) {
        $this->product = $product;
        $this->channel = $channel;
        $this->locale = $locale;
        $this->missingAttributes = $missingAttributes;
        $this->filledInAttributes = $filledInAttributes;
        $this->missingCount = $missingCount;
        $this->filledInCount = $filledInCount;
        $this->requiredCount = $requiredCount;

        $this->ratio = (int) round(100 * ($this->requiredCount - $this->missingCount) / $this->requiredCount);
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getMissingAttributes()
    {
        return $this->missingAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilledInAttributes()
    {
        return $this->filledInAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getMissingCount()
    {
        return $this->missingCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilledInCount()
    {
        return $this->filledInCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredCount()
    {
        return $this->requiredCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getRatio()
    {
        return $this->ratio;
    }
}
