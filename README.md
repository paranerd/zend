# Learning Zend Framework 3

## Tutorial
    https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/toc.html

## Installation
- Get the skeleton
    ```sh
    composer create-project -sdev zendframework/skeleton-application <target_dir>
    ```

## Module.php vs module.config.php
- Same functionality, different notation

#### Examples

- Controller configuration:

    - Module.php
    ```php
    class Module implements ConfigProviderInterface

    // ...

    public function getControllerConfig()
    {
        return [
            'factories' => [
                // ...
            ]
        ];
    }
    ```

    - module.config.php
    ```php
    return [
        'controllers' => [
            'factories' => [
                // ...
            ],
        ],
    ]
    ```

- View helpers:

    - Module.php
    ```php
    class Module implements ViewHelperProviderInterface

    // ...

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

    - module.config.php
    ```php
    return [
        // …

        'view_helpers' => [
            'invokables' => [
                // ...
            ],
            'factories' => [
                // ...
            ],
        ],
    ];
    ```

## Plugins
+ src/Controller/Plugin/AccessPlugin.php

    hat eine checkAccess() (call it whatever you want^^)

+ module.config.php

    ```php
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

    ```php
    $access=> $this->access()->checkAccess('index')
    ```

## Controller Factories (Dependency Injection)
+ src/Controller/Factory/IndexControllerFactory.php

    hat eine invoke(), die allerhand tun kann, aber letztendlich den Controller via return new IndexController($param1, $param2) aufruft

+ module.config.php

    ```php
    'controllers' => 'factories' =>
        Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,

    ```

+ IndexController.php

    ```php
    construct($param1, $param2)
    ```

+ Der Teil, der in der IndexControllerFactory.php invoke() passiert, kann auch in Module.php getControllerConfig() abgewickelt werden

    ```php
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

- Helper functions to be called inside the View
- Can be implemented as an invokable (each invokable-helper has only one function) or as a factory (can have multiple functions)

- src/View/Helper/LowercaseHelper.php

    ```php
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
    ```

- src/View/Helper/AnotherHelper.php

    ```php
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
    ```

- Registering in module.config.php

    ```php
    return [
        // ...

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

- Alternatively with an alias

    ```php
    'view_helpers' => [
        'factories' => [
            View\Helper\AnotherHelper::class => InvokableFactory::class,
        ],
        'aliases' => [
            'another' => View\Helper\AnotherHelper::class,
        ]
    ]

    ```

- Accessing in the view

    ```php
    <?= $this->lowercase("lOwErCaSeMe") ?>
    ```

    ```php
    <?= $this->another()->lowercase("lOwErCaSeMe") ?>
    ```
