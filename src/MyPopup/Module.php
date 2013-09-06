<?php

/*
 * Copyright (c) 2013 Stefano Torresi (http://stefanotorresi.it)
 * See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace MyPopup;

use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\ViewHelperProviderInterface
{
    /**
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'initPopup'), -100);
    }

    /**
     * @param MvcEvent $e
     */
    public function initPopup(MvcEvent $e)
    {
        $request = $e->getRequest();

        if ( ! $request instanceof HttpRequest) {
            return;
        }

        $cookie = $request->getCookie();

        $serviceManager = $e->getApplication()->getServiceManager();

        $config = $serviceManager->get('config');
        $timeout = $config[__NAMESPACE__]['timeout'];

        if ($enable = !isset($cookie->disablePopup)) {
            setcookie('disablePopup', true, time() + $timeout);
        }

        $layout = $e->getViewModel();
        $layout->showPopup = isset($layout->showPopup) ? $layout->showPopup && $enable : $enable;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__  => __DIR__
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return array(
            __NAMESPACE__ => array(
                'timeout' => 60 * 60 * 24 * 7
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                 __NAMESPACE__ . '\ViewHelper' => __NAMESPACE__ . '\ViewHelper',
            ),
            'aliases' => array(
                'myPopup' => __NAMESPACE__ . '\ViewHelper',
            ),
        );
    }
}
