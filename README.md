# Learning Zend Framework 3

## Tutorial
    https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/toc.html

## Installation
+ Get the skeleton
    ```
    composer create-project -sdev zendframework/skeleton-application <target_dir>
    ```

## Plugins
+ src/Controller/Plugin/AccessPlugin.php

    hat eine checkAccess() (call it whatever you want^^)

+ module.config.php

    ```
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\AccessPlugin::class => InvokableFactory::class,
        ],
        'aliases' => [
            'access' => Controller\Plugin\AccessPlugin::class,
        ]
    ],

    ```

+ IndexController.php

    ```
    $access=> $this->access()->checkAccess('index')
    ```

## Controller Factories (Dependency Injection)
+ src/Controller/Factory/IndexControllerFactory.php

    hat eine invoke(), die allerhand tun kann, aber letztendlich den Controller via return new IndexController($param1, $param2) aufruft

+ module.config.php

    ```
    'controllers' => 'factories' =>
        Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,

    ```

+ IndexController.php

    ```
    construct($param1, $param2)
    ```

+ Der Teil, der in der IndexControllerFactory.php invoke() passiert, kann auch in Module.php getControllerConfig() abgewickelt werden

    ```
    return [
        'factories' => [
            Controller\AlbumController::class => function($container) {
                    return new Controller\AlbumController(
                    $container->get(Model\AlbumTable::class),
                    9999
                    );
            },
        ]
    ];
    ```

## View Helper

+ src/View/Helper/FindHelper.php

    ```
    use Zend\View\Helper\AbstractHelper;
    ```
    ```
    extends AbstractHelper

    ```

+ Kann entweder als Invokable gestaltet werden (dann gibt es nur eine Funktion "\_\_invoke()"), oder als Factory, dann kann ein Helper mehrere Funktionen haben

+ Registriert wird entweder über config/module.config.php oder die Module.php

+ Registrieren via module.config.php:

    ```
    return [
        // …

        'view_helpers' => [
            'invokables' => [
                'find' => 'Album\View\Helper\FindHelper',
                'lowercase' => 'Album\View\Helper\LowercaseHelper',
            ],
            'factories' => [
                'another' => function($helper_plugin_manager) {
                        $helper = new View\Helper\AnotherHelper;
                        return $helper;
                },
            ],
        ],
    ];

    ```
    Alternativ auch:
    ```
    'view_helpers' => [
        'factories' => [
            View\Helper\AnotherHelper::class => InvokableFactory::class,
        ],
        'aliases' => [
            'another' => View\Helper\AnotherHelper::class,
        ]
    ]

    ```

+ Registrieren via Module.php:

    ```
    use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
    ```

    ```
    implements ViewHelperProviderInterface
    ```

    ```
    public function getViewHelperConfig() {
        return [
            'invokables’ => [
                // ...
                ],
            ‘factories’ => [
                // ...
            ]
        ]
    }

    ```

+ Zugriff im View (index.phtml) für eine \_\_invoke($str, $find):

    ```
    <?= $this->find("me", "e") ?>
    ```

+ Zugriff für eine Factory:
    ```
    <?= $this->factoryname()->functionname($param) ?>
    ```
