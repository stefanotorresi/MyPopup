<?php

/**
 * Copyright (c) 2013 Stefano Torresi (http://stefanotorresi.it)
 * See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace MyPopup;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Layout;

class ViewHelper extends AbstractHelper
{
    public function __invoke($template = null)
    {
        $view = $this->getView();

        if ( ! method_exists($view, 'plugin')) {
            return;
        }

        /** @var Layout $layoutHelper */
        $layoutHelper = $view->plugin('layout');

        if ( ! $layoutHelper()->showPopup) {
            return;
        }

        return $view->render($template);
    }
}
