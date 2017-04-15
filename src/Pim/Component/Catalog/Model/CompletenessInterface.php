<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Product completeness interface, define the completeness of the enrichment of the product.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CompletenessInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * Returns the completeness corresponding product.
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Returns the completeness corresponding channel.
     *
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * Returns the completeness corresponding locale.
     *
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * Returns the collection of the attributes corresponding to the missing
     * (i.e. incomplete) product values.
     *
     * @return Collection
     */
    public function getMissingAttributes();

    /**
     * Returns the collection of the attributes corresponding to the already
     * filled in (i.e. complete) product values.
     *
     * @return Collection
     */
    public function getFilledInAttributes();

    /**
     * Returns the number of missing product values.
     *
     * @return int
     */
    public function getMissingCount();

    /**
     * Returns the number of already filled in product values.
     *
     * @return int
     */
    public function getFilledInCount();

    /**
     * Returns the total number of required product values for the product to be complete.
     *
     * @return int
     */
    public function getRequiredCount();

    /**
     * Returns the completeness ratio (percentage).
     *
     * @return int
     */
    public function getRatio();
}
