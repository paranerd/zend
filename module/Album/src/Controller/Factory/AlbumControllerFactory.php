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

        return new AlbumController($container->get(AlbumTable::class), $my_int);
    }
}
