<?php

namespace Pim\Bundle\CatalogBundle\Completeness;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessCalculator3
{
    /** @var ProductValueFactory */
    protected $productValueFactory;

    /** @var CachedObjectRepositoryInterface */
    protected $channelRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $localeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ProductValueFactory             $productValueFactory
     * @param CachedObjectRepositoryInterface $channelRepository
     * @param CachedObjectRepositoryInterface $localeRepository
     * @param NormalizerInterface             $normalizer
     */
    public function __construct(
        ProductValueFactory $productValueFactory,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository,
        NormalizerInterface $normalizer
    ) {
        $this->productValueFactory = $productValueFactory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * Generates a two dimensional array of completenesses indexed by locale and channel.
     *
     * @param ProductInterface $product
     *
     * @return CompletenessInterface[]
     */
    public function calculate(ProductInterface $product)
    {
        if (null === $product->getFamily()) {
            return [];
        }

        $completenesses = [];
        $requiredProductValueCollectionsList = $this->getRequiredProductValueCollections($product->getFamily());
        $actualProductValueCollection = $product->getValues();

        foreach ($requiredProductValueCollectionsList as $channelCode => $requiredProductValueCollections) {
            foreach ($requiredProductValueCollections as $localeCode => $requiredProductValueCollection) {
                $completenesses[$channelCode][$localeCode] = $this->generateCompleteness(
                    $requiredProductValueCollection,
                    $actualProductValueCollection,
                    $channelCode,
                    $localeCode
                );
            }
        }

        return $completenesses;
    }

    /**
     * Generates a two dimensional array indexed by scope and locale containing the required product value collections.
     *
     * This method takes into account the localizable and scopable characteristic of the product value (meaning a
     * product value can be added to multiple productValueCollection if not localizable for instance).
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function getRequiredProductValueCollections(FamilyInterface $family)
    {
        $productValueCollections = [];

        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            foreach ($attributeRequirement->getChannel()->getLocales() as $locale) {
                if ($attributeRequirement->isRequired()) {
                    $channelCode = $attributeRequirement->getChannelCode();
                    $localeCode = $locale->getCode();

                    $attribute = $attributeRequirement->getAttribute();
                    $value =$this->productValueFactory->create(
                        $attribute,
                        $attribute->isScopable()? $channelCode : null,
                        $attribute->isLocalizable() ? $localeCode : null,
                        null
                    );

                    if (!isset($productValueCollections[$channelCode][$localeCode])) {
                        $productValueCollections[$channelCode][$localeCode] = new ProductValueCollection();
                    }

                    $this->addValueToCollections($value, $productValueCollections);
                }
            }
        }

        return $productValueCollections;
    }

    /**
     * Add the (empty) required product value to the right product value collections depending on the localizable and
     * scopable characteristics of product value.
     *
     * @param ProductValueInterface               $value
     * @param ProductValueCollectionInterface[][] $collectionOfProductValueCollections
     */
    protected function addValueToCollections(ProductValueInterface $value, array $collectionOfProductValueCollections)
    {
        foreach ($collectionOfProductValueCollections as $channelCode => $productValueCollections) {
            foreach ($productValueCollections as $localeCode => $productValueCollection) {
                if ((null === $value->getScope() || $channelCode = $value->getScope()) &&
                    (null === $value->getLocale() || $localeCode = $value->getLocale())
                ) {
                    $productValueCollection->add($value);
                }
            }
        }
    }

    /**
     * Generate one completeness for given requiredProductValue, channelcode, localeCode and the product values to
     * compare.
     *
     * @param ProductValueCollectionInterface $requiredProductValueCollection
     * @param ProductValueCollectionInterface $actualProductValueCollection
     * @param string                          $channelCode
     * @param string                          $localeCode
     *
     * @return CompletenessInterface
     */
    private function generateCompleteness(
        ProductValueCollectionInterface $requiredProductValueCollection,
        ProductValueCollectionInterface $actualProductValueCollection,
        $channelCode,
        $localeCode
    ) {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);

        $requiredStandardProductValues = $requiredProductValueCollection->getKeys();
        $actualStandardProductValues = $requiredProductValueCollection->getKeys();

        // Or implement the diff in the ProductValueCollection
        $missingKeys = array_diff($requiredStandardProductValues, $actualStandardProductValues);

        $completeness = new Completeness();
        $completeness->setChannel($channel);
        $completeness->setLocale($locale);

        $requiredCount = 0;

        foreach ($missingKeys as $key) {
            $productValue = $actualProductValueCollection->getByKey($key);
            $completeness->addMissingAttribute($productValue->getAttribute());

            $requiredCount++;
        }

        $completeness->setRequiredCount($requiredCount);

        return $completeness;
    }
}
