<?php
namespace Manager;

use Zend\Mvc\MvcEvent;

use Manager\Form\UploadForm;
use Manager\Form\UploadFormInputFilter;

class Module
{
    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Get service config
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Manager\Form\UploadForm' => function($sm)
                {
                    $translator = $sm->get('translator');
                    return new UploadForm($translator);
                },
                'Manager\Form\UploadFormInputFilter' => function($sm)
                {
                    return new UploadFormInputFilter();
                },
            ),
        );
    }

    /**
     * Get controller config
     *
     * @return array
     */
    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'Manager\Controller\Manager' => function($cm)
                {
                    $sm = $cm->getServiceLocator();
                    $logger = $sm->get('Logger');
                    $translator = $sm->get('Translator');
                    $uploadForm = $sm->get('Manager\Form\UploadForm');
                    $uploadFormInputFilter = $sm->get('Manager\Form\UploadFormInputFilter');

                    return new Controller\ManagerController(
                        $logger,
                        $translator,
                        $uploadForm,
                        $uploadFormInputFilter
                    );
                }
            )
        );
    }
}
