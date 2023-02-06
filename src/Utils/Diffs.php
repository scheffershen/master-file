<?php

namespace App\Utils;

class Diffs implements DiffsInterface
{
    public function diffs(array $old, array $new): array
    {
    	$diffs = []; 
    	foreach ($new as $key => $value) {
    		if (array_key_exists($key, $old)) {
    			if ($value != $old[$key] ) {
    				$diffs[$key]['old'] = $old[$key];
    				$diffs[$key]['new'] = $value;
    			}
    		} else {
				$diffs[$key]['old'] = '';
				$diffs[$key]['new'] = $value;
    		}
    	}
    	return $diffs;
    }

    public function diffsCorrespondance(array $old, array $new): array
    {
        $diffs = []; 
        foreach ($new as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                if (array_key_exists($k2, $old[$k1])) {
                    if ($v2 != $old[$k1][$k2] ) {
                        $diffs[$k2]['old'] = $old[$k1][$k2];
                        $diffs[$k2]['new'] = $v2;
                    }
                } else {
                    $diffs[$k2]['old'] = '';
                    $diffs[$k2]['new'] = $v2;
                }
            }
        }
        return $diffs;
    }    
}
