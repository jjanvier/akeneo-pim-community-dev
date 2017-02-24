<?php

namespace Acme\Bundle\RangeBundle\Normalizer\Flat;

use Acme\Bundle\RangeBundle\Model\Range;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\AbstractProductValueDataNormalizer;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RangeNormalizer extends AbstractProductValueDataNormalizer
{
    const DECIMAL_PRECISION = 4;

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Range && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    protected function doNormalize($object, $format = null, array $context = [])
    {
        $data = sprintf(
            'From %s to %s',
            $this->getDataAsString($object->getFromData(), $context),
            $this->getDataAsString($object->getToData(), $context)
        );

        return $data;
    }

    /**
     * @param mixed $data
     * @param array $context
     *
     * @return mixed
     */
    protected function getDataAsString($data, array $context)
    {
        if (null !== $data && is_numeric($data) && isset($context['is_decimals_allowed'])) {
            $data = $context['is_decimals_allowed']
                ? number_format($data, static::DECIMAL_PRECISION, '.', '') : (int) $data;
        }

        return (string) $data;
    }
}
