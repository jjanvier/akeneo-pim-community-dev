<?php

namespace Pim\Bundle\DataGridBundle\Normalizer;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $standardNormalizer;
    /**
     * @var CachedObjectRepositoryInterface
     */
    protected $familyRepository;
    /**
     * @var GroupRepositoryInterface
     */
    protected $grouRepository;

    /**
     * @param NormalizerInterface             $standardNormalizer
     * @param CachedObjectRepositoryInterface $familyRepository
     * @param GroupRepositoryInterface        $grouRepository
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        CachedObjectRepositoryInterface $familyRepository,
        GroupRepositoryInterface $grouRepository
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->familyRepository = $familyRepository;
        $this->grouRepository = $grouRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!isset($context['locale']) || !isset($context['channel'])) {
            throw new \LogicException('"locale" and "channel" keys are required');
        }

        $locale = $context['locale'];
        $channel = $context['channel'];

        $context['channels'] = [$channel];
        $context['locales'] = [$locale];

        $standardProduct = $this->standardNormalizer->normalize($product, 'standard', $context);

        $standardProduct['family'] = $this->getFamilyLabel($standardProduct, $locale);
        $standardProduct['groups'] = $this->getGroupsLabels($standardProduct, $locale);
        $standardProduct['completeness'] = $this->getCompleteness($product, $locale, $channel);
        $standardProduct['label'] = $product->getLabel($locale);

        return $standardProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'datagrid' === $format;
    }

    /**
     * @param array $standardProduct
     *
     * @return string|null
     */
    protected function getFamilyLabel(array $standardProduct, $locale)
    {
        $family = $this->familyRepository->findOneByIdentifier($standardProduct['family']);
        if (null === $family) {
            return null;
        }

        return $family->getTranslation($locale)->getLabel();
    }

    /**
     * @param array $standardProduct
     *
     * @return string|null
     */
    protected function getGroupsLabels(array $standardProduct, $locale)
    {
        $groups = [];
        foreach ($standardProduct['groups'] as $groupCode) {
            $group = $this->grouRepository->findOneByIdentifier($groupCode);

            if (null !== $group) {
                $groups[] = $group->getTranslation($locale)->getLabel();
            }
        }

        return implode(', ', $groups);
    }

    /**
     * @param ProductInterface $product
     * @param string           $locale
     * @param string           $channel
     *
     * @return int|null
     */
    protected function getCompleteness(ProductInterface $product, $locale, $channel)
    {
        foreach ($product->getCompletenesses() as $completeness) {
            if ($completeness->getLocale()->getCode() === $locale && $completeness->getChannel()->getCode() === $channel) {
                return $completeness->getRatio();
            }
        }

        return null;
    }
}
