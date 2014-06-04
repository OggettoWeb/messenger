<?php
/**
 * Oggetto Web Messenger extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Oggetto Messenger module to newer versions in the future.
 * If you wish to customize the Oggetto Messenger module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @copyright  Copyright (C) 2012 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Events dispatcher test case
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Event_Dispatcher
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test dispatches built event message to transport
     *
     * @return void
     */
    public function testDispatchesBuiltEventMessageToTransport()
    {
        $messagePrototype = $this->getModelMock('messenger/message_interface', null, true);
        $transport = $this->getModelMock('messenger/transport_interface', array('send'), true);
        $transport->expects($this->once())->method('send')
            ->with($this->isInstanceOf($messagePrototype));

        $event = Mage::getModel('messenger/event');
        $dispatcher = $this->getModelMock('messenger/event_dispatcher', array('buildMessage'));
        $dispatcher->expects($this->once())->method('buildMessage')
            ->with($this->equalTo($event), $this->equalTo($messagePrototype))
            ->will($this->returnValue($messagePrototype));

        $dispatcher
            ->setTransport($transport)
            ->setMessagePrototype($messagePrototype)
            ->dispatch($event);
    }

    /**
     * Test runs dispatch observers
     *
     * @return void
     */
    public function testRunsDispatchObservers()
    {
        $message = $this->getModelMock('messenger/message_interface', null, true);
        $event = Mage::getModel('messenger/event');

        $dispatcher = $this->getModelMock('messenger/event_dispatcher', array('buildMessage'));
        $dispatcher->expects($this->any())->method('buildMessage')->will($this->returnValue($message));

        $observer = $this->getModelMock(
            'messenger/event_dispatchObserver_interface', ['beforeDispatch', 'afterDispatch']
        );
        $observer->expects($this->once())->method('beforeDispatch')
            ->with($this->equalTo($event), $this->equalTo($message));
        $observer->expects($this->once())->method('afterDispatch')
            ->with($this->equalTo($event), $this->equalTo($message));

        $dispatcher
            ->setTransport($this->getModelMock('messenger/transport_interface'))
            ->setMessagePrototype($message)
            ->attachObserver($observer)
            ->dispatch($event);
    }

    /**
     * Test builds message from event
     *
     * @return void
     */
    public function testBuildsMessageFromEvent()
    {
        $dispatcher = Mage::getModel('messenger/event_dispatcher');
        $data = array('foo' => 'bar');
        $event = Mage::getModel('messenger/event')
            ->setName('event_name')
            ->setData($data);

        $message = $this->getModelMock('messenger/message_interface', array('setData', 'setMeta'), true);
        $message->expects($this->once())->method('setMeta')->with($this->equalTo(
            array('name' => 'event_name', '_class' => 'event')));
        $message->expects($this->once())->method('setData')->with($this->equalTo($data));

        $dispatcher->buildMessage($event, $message);
    }
}