<?php
namespace Album\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

// Plugin class
class AccessPlugin extends AbstractPlugin
{
    // This method checks whether user is allowed
    // to visit the page
    public function checkAccess($actionName)
    {
        if ($actionName == 'test') {
            return "true";
        }
        else {
            return "false";
        }
    }
}
