<?php

namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Form\UploadForm;
use Album\Model\Album;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;

class ImageController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function uploadAction()
    {
        // Create the form model.
        $form = new UploadForm();

        // Check if user has submitted the form.
        if ($this->getRequest()->isPost()) {
            // Make certain to merge the files info!
            $request = $this->getRequest();
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // Pass data to form.
            $form->setData($data);

            // Execute file validators.
            if ($form->isValid()) {

                // Execute file filters.
                $data = $form->getData();

                // Redirect the user to another page.
                return $this->redirect()->toRoute('image', ['action' => 'index']);
            }
        }

        // Render the page.
        return new ViewModel([
                 'form' => $form
            ]);
    }
}
