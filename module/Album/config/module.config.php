<?php

namespace Album;

use Zend\Router\Http\Segment;
use Zend\Router\Http\Regex;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    /*'controllers' => [
        'factories' => [
            Controller\AlbumController::class => InvokableFactory::class,
        ],
    ],*/
    'router' => [
        'routes' => [
            'album' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/album[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AlbumController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'routingtest' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/routing[/:param1[/:param2]]',
                    'constraints' => [
                        'param1' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'param2' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AlbumController::class,
                        'action'     => 'routing',
                    ],
                ],
            ],
            'regexrouting' => [
                'type' => Regex::class,
                'options' => [
                    'regex'    => '/regex(?<page>\/[a-zA-Z0-9_\-]+)\/*(?<section>[a-z]*)',
                    'defaults' => [
                        'controller' => Controller\AlbumController::class,
                        'action'     => 'regex',
                    ],
                    'spec'=>'/doc/%page%.html'
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'album/album/maptest' => __DIR__ . '/../view/album/mapped/index.phtml',
        ],
        'template_path_stack' => [
            'album' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
        //'not_found_template'       => 'error/my_404',
        //'exception_template'       => 'error/index',
    ],
    'controllers' => [
        'factories' => [
            Controller\AlbumController::class => Controller\Factory\AlbumControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\AccessPlugin::class => InvokableFactory::class,
        ],
        'aliases' => [
            'access' => Controller\Plugin\AccessPlugin::class,
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'find' => 'Album\View\Helper\FindHelper',
            'lowercase' => 'Album\View\Helper\LowercaseHelper',
        ],
        /*'factories' => [
            'another' => function($helper_plugin_manager) {
                $helper = new View\Helper\AnotherHelper;
                return $helper;
            },
        ],*/
        'factories' => [
            View\Helper\AnotherHelper::class => InvokableFactory::class,
        ],
        'aliases' => [
            'another' => View\Helper\AnotherHelper::class,
        ]
    ],
];
