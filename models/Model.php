<?php

class Model extends \DB\SQL\Mapper{

    function safe_copyfrom($var, array $fieldlist, bool $whitelist = false){
        $this->copyfrom('POST', function($val) use($fieldlist, $whitelist){
            if($whitelist){
                return array_intersect_key(
                    $val, array_flip($fieldlist)
                );
            }
            else{
                return array_diff_key(
                    $val, array_flip($fieldlist)
                );
            }
		});
    }

}