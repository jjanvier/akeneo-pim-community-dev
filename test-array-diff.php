<?php

function array_diff_recursive($arr1, $arr2, $depth=0, $maxdepth=3)
{
    $outputDiff = [];

    foreach ($arr1 as $key => $value) {
        //if the key exists in the second array, recursively call this function
        //if it is an array, otherwise check if the value is in arr2
        if (array_key_exists($key, $arr2)) {
            if (is_array($value)) {
                if ($depth < $maxdepth) {
                    $depth++;
                    $recursiveDiff = array_diff_recursive($value, $arr2[$key], $depth);

                    if (count($recursiveDiff)) {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                }
            } else {
                if (!in_array($value, $arr2)) {
                    $outputDiff[$key] = $value;
                }
            }
        }
        //if the key is not in the second array, check if the value is in
        //the second array (this is a quirk of how array_diff works)
        else {
            if (!in_array($value, $arr2)) {
                $outputDiff[$key] = $value;
            }
        }
    }

    return $outputDiff;
}

function array_diff_key_recursive ($a1, $a2, $depth=0, $maxdepth=2) {
    foreach($a1 as $k => $v) {
        //$r[$k] = is_array($v) ? $this->array_diff_key_recursive($a1[$k], $a2[$k]) : array_diff_key($a1, $a2);
        if ($depth >= $maxdepth) {
            return;
        }
        if (is_array($v)) {
            $depth++;
            $r[$k]=array_diff_key_recursive($a1[$k], $a2[$k]);
        }else
        {
            $r=array_diff_key($a1, $a2);
        }

        if (is_array($r[$k]) && count($r[$k])==0)
        {
            unset($r[$k]);
        }
    }
    return $r;
}

$originalData = '{"description":{"mobile":{"en_US":"Akeneo T-Shirt","fr_FR":"T-Shirt Akeneo"}},"clothing_size":{"<all_channels>":{"<all_locales>":"xs"}},"main_color":{"<all_channels>":{"<all_locales>":"black"}},"secondary_color":{"<all_channels>":{"<all_locales>":"purple"}},"tshirt_materials":{"<all_channels>":{"<all_locales>":"cotton"}},"tshirt_style":{"<all_channels>":{"<all_locales>":["crewneck","short_sleeve"]}},"price":{"<all_channels>":{"<all_locales>":[{"amount":"10.00","currency":"EUR"},{"amount":"14.00","currency":"USD"}]}}}';
$requiredData = '{"description":{"mobile":{"en_US":"Akeneo BITE T-Shirt","fr_FR":"T-Shirt Akeneo"},"print":{"en_US":"Akeneo T-Shirt with short sleeve","fr_FR":"T-Shirt Akeneo manches courtes"}},"main_color":{"<all_channels>":{"<all_locales>":"black"}},"name":{"<all_channels>":{"<all_locales>":"Akeneo T-Shirt black and purple with short sleeve"}}}';

$original = json_decode($originalData, true);
$required = json_decode($requiredData, true);

$diff = array_diff_recursive($required, $original);
$diff = array_diff_key_recursive($required, $original);

var_dump($diff);
