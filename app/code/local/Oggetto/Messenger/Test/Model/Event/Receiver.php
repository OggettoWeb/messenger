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
 * Update events receiver test case
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Event_Receiver
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test receives messages from transport
     *
     * @return void
     */
    public function testReceivesMessagesFromTransport()
    {
        $message = $this->getModelMock('messenger/message_interface', null, true);

        $callback = $this->getMock('stdClass', array('callbackMethod'));
        $callback->expects($this->once())->method('callbackMethod')
            ->with($this->equalTo($message));

        $transport = $this->getModelMock('messenger/transport_interface', array('startReceiving'), true);
        $transport->expects($this->any())->method('startReceiving')
            ->with($this->equalTo($message), $this->equalTo(array($callback, 'callbackMethod')))
            ->will($this->returnCallback(
                function () use ($callback, $message) {
                    return $callback->callbackMethod($message);
                }
            ));

        $receiver = Mage::getModel('messenger/event_receiver');
        $receiver
            ->setMessagePrototype($message)
            ->setTransport($transport)
            ->receive(array($callback, 'callbackMethod'));
    }

    /**
     * Test observes message event
     *
     * @return void
     */
    public function testObservesMessageEvent()
    {
        $event = Mage::getModel('messenger/event');
        $message = $this->getModelMock('messenger/message_interface', null, true);

        $receiver = $this->getModelMock('messenger/event_receiver', array('buildEvent'));
        $receiver->expects($this->any())->method('buildEvent')
            ->with($this->equalTo($message))
            ->will($this->returnValue($event));

        $observer = $this->getModelMock('messenger/event_observer', array('observe', 'initFromConfig'));
        $observer->expects($this->once())->method('initFromConfig')
            ->with($this->isInstanceOf('Mage_Core_Model_Config'))
            ->will($this->returnSelf());
        $observer->expects($this->once())->method('observe')->with($this->equalTo($event));
        $this->replaceByMock('model', 'messenger/event_observer', $observer);

        $receiver->processMessage($message);
    }

    /**
     * Test builds event from message
     *
     * @return void
     */
    public function testBuildsEventFromMessage()
    {
        $data = array('foo' => 'bar');
        $message = $this->getModelMock('messenger/message_interface', array('getMeta', 'getData'), true);
        $message->expects($this->any())->method('getMeta')->will($this->returnValue(array('name' => 'some_update')));
        $message->expects($this->any())->method('getData')->will($this->returnValue($data));

        $event = Mage::getModel('messenger/event_receiver')->buildEvent($message);
        $this->assertEquals('some_update', $event->getName());
        $this->assertEquals($data, $event->getData());
    }
}