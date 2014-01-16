<?php

/*
 * Copyright (c) 2013 Stefano Torresi (http://stefanotorresi.it)
 * See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace MyPopup;

use Zend\Http\Header\SetCookie;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface
{
    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $app = $event->getApplication();
        $eventManager = $app->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'initPopup'), -100);
        $eventManager->attach(MvcEvent::EVENT_FINISH, array($this, 'showPopup'), -9999);
    }

    /**
     * @param MvcEvent $event
     */
    public function initPopup(MvcEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (! $request instanceof HttpRequest || ! $response instanceof HttpResponse) {
            return;
        }

        $serviceManager = $event->getApplication()->getServiceManager();
        $config         = $serviceManager->get('config')[__NAMESPACE__];
        $cookie         = $request->getCookie();

        if ($enable = !isset($cookie->disablePopup)) {
            $cookie = new SetCookie('disablePopup', true, time() + $config['timeout']);
            $response->getHeaders()->addHeader($cookie);
        }

        $event->setParam('enablePopup', $enable);
    }

    public function showPopup(MvcEvent $event)
    {
        if (! $event->getParam('enablePopup')) {
            return;
        }

        $serviceManager = $event->getApplication()->getServiceManager();
        $renderer       = $serviceManager->get('ViewRenderer');
        $config         = $serviceManager->get('config')[__NAMESPACE__];
        $response       = $event->getResponse();

        $popup = $renderer->render($config['template']);

        $injected = preg_replace('/<\/body>/i', $popup . "\n</body>", $response->getContent(), 1);
        $response->setContent($injected);
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/../../autoload_classmap.php',
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__  => __DIR__
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            __NAMESPACE__ => [
                'timeout' => 60 * 60 * 24 * 7,
                'template' => 'my-popup/popup'
            ],
        ];
    }
}
