<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    protected $productNormalizer;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var VersionManager */
    protected $versionManager;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /** @var FormProviderInterface */
    protected $formProvider;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /**
     * @param NormalizerInterface               $productNormalizer
     * @param NormalizerInterface               $versionNormalizer
     * @param VersionManager                    $versionManager
     * @param LocaleRepositoryInterface         $localeRepository
     * @param StructureVersionProviderInterface $structureVersionProvider
     * @param FormProviderInterface             $formProvider
     * @param AttributeConverterInterface       $localizedConverter
     * @param ConverterInterface                $productValueConverter
     */
    public function __construct(
        NormalizerInterface $productNormalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter
    ) {
        $this->productNormalizer = $productNormalizer;
        $this->versionNormalizer = $versionNormalizer;
        $this->versionManager = $versionManager;
        $this->localeRepository = $localeRepository;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->formProvider = $formProvider;
        $this->localizedConverter = $localizedConverter;
        $this->productValueConverter = $productValueConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $normalizedProduct = $this->productNormalizer->normalize($product, 'standard', $context);
        $normalizedProduct['values'] = $this->localizedConverter->convertToLocalizedFormats(
            $normalizedProduct['values'],
            $context
        );

        $normalizedProduct['values'] = $this->productValueConverter->convert($normalizedProduct['values']);

        $oldestLog = $this->versionManager->getOldestLogEntry($product);
        $newestLog = $this->versionManager->getNewestLogEntry($product);

        $created = null !== $oldestLog ? $this->versionNormalizer->normalize($oldestLog, 'internal_api') : null;
        $updated = null !== $newestLog ? $this->versionNormalizer->normalize($newestLog, 'internal_api') : null;

        $normalizedProduct['meta'] = [
            'form'              => $this->formProvider->getForm($product),
            'id'                => $product->getId(),
            'created'           => $created,
            'updated'           => $updated,
            'model_type'        => 'product',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
        ] + $this->getLabels($product) + $this->getAssociationMeta($product) + $this->getCompleteness($product);

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormat);
    }

    /**
     * Get labels of product, family & groups
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getLabels(ProductInterface $product)
    {
        $labels = [];

        foreach ($this->localeRepository->getActivatedLocaleCodes() as $localeCode) {
            $labels['product'][$localeCode] = $product->getLabel($localeCode);
        }

        $family = $product->getFamily();
        foreach ($family->getTranslations() as $translation) {
            $label = '' !== $translation->getLabel() ? $translation->getLabel() : sprintf('[%s]', $family->getCode());
            $labels['family'][$translation->getLocale()] = $label;
        }

        foreach ($product->getGroups() as $group) {
            foreach ($group->getTranslations() as $translation) {
                $label = '' !== $translation->getLabel() ? $translation->getLabel() : sprintf('[%s]', $group->getCode());
                $labels['groups'][$translation->getLocale()][] = $label;
            }
        }

        return ['labels' => $labels];
    }

    /**
     * Get the completenesses of the product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getCompleteness(ProductInterface $product)
    {
        $completenesses = [];

        foreach ($product->getCompletenesses() as $completeness) {
            $completenesses[$completeness->getChannel()->getCode()][$completeness->getLocale()->getCode()] = $completeness->getRatio();
        }

        return ['completeness' => $completenesses];
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getAssociationMeta(ProductInterface $product)
    {
        $meta = [];
        $associations = $product->getAssociations();

        foreach ($associations as $association) {
            $associationType = $association->getAssociationType();
            $meta[$associationType->getCode()]['groupIds'] = array_map(
                function ($group) {
                    return $group->getId();
                },
                $association->getGroups()->toArray()
            );
        }

        return ['associations' => $meta];
    }
}
