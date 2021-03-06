<?php
namespace Xmlps\Event\Handler;

/**
 * Processes controller ACLs. Registers controller ACL plugin and handles ACL
 * errors.
 *
 * Register callback during bootstap:
 *
 * public function onBootstrap(MvcEvent $e)
 * {
 *     $eventManager->attach(MvcEvent::EVENT_DISPATCH, function($e) {
 *         AclDispatch::processAcls($e);
 *     }, 100);
 * }
 */
class AclDispatchHandler
{
   public static function processAcls(&$e)
   {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();

        // Check if the user is already authenticated
        $authenticated = $sm->get('ControllerPluginManager')
            ->get('ControllerAcl')
            ->authorize($e);
        if ($authenticated) return;

        // Check if the user submitted email and password parameters and try to
        // authenticate using those (API authentication)
        $request = $e->getRequest();
        if ($request->isPost()) {
            $email = $request->getPost('email');
            $access_token = $request->getPost('access_token');
        }
        else {
            $email = $request->getQuery('email');
            $access_token = $request->getQuery('access_token');
        }

        if (!empty($email) and !empty($access_token)) {
            $authService = $sm->get(
                'Zend\Authentication\AuthenticationService'
            );
            $adapter = $authService->getAdapter();
            $adapter->setIdentityValue($email);
            $adapter->setCredentialValue($access_token);
            $authResult = $authService->authenticate();

            if ($authResult->isValid()) {
                $sm->get('Logger')->debugTranslate('user.authentication.sucessfulLoginLog', $email);
                return;
            }
        }

        // Display an error message and return a 403
        $view = $e->getViewModel();
        $translator = $sm->get('Translator');
        $view->setVariable('messages', array('error' => array(
            $translator->translate(
                'application.acl.notAuthorized'
            )
        )));
        $e->getResponse()->setStatusCode(403);
        $e->stopPropagation();
    }
}
