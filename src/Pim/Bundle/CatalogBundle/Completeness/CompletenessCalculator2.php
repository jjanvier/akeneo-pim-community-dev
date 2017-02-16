<?php

namespace Pim\Bundle\CatalogBundle\Completeness;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Calculates the completeness of a product given a family
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculator2
{
    /** @var ProductValueFactory */
    protected $productValueFactory;

    /** @var CachedObjectRepositoryInterface */
    protected $channelRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param ProductValueFactory             $productValueFactory
     * @param CachedObjectRepositoryInterface $channelRepository
     * @param CachedObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        ProductValueFactory $productValueFactory,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository
    ) {
        $this->productValueFactory = $productValueFactory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
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
        $requiredCount = [];
        $requiredValues = $this->getRequiredProductValueCollections($product->getFamily());
        $actualValues = $product->getValues();

        $missingValues = $actualValues->filter(
            function (ProductValueInterface $value) use ($requiredValues) {
                // Not precise enough here
                // Create method isDataEmpty in ProductValueInterface
                return null !== $requiredValues->getByCodes(
                    $value->getAttribute()->getCode(),
                    $value->getScope(),
                    $value->getLocale()
                );
            }
        );

        /** @var ProductValueInterface $missingValue */
        foreach ($missingValues as $missingValue) {
            if (!isset($completenesses[$missingValue->getScope()][$missingValue->getLocale()])) {
                // maybe rework the Completeness object to have an immutable model that is part of the product
                // this could allow to automatically calculate the missing and ratio
                $completeness = new Completeness();
                $channel = $this->channelRepository->findOneByIdentifier($missingValue->getScope());
                $locale = $this->localeRepository->findOneByIdentifier($missingValue->getLocale());

                $completeness->setChannel($channel);
                $completeness->setLocale($locale);

                $completenesses[$missingValue->getScope()][$missingValue->getLocale()] = $completeness;
                $requiredCount[$missingValue->getScope()][$missingValue->getLocale()] = 0;
            }

            $requiredCount[$missingValue->getScope()][$missingValue->getLocale()]++;

            /** @var CompletenessInterface $completeness */
            $completeness = $completenesses[$missingValue->getScope()][$missingValue->getLocale()];
            $completeness->addMissingAttribute($missingValue->getAttribute());
            $completeness->setRequiredCount($requiredCount[$missingValue->getScope()][$missingValue->getLocale()]);
        }

        return $completenesses;
    }

    /**
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
     * @param ProductValueInterface      $value
     * @param ProductValueCollection[][] $collectionOfProductValueCollections
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
}
