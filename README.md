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
- Extend the functionality of all controllers
- src/Controller/Plugin/AccessPlugin.php

    ```php
    namespace Album\Controller\Plugin;

    use Zend\Mvc\Controller\Plugin\AbstractPlugin;

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
    ```

- module.config.php

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

- Accessing the plugin inside a controller

    ```php
    $access => $this->access()->checkAccess('alice')
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

## View templates

#### View resolvers
1. Template Path Stack Resolver (default)
    - Assumes, that a template with a given name will be accessible inside the view-folder under [module]/[controller]/[template_name]
2. Template Map Resolver
    - Uses a map in the module.config.php to determine the template-path

#### Set a different view template
- In module.config.php (using the template map resolver)
    ```php
    'view_manager' => [
       //...

       'template_map' => [
           'album/album/test' => __DIR__ . '/../view/album/mapped/index.phtml'
       ],
       'template_path_stack' => [
           __DIR__ . '/../view',
       ],
    ],
    ```
- Or in a controller:
    ```php
    public function indexAction()
    {
        $view_model = new ViewModel([
            'my_param' => "something"
        ]);

        $view_model->setTemplate('album/my/test');

        return $view_model;
    }
    ```

#### Action without a view
- In a controller:
    ```php
    public function noviewAction()
    {
        return $this->getResponse();
    }
    ```

#### Return JSON
- module.config.php
    ```php
    'view_manager' => [
            //...

            'strategies' => [
                'ViewJsonStrategy',
            ],
        ],
    ```

- In a controller:
    ```php
    use Zend\View\Model\JsonModel;

    // ...

    public function jsonAction()
    {
        return new JsonModel([
            'status' => 'SUCCESS',
            'message'=>'Here is your data',
            'data' => [
                'full_name' => 'John Doe',
                'address' => '51 Middle st.'
            ]
        ]);
    }
    ```

#### Custom error pages
- module.config.php
    ```php
    'view_manager' => [    
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'not_found_template'       => 'error/404', // Customize this path
        'exception_template'       => 'error/index', // Customize this path
        'template_map' => [
            'error/404' => __DIR__ . '/../view/error/404.phtml', // Customize this path
            'error/index'=> __DIR__ . '/../view/error/index.phtml', // Customize this path
        ],
    ],
    ```

## Factories (Dependency Injection)
- Are used to instantiate other models (model being one of: Entity, Repository, Value Object, Service, Factory), when there is additional work to be done before instantiation, for example when the model depends on other services

- src/Controller/Factory/AlbumControllerFactory.php
    ```php
    namespace Album\Controller\Factory;

    use Interop\Container\ContainerInterface;
    use Zend\ServiceManager\Factory\FactoryInterface;
    use Album\Controller\AlbumController;
    use Album\Model\AlbumTable;

    class AlbumControllerFactory implements FactoryInterface
    {
        public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
        {
            $this->table = $container->get(AlbumTable::class);
            $my_int = 1234;

            return new AlbumController($table, $my_int);
        }
    }
    ```

- src/Controller/AlbumController.php

    ```php
    // ...
    public function __construct(AlbumTable $table, $my_int)
    {
        $this->table = $table;
        $this->my_int = $my_int;
    }
    ```

- module.config.php

    ```php
    'controllers' => [
        'factories' => [
            Controller\AlbumController::class => Controller\Factory\AlbumControllerFactory::class,
        ],
    ],
    ```

- Alternatively (but not as clean) you could put the factory code directly inside the module.config.php (or Module.php in getControllerConfig()):

    ```php
    return [
        'factories' => [
            Controller\AlbumController::class => function($container) {
                return new Controller\AlbumController(
                    $container->get(Model\AlbumTable::class),
                    1234
                );
            },
        ]
    ];
    ```

## Routing
#### Literal
- Route match is achieved only when there's an exact match
- module.config.php
    ```php
    'about' => [
        'type' => Literal::class,
        'options' => [
            'route' => '/about',
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'about',
            ],
        ],
    ],
    ```

#### Segment
- Covers one or more URLs
- In the following example, [action] maps to a controller-action
    ```php
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
    ```
- Here, there's only one action (because there's no 'action'-parameter in the URL)
    ```php
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
    ```
- Parameters can be accessed in the controller like so:
    ```php
    public function routingAction()
    {
        $param1 = $this->params()->fromRoute('param1', 'default-param1');
        $param2 = $this->params()->fromRoute('param2', 'default-param2');

        return new ViewModel([
            'param1' => $param1,
            'param2' => $param2
        ]);
    }
    ```

#### Regex
- Maps URL to a regular expression
- module.config.php:
    ```php
    use Zend\Router\Http\Regex;
    ```
    ```php
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
    ```
- Accessing parameters is similar to Segment routes:
    ```php
    public function regexAction()
    {
        $page = $this->params()->fromRoute('page', 'default-page');
        $section = $this->params()->fromRoute('section', 'default-section');

        return new ViewModel([
            'page' => $page,
            'section' => $section
        ]);
    }
    ```

#### Building routes in view templates
- In a *.phtml
    ```php
    <a href="<?= $this->url('routingtest', ['param1' => 'param1-from-link', 'param2' => '999'], ['query' => ['q' => 'my-query']]); ?>" >Add Album</a>
    ```
- The first parameter ('routingtest') is the NAME of the route in module.config.php, NOT the first part of the URL!
- In the second parameter, you can pass parameters^^ to the URL
- The 'query' in the third parameter adds key-value-pairs after a '?'
