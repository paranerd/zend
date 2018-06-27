<?php

namespace Album\Controller;

use Album\Model\AlbumTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Form\AlbumForm;
use Album\Form\ExampleForm;
use Album\Model\Album;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

class AlbumController extends AbstractActionController
{
    private $table;
    private $my_int;
    private $session_container;

    public function __construct(AlbumTable $table, $session_container, $my_int)
    {
        $this->table = $table;
        $this->session_container = $session_container;
        $this->my_int = $my_int;
    }

    public function onDispatch(MvcEvent $e)
    {
        $response = parent::onDispatch($e);

        // Set alternative layout
        //$this->layout()->setTemplate('album/layout');

        // Return the response
        return $response;
    }

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

    public function contactusAction()
    {
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            // Retrieve form data from POST variables
            $data = $this->params()->fromPost();

            // ... Do something with the data ...
            var_dump($data);
        }

        // Pass form variable to view
        return new ViewModel([
              'form' => $form
        ]);
    }

    public function breadcrumbsAction()
    {
        return new ViewModel([]);
    }

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

    public function regexAction()
    {
        $page = $this->params()->fromRoute('page', 'default-page');
        $section = $this->params()->fromRoute('section', 'default-section');

        return new ViewModel([
            'page' => $page,
            'section' => $section
        ]);
    }

    public function routingAction()
    {
        $param1 = $this->params()->fromRoute('param1', 'default-param1');
        $param2 = $this->params()->fromRoute('param2', 'default-param2');

        return new ViewModel([
            'param1' => $param1,
            'param2' => $param2
        ]);
    }

    public function testAction()
    {
        $view_model = new ViewModel([
            'my_int' => $this->my_int,
            'access' => $this->access()->checkAccess('alice')
        ]);

        $view_model->setTemplate('album/my/test');

        return $view_model;
    }

    public function maptestAction()
    {
        return new ViewModel([
            'param' => "my maptest param"
        ]);
    }

    public function noviewAction()
    {
        return $this->getResponse();
    }

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

    public function indexAction()
    {
        return new ViewModel([
            'albums' => $this->table->fetchAll(),
        ]);
    }

    public function addAction()
    {
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return ['form' => $form];
        }

        $album = new Album();
        $form->setInputFilter($album->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return ['form' => $form];
        }

        $album->exchangeArray($form->getData());
        $this->table->saveAlbum($album);

        return $this->redirect()->toRoute('album');
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('album', ['action' => 'add']);
        }

        // Retrieve the album with the specified id. Doing so raises
        // an exception if the album is not found, which should result
        // in redirecting to the landing page.
        try {
            $album = $this->table->getAlbum($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('album', ['action' => 'index']);
        }

        $form = new AlbumForm();
        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($album->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }

        $this->table->saveAlbum($album);

        // Redirect to album list
        return $this->redirect()->toRoute('album', ['action' => 'index']);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->table->deleteAlbum($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }

        return [
            'id'    => $id,
            'album' => $this->table->getAlbum($id),
        ];
    }
}
