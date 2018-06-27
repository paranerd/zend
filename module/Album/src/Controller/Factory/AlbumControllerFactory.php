<?php
namespace Album\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Album\Controller\AlbumController;
use Album\Model\AlbumTable;
use Interop\Container\ContainerInterface;

// Factory class
class AlbumControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $my_int = 1234;
        $table = $container->get(AlbumTable::class);
        $session_container = $container->get('ContainerNamespace');

        return new AlbumController($table, $session_container, $my_int);
    }
}
