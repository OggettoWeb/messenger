<?php
require_once Mage::getBaseDir() . DS . 'vendor' . DS . 'autoload.php';
use PhpAmqpLib\Message\AMQPMessage;
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
 * RabbitMQ transport callback mock
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Transport_Rabbitmq_CallbackMock
{
    /**
     * Stub for callback method
     *
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     * @return void
     */
    public function callbackMethod(Oggetto_Messenger_Model_Message_Interface $message)
    {
    }
}

/**
 * RabbitMQ transport test case
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Transport_Rabbitmq extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Get AMQP channel mock
     *
     * @return AMQPChannel
     */
    protected function _getChannelMock()
    {
        return $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->setMethods(array(
                'queue_declare', 'basic_qos', 'basic_consume',
                'basic_ack', 'basic_publish', 'basic_cancel'
            ))
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * Start messages receiving
     *
     * @param Oggetto_Messenger_Model_Transport_Rabbitmq $transport Transport
     * @return void
     */
    protected function _startReceiving($transport)
    {
        $transport->startReceiving(
            $this->getModelMock('messenger/message_interface', null, true),
            function () {
            }
        );
    }

    /**
     * Test consumes update queues
     *
     * @return void
     */
    public function testConsumesUpdateQueues()
    {
        $rabbit = $this->getModelMock('messenger/transport_rabbitmq', array('_getChannel', '_close'));
        $channel = $this->_getChannelMock();
        $rabbit->expects($this->any())->method('_getChannel')->will($this->returnValue($channel));
        $rabbit->setConsumeQueues(array('test'));

        $channel->expects($this->once())->method('queue_declare')->with($this->equalTo('test'));
        $channel->expects($this->once())->method('basic_consume')->with($this->equalTo('test'));
        $this->_startReceiving($rabbit);
    }

    /**
     * Test defines prefetch count
     *
     * @return void
     */
    public function testDefinesPrefetchCount()
    {
        $rabbit = $this->getModelMock('messenger/transport_rabbitmq', array('_getChannel', '_close'));
        $channel = $this->_getChannelMock();
        $channel->expects($this->once())->method('basic_qos')
            ->with($this->equalTo(null), $this->equalTo(1), $this->equalTo(null));
        $rabbit->expects($this->any())->method('_getChannel')->will($this->returnValue($channel));
        $this->_startReceiving($rabbit);
    }

    /**
     * Test executes callback with received message
     *
     * @return void
     */
    public function testExecutesCallbackWithReceivedMessage()
    {
        $channel = $this->_getChannelMock();
        $channel->expects($this->once())->method('basic_ack')
            ->with($this->equalTo('delivery_tag_value'));

        $rabbitMessage = new AMQPMessage('message body');
        $rabbitMessage->{'delivery_info'}['channel'] = $channel;
        $rabbitMessage->{'delivery_info'}['delivery_tag'] = 'delivery_tag_value';

        $prototype = $this->getModelMock('messenger/message_interface', array('init'), true);
        $prototype->expects($this->once())->method('init')->with($this->equalTo('message body'));

        $callback = $this->getMock(
            'Oggetto_Messenger_Test_Model_Transport_Rabbitmq_CallbackMock', array('callbackMethod')
        );
        $callback->expects($this->once())->method('callbackMethod')->with($this->isInstanceOf($prototype));

        Mage::getModel('messenger/transport_rabbitmq')
            ->setLogger(Zend_Log::factory(array(new Zend_Log_Writer_Null)))
            ->receiveMessage($rabbitMessage, $prototype, array($callback, 'callbackMethod'));
    }

    /**
     * Test logs message processing errors
     *
     * @return void
     */
    public function testLogsMessageProcessingErrors()
    {
        $channel = $this->_getChannelMock();
        $rabbitMessage = new AMQPMessage('message body');
        $rabbitMessage->{'delivery_info'}['channel'] = $channel;
        $rabbitMessage->{'delivery_info'}['delivery_tag'] = null;

        $prototype = $this->getModelMock('messenger/message_interface', null, true);

        $exception = new Exception('Error!');
        $callback = $this->getMock(
            'Oggetto_Messenger_Test_Model_Transport_Rabbitmq_CallbackMock', array('callbackMethod')
        );
        $callback->expects($this->any())->method('callbackMethod')
            ->will($this->throwException($exception));

        $log = $this->getMock('Zend_Log', array('log'));
        $log->expects($this->at(1))->method('log')->with($this->equalTo($exception), $this->equalTo(Zend_Log::ERR));

        Mage::getModel('messenger/transport_rabbitmq')
            ->setLogger($log)
            ->receiveMessage($rabbitMessage, $prototype, array($callback, 'callbackMethod'));
    }

    /**
     * Test sends message to routed queue
     *
     * @return void
     */
    public function testPublishesMessageToRoutedQueue()
    {
        $message = $this->getModelMock('messenger/message_interface', null, true);
        $router = $this->getModelMock('messenger/transport_rabbitmq_router', array('findMessageQueue'));
        $router->expects($this->any())->method('findMessageQueue')
            ->with($this->equalTo($message))
            ->will($this->returnValue('queue_name'));

        $channel = $this->_getChannelMock();
        $channel->expects($this->once())->method('queue_declare')
            ->with($this->equalTo('queue_name'));
        $channel->expects($this->once())->method('basic_publish')
            ->with(
                $this->isInstanceOf('PhpAmqpLib\Message\AMQPMessage'),
                $this->anything(),
                $this->equalTo('queue_name')
            );

        $rabbit = $this->getModelMock('messenger/transport_rabbitmq', array('_getChannel', '_close'));
        $rabbit->expects($this->any())->method('_getChannel')->will($this->returnValue($channel));

        $rabbit->setPublishRouter($router)
            ->setLogger(Zend_Log::factory(array(new Zend_Log_Writer_Null)))
            ->send($message);
    }
}