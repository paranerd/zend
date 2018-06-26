<?php
namespace Album\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

// Plugin class
class AccessPlugin extends AbstractPlugin
{
    // This method checks whether user is allowed
    // to visit the page
    public function checkAccess($username)
    {
        if ($username == 'bob') {
            return "yes";
        }
        else {
            return "no";
        }
    }
}
