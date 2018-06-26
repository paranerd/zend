<?php

namespace Album\View\Helper;

use Zend\View\Helper\AbstractHelper;

class BreadcrumbsHelper extends AbstractHelper
{
    public function __construct($items=[])
    {
        $this->items = $items;
    }

    public function setItems($items) {
        $this->items = $items;
    }

    public function render() {
        if (count($this->items) == 0) {
            return '';
        }

        $result = '<ol class="breadcrumb">';

        // Get item count
        $itemCount = count($this->items);

        $itemNum = 1; // item counter

        foreach ($this->items as $label => $link) {
            $active = ($itemNum == $itemCount);
            $result .= '<li><a href="' . $link . '">' . $label . '</a></li>';

            $itemNum++;
        }

        $result .= '</ol>';

        return $result;
    }
}
