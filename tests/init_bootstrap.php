<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

require __DIR__ . '/Test/Bootstrap.php';

MyPopup\Test\Bootstrap::init();
