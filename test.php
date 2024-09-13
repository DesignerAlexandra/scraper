<?php

$arr = [[1,2,3], [4,5,6]];

foreach ($arr as $key => $value) {
    
    foreach ($value as $id => $value1) {
        if(!in_array(2, $value)) {
            var_dump($value[$id]);
        }

        
    }

}