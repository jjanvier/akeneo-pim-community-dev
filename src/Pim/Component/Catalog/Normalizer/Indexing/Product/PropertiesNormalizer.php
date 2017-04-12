<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Pim\Component\Catalog\Repository\AssociationRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Transform the properties of a product object (fields and product values)
 * to the indexing format.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertiesNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    const FIELD_COMPLETENESS = 'completeness';
    const FIELD_IS_ASSOCIATED = 'is_associated';
    const FIELD_IN_GROUP = 'in_group';
    const FIELD_ID = 'id';

    /** @var AssociationRepositoryInterface */
    protected $associationRepository;

    /**
     * @param AssociationRepositoryInterface $associationRepository
     */
    public function __construct(AssociationRepositoryInterface $associationRepository)
    {
        $this->associationRepository = $associationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = (string) $product->getId();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = $product->getIdentifier();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->serializer->normalize(
            $product->getCreated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_UPDATED] = $this->serializer->normalize(
            $product->getUpdated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = $this->serializer->normalize(
            $product->getFamily(),
            $format
        );

        $data[StandardPropertiesNormalizer::FIELD_ENABLED] = (bool) $product->isEnabled();
        $data[StandardPropertiesNormalizer::FIELD_CATEGORIES] = $product->getCategoryCodes();

        $data[StandardPropertiesNormalizer::FIELD_GROUPS] = $product->getGroupCodes();
        $data[StandardPropertiesNormalizer::FIELD_VARIANT_GROUP] = null !== $product->getVariantGroup()
            ? $product->getVariantGroup()->getCode() : null;

        foreach ($product->getGroupCodes() as $groupCode) {
            $data[self::FIELD_IN_GROUP][$groupCode] = true;
        }

        $data[self::FIELD_IS_ASSOCIATED] = $this->getOwnerProducts($product);

        $data[self::FIELD_COMPLETENESS] = !$product->getCompletenesses()->isEmpty()
            ? $this->serializer->normalize($product->getCompletenesses(), 'indexing', $context) : [];

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$product->getValues()->isEmpty()
            ? $this->serializer->normalize($product->getValues(), 'indexing', $context) : [];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'indexing' === $format;
    }

    /**
     * Creates the "is_associated" field.
     *
     * If we consider 3 products "foo", "bar" and "bar", where "bar" and "baz"
     * are associated to "foo" through association type "pack" and "upsell",
     * respectively, this corresponds to the following in standard format:
     *
     * [
     *     "identifier"   => "foo",
     *     "associations" => [
     *         "pack"   => [
     *             "bar",
     *         ],
     *         "upsell" => [
     *             "baz",
     *         ],
     *     ],
     * ]
     *
     * When we index the "bar" product in Elasticsearch, we create the field
     * "is_associated" as follow:
     *
     * [
     *     "is_associated" => [
     *         "pack" => [
     *             "foo" => true,
     *         ],
     *     ],
     * ]
     *
     * and we index the following for the "baz" product:
     *
     *
     *
     * [
     *     "is_associated" => [
     *         "upsell" => [
     *             "foo" => true,
     *         ],
     *     ],
     * ]
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOwnerProducts(ProductInterface $product)
    {
        $isAssociated = [];

        $associations = $this->associationRepository->getAssociationsContainingProduct($product);

        foreach ($associations as $association) {
            $associationTypeCode = $association->getAssociationType()->getCode();
            $ownerIdentifier = $association->getOwner()->getIdentifier();

            $isAssociated[$associationTypeCode][$ownerIdentifier] = true;
        }

        return $isAssociated;
    }
}
