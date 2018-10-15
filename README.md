# Learning Zend Framework 3

## Tutorial
    https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/toc.html

## Installation
- Get the skeleton
    ```sh
    composer create-project -sdev zendframework/skeleton-application <target_dir>
    ```

## Add new module
- zend/composer.json
```php
<?php
"autoload": {
    "psr-4": {
        // ...
        "User\\": "module/User/src/"
    }
},
```

```bash
composer update
```
- zend/config/modules.config.php

```php
<?php
return [
    // ...
    User,
]
```

- Create Module-Directory under zend/module/

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
                'invokables' => [
                    // ...
                    ],
                'factories' => [
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

## View Helpers
- Helper functions to be called inside the View
- Can be implemented as an invokable (each invokable-helper has only one function) or as a factory (can have multiple functions)
#### Invokable
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
#### Factory
- src/View/Helper/Breadcrumbs.php
    ```php
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
    <?php
    $this->pageBreadcrumbs()->setItems([
        'Home' => $this->url('albumhome'),
        'Add' => $this->url('album', ['action' => 'add']),
    ]);
    ?>

    <!-- Breadcrumbs -->
    <?= $this->pageBreadcrumbs()->render(); ?>
    ```

## Views

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
    - For a single action
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
    - For all actions
        ```php
        use Zend\Mvc\MvcEvent;
        ```
        ```php
        public function onDispatch(MvcEvent $e)
          {
            $response = parent::onDispatch($e);        

            // Set alternative layout
            $this->layout()->setTemplate('album/layout');                

            // Return the response
            return $response;
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

#### Partial views
- In a controller
    ```php
    public function partialAction()
    {
        $products = [
            [
              'id' => 1,
              'name' => 'Digital Camera',
              'price' => 99.95,
            ],
            [
              'id' => 2,
              'name' => 'Tripod',
              'price' => 29.95,
            ]
          ];

          $view_model = new ViewModel([
              'products' => $products,
          ]);

          $view_model->setTemplate('album/partial/partial');

          return $view_model;
    }
    ```
- view/album/partial/partial.phtml
    ```php
    <table class="table table-striped table-hover">
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Price</th>
        </tr>

        <?php
            foreach ($this->products as $product) {
                echo $this->partial('album/partial/row', ['product' => $product]);
            }
        ?>
    </table>
    ```
- view/album/partial/row.phtml
    ```php
    <tr>
        <td> <?= $this->product['id'] ?> </td>
        <td> <?= $this->product['name'] ?> </td>
        <td> <?= $this->product['price'] ?> </td>
    </tr>
    ```

#### Base-Layout
- Used as the base for all other views
- module.config.php
    ```php
    'view_manager' => [
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
    ```
- view/layout/layout.phtml
    ```php
    <html lang="de">
        <head>
            <meta charset="utf-8">
        </head>
        <body>
            <?= $this->content ?>
        </body>
    </html>
    ```
- The part "<?= $this->content ?>" displays the content of the respective view being rendered

#### Adding JavaScript
- Either
    ```php
    <?php
    $this->headScript()->appendFile('/js/yourscript.js', 'text/javascript');
    ?>
    ```
- or
    ```html
    <script type="text/javascript" src="/js/yourscript.js"></script>
    ```

#### Adding CSS
- Either
    ```php
    <?php
    $this->headLink()->appendStylesheet('/css/style.css');
    ?>
    ```
- or
    ```html
    <link rel="stylesheet" type="text/css" href="/css/style.css">
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


## Forms
- Define a controller-action that will render the form on GET and process data on POST
    ```php
    <?php
    public function formAction()
    {
        $form = new ExampleForm();

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {
                // Get filtered and validated data
                $data = $form->getData();
                $title = $data['title'];

                // Redirect to "Thank You" page
                return $this->redirect()->toRoute('album');
            }
        }

        // Pass form variable to view
        return new ViewModel([
            'form' => $form
        ]);
    }
    ?>
    ```
- src/Form/ExampleForm.php
- The name of the input-filter must match the name of the corresponding element
    ```php
    <?php
    namespace Album\Form;

    use Zend\Form\Form;
    use Zend\InputFilter\InputFilter;

    class ExampleForm extends Form
    {
        public function __construct() {
            parent::__construct('example');

            $this->addElements();
            $this->addInputFilter();
        }

        private function addElements()
        {
            $this->add([
                'name' => 'title',
                'type' => 'text',
                'options' => [
                    'label' => 'Title',
                ],
            ]);

            $this->add([
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'value' => 'Go',
                    'id' => 'submitbutton',
                ],
            ]);
        }

        private function addInputFilter()
        {
            $inputFilter = new InputFilter();
            $this->setInputFilter($inputFilter);

            $inputFilter->add([
                'name'     => 'title',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 6
                        ],
                    ],
                ],
            ]);
        }
    }
    ```
- view/album/album/form.phtml
- Validator-errors are shown via $this->formElementErrors()->render()
    ```php
    <h1><?= $this->escapeHtml($title) ?></h1>
    <?php
    // This provides a default CSS class and placeholder text for the title element:
    $title = $form->get('title');
    $title->setAttribute('class', 'form-control');
    $title->setAttribute('placeholder', 'Title');

    // This provides CSS classes for the submit button:
    $submit = $form->get('submit');
    $submit->setAttribute('class', 'btn btn-primary');

    $form->setAttribute('action', $this->url('album', ['action' => 'form']));
    $form->prepare();

    echo $this->form()->openTag($form);
    ?>
    <?php // Wrap the elements in divs marked as form groups, and render the
          // label, element, and errors separately within ?>
    <div class="form-group">
        <?= $this->formLabel($title) ?>
        <?= $this->formElement($title) ?>
        <?= $this->formElementErrors()->render($title, ['class' => 'help-block']) ?>
    </div>

    <?php
    echo $this->formSubmit($submit);
    echo $this->form()->closeTag();
    ```
## Uploads
- src/Form/UploadForm.php
    ```php
    <?php
    class UploadForm extends Form
    {
        public function __construct() {
            parent::__construct('upload');

            // Set method
            $this->setAttribute('method', 'post');
            // Set form-type
            $this->setAttribute('enctype', 'multipart/form-data');

            // ...
        }
    }
    ```
- Add file-input
    ```php
    <?php
    private function addElements()
    {
        $this->add([
            'type'  => 'file',
            'name' => 'file',
            'attributes' => [
                'id' => 'file'
            ],
            'options' => [
                'label' => 'Upload file',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Upload',
                'id' => 'submitbutton',
            ],
        ]);
    }
    ```
- Add validators and filters
- The filter being used moves the uploaded file to [web-root]/data/upload/
- The validator checks if it is a POST-Upload as well as for the MIME-Type
    ```php
    <?php
    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'file',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'FileRenameUpload',
                    'options' => [
                        'target' => './data/upload',
                        'useUploadName' => true,
                        'useUploadExtension' => true,
                        'overwrite' => true,
                        'randomize' => false
                    ]
                ]
            ],
            'validators' => [
                ['name' => 'FileUploadFile'],
                [
                    'name' => 'FileMimeType',
                    'options' => [
                        'mimeType'  => ['image/jpeg', 'image/png']
                    ]
                ]
            ],
        ]);
    }
    ```

## Captchas
- Install necessary packages
    ```sh
    composer require zendframework/zend-captcha
    composer require zendframework/zend-text
    ```
- src/Form/UploadForm.php
    ```php
    <?php
    $this->add([
        'type'  => 'captcha',
        'name' => 'captcha',
        'attributes' => [
        ],
        'options' => [
            'label' => 'Human check',
            'captcha' => [
                'class' => 'Figlet',
                'wordLen' => 6,
                'expiration' => 600,
            ],
        ],
    ]);
    ```
- view/album/image/upload.phtml
    ```html
    <div class="form-group">
      <?= $this->formLabel($form->get('captcha')); ?>
      <?= $this->formElement($form->get('captcha')); ?>
      <?= $this->formElementErrors($form->get('captcha')); ?>
      <p class="help-block">Enter the letters above as you see them.</p>
    </div>
    ```

## Sessions
```bash
composer require zendframework/zend-session
```

- `[app]/config/modules.config.php`
```php
return [
	'Zend\Session',
	// ...
]
```

- [app]/config/autoload/global.php
    ```php
    <?php
    use Zend\Session\Storage\SessionArrayStorage;
    use Zend\Session\Validator\RemoteAddr;
    use Zend\Session\Validator\HttpUserAgent;

    return [
        // Session configuration.
        'session_config' => [
            // Session cookie will expire in 1 hour.
            'cookie_lifetime' => 60*60*1,
            // Session data will be stored on server maximum for 30 days.
            'gc_maxlifetime'     => 60*60*24*30,
        ],
        // Session manager configuration.
        'session_manager' => [
            // Session validators (used for security).
            'validators' => [
                RemoteAddr::class,
                HttpUserAgent::class,
            ]
        ],
        // Session storage configuration.
        'session_storage' => [
            'type' => SessionArrayStorage::class
        ],
    ];
    ```
- config/module.config.php
    ```php
    <?php
    return [
        // ...
        'session_containers' => [
            'ContainerNamespace'
        ],
    ]
    ```
- src/Controller/AlbumControllerFactory.php
    ```php
    <?php
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $session_container = $container->get('ContainerNamespace');

        return new AlbumController($session_container);
    }
    ```
- src/Controller/AlbumController.php
	```php
	<?php
	private $session_container;
	```

	```php
	<?php
	public function __construct($session_container) {
		$this->session_container = $session_container;
	}
	```

    ```php
    <?php
    use Zend\Session\Container;
    // ...
    public function sessionAction()
    {
        if (isset($this->session_container->counter)) {
            $this->session_container->counter += 1;
        }
        else {
            $this->session_container->counter = 0;
        }

        return new ViewModel([
            'counter' => $this->session_container->counter,
        ]);
    }
    ```
## Database-Management with Doctrine
#### Setup
- Install doctrine
    ```sh
    composer require doctrine/doctrine-orm-module
    ```
- Add to zend/config/modules.config.php
    ```php
    <?php
    return [
        // ...
        'DoctrineModule',
        'DoctrineORMModule'
    ]
    ```

#### Set database credentials
- zend/config/autoload/local.php
    ```php
    <?php
    use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySqlDriver;

    return [
        // Database connection configuration.
        'doctrine' => [
            'connection' => [
                'orm_default' => [
                    'driverClass' => PDOMySqlDriver::class,
                    'params' => [
                        'host'     => '127.0.0.1',
                        'user'     => 'root',
                        'password' => 'root',
                        'dbname'   => 'zend',
                    ]
                ],
            ],
        ],
    ];
    ```

## Authentication
- Prerequisites
For Authentication to work, we need to have Sessions in place first!
```bash
sudo apt install php-mbstring
```

```bash
composer require zendframework/zend-authentication
```

```bash
composer require zendframework/zend-math
```

```bash
composer require zendframework/zend-crypt
```

`module.config.php`
Add routes
```php
'login' => [
	'type' => Literal::class,
	'options' => [
		'route'    => '/login',
		'defaults' => [
			'controller' => Controller\AuthController::class,
			'action'     => 'login',
		],
	],
],
'logout' => [
	'type' => Literal::class,
	'options' => [
		'route'    => '/logout',
		'defaults' => [
			'controller' => Controller\AuthController::class,
			'action'     => 'logout',
		],
	],
],
```
It's better to have them Literal than Segment (auth[/:action]) so we can refer to them easily when redirecting to login i.e.

Link controller to factory
```php
'controllers' => [
	'factories' => [
		Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
	],
],
```

Add Service Managers
```php
'service_manager' => [
	'factories' => [
		\Zend\Authentication\AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
		Service\AuthAdapter::class => Service\Factory\AuthAdapterFactory::class,
		Service\AuthManager::class => Service\Factory\AuthManagerFactory::class,
	],
],
```

- Other files required:
    - Controller/AuthController.php
    - Controller/Factory/AuthControllerFactory.php
    - Service/AuthAdapter.php
    - Service/AuthManager.php
    - Service/Factory/AuthAdapterFactory.php
    - Service/Factory/AuthManagerFactory.php
    - view/application/auth/login.phtml

#### General
- The src/Service/AuthenticationServiceFactory.php apparently overrides the default AuthenticationService to set the src/Service/AuthAdapter.php as the adapter
- The src/Service/Factory/AuthManagerFactory.php passes an AuthenticationService-Instance to the AuthManager
- That way the AuthManager can access the AuthAdapter

#### Logging in
- On POST the loginAction() in the AuthController calls the src/Service/AuthManager.php -> login()
- This in turn calls the src/Service/AuthAdapter.php -> authenticate()
- The authenticate() queries the database for the email, and if found,  verifies the password

#### Access filter
We could inject the AuthService into every Controller to check for logged in users, but that's very cumbersome. So instead we're going to use access filters.

- check `onBootstrap()` and `onDispatch()` in Module.php

- config/module.config.php
    ```php
    <?php
    return [
        // ...
        'access_filter' => [
            'controllers' => [
                Controller\UserController::class => [
                    // Give access to "resetPassword", "message" and "setPassword" actions
                    // to anyone.
                    ['actions' => ['resetPassword', 'message', 'setPassword'], 'allow' => '*'],
                    // Give access to "index", "add", "edit", "view", "changePassword" actions to authorized users only.
                    ['actions' => ['index', 'add', 'edit', 'view', 'changePassword'], 'allow' => '@']
                ],
            ]
        ],
    ]
    ```
- Procedure
    - `onBootstrap()` calls `onDispatch()`
	- `onDispatch()` extracts controller-name and action-name and calls `src/Service/AuthManager.php` -> `filterAccess()`
    - `filterAccess()` loops over all the entries of the given controller and looks for the action-name (or an "*" matching all pages) and the corresponding "allow"-value
    - In "restrictive"-mode any action that is not in there is considered to require authentication (locked down unless stated otherwise)
    - In "permissive"-mode an action is open unless stated otherwise
	- if `filterAccess()` returns false, `onDispatch()` redirects to 'login' (and includes the URL the user was denied access to as 'redirectUrl', so we can redirect to it after successful login)

