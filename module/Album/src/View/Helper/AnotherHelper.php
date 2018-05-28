<?php

namespace Album\View\Helper;

use Zend\View\Helper\AbstractHelper;

class AnotherHelper extends AbstractHelper
{
    public function __invoke($str)
    {
        return $this;
    }

    public function find($str, $find) {
        if (!is_string($str)){
            return 'must be string';
        }

        if (strpos($str, $find) === false){
            return 'not found';
        }

        return 'found';
    }

    public function lowercase($str) {
        if (!is_string($str)) {
            return $str;
        }

        return strtolower($str);
    }
}
