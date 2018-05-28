<?php

namespace Album\View\Helper;

use Zend\View\Helper\AbstractHelper;

class LowercaseHelper extends AbstractHelper
{
    public function __invoke($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        return strtolower($str);
    }
}
