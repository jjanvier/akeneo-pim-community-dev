<?php

namespace Acme\Bundle\RangeBundle\Normalizer\Standard\Product;

use Acme\Bundle\RangeBundle\Model\Range;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RangeNormalizer implements NormalizerInterface
{
    const DECIMAL_PRECISION = 4;

    /**
     * {@inheritdoc}
     */
    public function normalize($range, $format = null, array $context = [])
    {
        $fromData = $this->getDecimalOrInteger($range->getFromData(), $context);
        $toData = $this->getDecimalOrInteger($range->getToData(), $context);

        return [
            'from_data' => $fromData,
            'to_data'   => $toData,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Range && 'standard' === $format;
    }

    /**
     * @param mixed $data
     * @param array $context
     *
     * @return mixed
     */
    protected function getDecimalOrInteger($data, array $context)
    {
        if (null !== $data && is_numeric($data) && isset($context['is_decimals_allowed'])) {
            $data = $context['is_decimals_allowed']
                ? number_format($data, static::DECIMAL_PRECISION, '.', '') : (int) $data;
        }

        return $data;
    }
}
