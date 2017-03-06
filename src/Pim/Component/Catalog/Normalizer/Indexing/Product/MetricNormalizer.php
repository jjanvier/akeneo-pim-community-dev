<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric entity to the indexing format.
 * This format is based on the standard format but differs from the following:
 *      - base data is added
 *      - base unit is added
 *      - metric family is added
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->stdNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = [])
    {
        $rawMetric = $this->stdNormalizer->normalize($metric, $format, $context);

        $rawMetric['base_data'] = $metric->getBaseData();
        $rawMetric['base_unit'] = $metric->getBaseUnit();
        $rawMetric['family'] = $metric->getFamily();

        return $rawMetric;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricInterface && 'indexing' === $format;
    }
}
