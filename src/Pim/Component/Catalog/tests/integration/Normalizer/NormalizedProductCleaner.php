<?php

namespace Pim\Component\Catalog\tests\integration\Normalizer;

use Akeneo\Test\Integration\DateSanitizer;

/**
 * Clean a normalized product (aka, an array of data) so that it can be compared with the expected result
 * of the normalization. This cleaner:
 *      - sorts recursively the values by keys
 *      - take care of the inconsistent "created_at" and "updated_at" fields
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizedProductCleaner
{
    /**
     * @param array $productNormalized
     */
    public static function clean(array &$productNormalized)
    {
        self::sanitizeDateFields($productNormalized);
        self::sortValues($productNormalized['values']);
    }

    /**
     * @param array $values
     */
    public static function cleanOnlyValues(array &$values)
    {
        self::sortValues($values);
    }

    /**
     * Sort values by attribute code, then by channel, then by locale.
     *
     * We have different types of product value here.
     * Either standard values:
     *  "auto_exposure" => array:1 [
     *      0 => array:3 [
     *          "locale" => null
     *          "scope" => null
     *          "data" => true
     *      ]
     *  ]
     *
     * Either values normalized for the "storage" and "indexing" format:
     *  "auto_exposure-boolean" => array:1 [
     *      0 => array:1 [
     *          "<all_channels>" => array:1 [
     *              "<all_locales>" => true
     *          ]
     *      ]
     *  ]
     *
     * We need to sort both of these formats.
     *
     * @param array $values
     */
    private function sortValues(array &$values)
    {
        if (empty($values)) {
            return;
        }

        $firstValue = current($values);
        $keys = array_keys($firstValue);
        $isStandardFormat = is_integer(current($keys));

        if ($isStandardFormat) {
            $values = self::sortStandardValues($values);
        } else {
            // easy for the indexing and storage format as channels and locales are directly
            // accessible as keys
            self::ksortRecursive($values);
        }
    }

    /**
     * Here we index each values of an attribute by channel and by code
     * so that they can be easily sorted.
     *
     * @param array $allValues
     *
     * @return array
     */
    private function sortStandardValues(array $allValues)
    {
        // first sort values by attribute code
        ksort($allValues);
        $sortedValues = [];

        foreach ($allValues as $attributeCode => $attributeValues) {
            $attributeIndexedValues = [];
            foreach ($attributeValues as $value) {
                $channel = null === $value['scope'] ? 'channel' : $value['scope'];
                $locale = null === $value['locale'] ? 'locale' : $value['locale'];
                $attributeIndexedValues[$channel . '-' . $locale] = $value;
            }
            ksort($attributeIndexedValues);
            $sortedValues[$attributeCode] = array_values($attributeIndexedValues);
        }

        return $sortedValues;
    }

    /**
     * @param mixed $array
     * @param int   $sort_flags
     *
     * @return bool
     */
    private function ksortRecursive(&$array, $sort_flags = SORT_REGULAR)
    {
        if (!is_array($array)) {
            return false;
        }

        ksort($array, $sort_flags);
        foreach ($array as &$arr) {
            self::ksortRecursive($arr, $sort_flags);
        }

        return true;
    }

    /**
     * Replaces dates fields (created/updated) in the $data array by self::DATE_FIELD_COMPARISON.
     *
     * @param array $data
     */
    private function sanitizeDateFields(array &$data)
    {
        $data['created'] = DateSanitizer::sanitize($data['created']);
        $data['updated'] = DateSanitizer::sanitize($data['updated']);
    }
}
