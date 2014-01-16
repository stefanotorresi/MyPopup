<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace MyPopupTest;

use MyPopup\Module;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Console\Request as ConsoleRequest;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

class ModuleTest extends TestCase
{
    /**
     * @var Module
     */
    protected $module;

    /**
     * @var MvcEvent
     */
    protected $event;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();

        /** @var ModuleManager $moduleManager */
        $moduleManager = $serviceManager->get('ModuleManager');

        /** @var Application $application */
        $application = Bootstrap::getServiceManager()->get('Application');
        $event = new MvcEvent();
        $event->setApplication($application);

        $this->event = $event;
        $this->module = $moduleManager->getModule('MyPopup');
        $this->module->onBootstrap($event);
    }

    public function testModuleLoad()
    {
        $this->assertInstanceOf('MyPopup\Module', $this->module);
    }

    public function testInitPopupListener()
    {
        $event = $this->event;
        $request = new HttpRequest();
        $response = new HttpResponse();
        $event->setRequest($request);
        $event->setResponse($response);
        $event->setName(MvcEvent::EVENT_DISPATCH);
        $event->getApplication()->getEventManager()->trigger($event);

        $this->assertArrayHasKey('Set-Cookie', $response->getHeaders()->toArray());

        /** @var SetCookie $setCookieHeader */
        $setCookieHeader = null;
        foreach($response->getHeaders()->get('Set-Cookie') as $header) {
            if ($header->getName() == 'disablePopup') {
                $setCookieHeader = $header;
                break;
            }
        }

        $this->assertNotNull($setCookieHeader);
        $this->assertTrue($setCookieHeader->getValue());

        $timeoutSetting = $event->getApplication()->getServiceManager()->get('config')['MyPopup']['timeout'];
        $this->assertLessThanOrEqual(time() + $timeoutSetting, $setCookieHeader->getExpires(true));
        $this->assertTrue($event->getParam('enablePopup'));
    }

    public function testInitPopupListenerBailsoutWithNonHttpRequest()
    {
        $module = $this->module;
        $event = $this->event;
        $request = new ConsoleRequest();
        $event->setRequest($request);

        $response = $this->getMock('Zend\Http\Response');
        $response->expects($this->never())->method('getHeaders');
        $event->setResponse($response);

        $module->initPopup($event);
        $this->assertNull($event->getParam('enablePopup'));

    }

    public function testInitPopupListenerBailsOutWithNonHttpResponse()
    {
        $module = $this->module;
        $event = $this->event;
        $request = new HttpRequest();
        $event->setRequest($request);

        $response = $this->getMock('Zend\Console\Response');
        $response->expects($this->never())->method('getHeaders');
        $event->setResponse($response);

        $module->initPopup($event);
        $this->assertNull($event->getParam('enablePopup'));
    }

    public function testShowPopup()
    {
        $module = $this->module;
        $event = $this->event;
        $response = new HttpResponse();
        $event->setParam('enablePopup', true);
        $event->setResponse($response);
        $response->setContent('<html><body></body></html>');

        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $config = $event->getApplication()->getServiceManager()->get('config')['MyPopup'];
        $renderer->expects($this->atLeastOnce())
                 ->method('render')
                 ->with($config['template'])
                 ->will($this->returnValue('popup test template content'));

        $event->getApplication()->getServiceManager()->setService('ViewRenderer', $renderer);

        $event->setName(MvcEvent::EVENT_FINISH);
        $event->getApplication()->getEventManager()->trigger($event);

        $this->assertContains("popup test template content\n</body>", $response->getContent());
    }

    public function testShowPopupBailsOutWhenPopupIsDisabled()
    {
        $module = $this->module;
        $event = $this->event;
        $event->setParam('enablePopup', false);

        $response = $this->getMock('Zend\Stdlib\ResponseInterface');
        $response->expects($this->never())
                 ->method('setContent');
        $event->setResponse($response);

        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $renderer->expects($this->never())
                 ->method('render');
        $event->getApplication()->getServiceManager()->setService('ViewRenderer', $renderer);

        $module->showPopup($event);
    }
}
