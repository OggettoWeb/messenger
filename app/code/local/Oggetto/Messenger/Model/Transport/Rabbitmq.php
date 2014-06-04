<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

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
 * @copyright  Copyright (C) 2012 Oggetto Web (http://oggettoweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rabbit MQ transport
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Transport_Rabbitmq
    implements Oggetto_Messenger_Model_Transport_Interface,
    Oggetto_Messenger_Model_Log_Loggable
{
    /**
     * Connection instance
     *
     * @var AMQPConnection
     */
    protected $_connection;

    /**
     * Channel instance
     *
     * @var AMQPChannel
     */
    protected $_channel;

    /**
     * Queues to consume
     *
     * @var array
     */
    protected $_consumeQueues = array();

    /**
     * Publish router
     *
     * @var Oggetto_Messenger_Model_Transport_Rabbitmq_Router
     */
    protected $_publishRouter;

    /**
     * Logger
     *
     * @var Zend_Log
     */
    protected $_logger;

    /**
     * Connect to server
     *
     * @return AMQPConnection
     */
    protected function _getConnection()
    {
        if (!$this->_connection) {

            $transport = new Varien_Object;
            Mage::dispatchEvent('rabbitmq_connect_before', ['transport' => $transport]);

            $this->_connection = new AMQPConnection(
                $this->_config('host'),
                $this->_config('port'),
                $this->_config('username'),
                $this->_config('pass'),
                $this->_config('virtualhost')
            );
            $this->_logger->info('Established connection with Rabbit MQ server');

            Mage::dispatchEvent('rabbitmq_connect_after', ['transport' => $transport]);
        }
        return $this->_connection;
    }

    /**
     * Get transport channel for current connection.
     *
     * @return AMQPChannel
     */
    protected function _getChannel()
    {
        if (!$this->_channel) {
            $this->_channel = $this->_getConnection()->channel();
        }
        return $this->_channel;
    }

    /**
     * Set queues to consume
     *
     * @param array $consumeQueueus Queues names
     * @return Oggetto_Messenger_Model_Transport_Rabbitmq
     */
    public function setConsumeQueues(array $consumeQueueus)
    {
        $this->_consumeQueues = $consumeQueueus;
        return $this;
    }

    /**
     * Set logger
     *
     * @param Zend_Log $logger Logger
     * @return Oggetto_Messenger_Model_Transport_Rabbitmq
     */
    public function setLogger(Zend_Log $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Start receiving messages countinously
     *
     * @param Oggetto_Messenger_Model_Message_Interface $messagePrototype Messages class to be received
     * @param array|Closure                             $callback         Callback to be called on message receive
     *
     * @throws Exception
     * @return void
     */
    public function startReceiving(Oggetto_Messenger_Model_Message_Interface $messagePrototype, $callback)
    {
        try {
            $this->_getChannel()->basic_qos(null, 1, null);
            foreach ($this->_consumeQueues as $_queue) {
                $this->_getChannel()->queue_declare($_queue, false, true, false, false);
                $transport = $this;
                $this->_getChannel()->basic_consume($_queue, '', false, false, false, false,
                    function ($msg) use ($transport, $callback, $messagePrototype) {
                        $transport->receiveMessage($msg, $messagePrototype, $callback);
                    }
                );
            }

            while (count($this->_getChannel()->callbacks)) {
                $this->_getChannel()->wait();
            }
        } catch (Exception $e) {
            $this->_close();
            throw $e;
        }
        $this->_close();
    }

    /**
     * Receive message
     *
     * @param AMQPMessage                               $rabbitMessage    Rabbit message
     * @param Oggetto_Messenger_Model_Message_Interface $messagePrototype Message prototype
     * @param array|Closure                             $callback         Callback
     *
     * @throws Exception
     * @throws Oggetto_Messenger_Exception_Critical
     *
     * @return void
     */
    public function receiveMessage(
        AMQPMessage $rabbitMessage,
        Oggetto_Messenger_Model_Message_Interface $messagePrototype,
        $callback
    ) {
        try {
            $this->_logger->info("Received new message: {$rabbitMessage->body}");

            $message = clone $messagePrototype;
            $message->init($rabbitMessage->body);
            call_user_func_array($callback, array($message));
        } catch (Oggetto_Messenger_Exception_Critical $e) {
            // Only critical exceptions are supposed to stop the receiver
            throw $e;
        } catch (Exception $e) {
            $this->_logger->err($e);
        }

        $channel = $rabbitMessage->{'delivery_info'}['channel'];
        $channel->basic_ack($rabbitMessage->{'delivery_info'}['delivery_tag']);
    }

    /**
     * Close session
     *
     * @return void
     */
    protected function _close()
    {
        $this->_getChannel()->close();
        $this->_getConnection()->close();
    }

    /**
     * Get config value
     *
     * @param string $name Config name
     * @return string
     */
    protected function _config($name)
    {
        return Mage::getStoreConfig("messenger/rabbitmq/{$name}");
    }

    /**
     * Send message
     *
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     * @return void
     */
    public function send(Oggetto_Messenger_Model_Message_Interface $message)
    {
        if (!$this->_publishRouter) {
            $this->_logger->warn('Publish router is not defined: message cannot be sent');
            return;
        }
        if ($queue = $this->_publishRouter->findMessageQueue($message)) {
            $this->_declareQueue($queue);
            $this->_sendMessage($message, $queue);
        } else {
            $this->_logger->warn('Destination queue not found for message:');
            $this->_logger->warn(print_r($message->getMeta(), true));
        }
    }

    /**
     * Set publish router
     *
     * @param Oggetto_Messenger_Model_Transport_Rabbitmq_Router $router Router
     * @return Oggetto_Messenger_Model_Transport_Rabbitmq
     */
    public function setPublishRouter(Oggetto_Messenger_Model_Transport_Rabbitmq_Router $router)
    {
        $this->_publishRouter = $router;
        return $this;
    }

    /**
     * Get publish router instance
     *
     * @return Oggetto_Messenger_Model_Transport_Rabbitmq_Router
     */
    public function getPublishRouter()
    {
        return $this->_publishRouter;
    }

    /**
     * Declare RabbitMQ queue
     *
     * @param string $queue Queue name
     * @return void
     */
    private function _declareQueue($queue)
    {
        $transport = new Varien_Object;
        Mage::dispatchEvent('rabbitmq_queue_declare_before', ['queue' => $queue, 'transport' => $transport]);
        $this->_getChannel()->queue_declare($queue, false, true, false, false);
        Mage::dispatchEvent('rabbitmq_queue_declare_after', ['queue' => $queue, 'transport' => $transport]);
    }

    /**
     * Send message to queue
     *
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     * @param string                                    $queue   Queue
     *
     * @return void
     */
    private function _sendMessage(Oggetto_Messenger_Model_Message_Interface $message, $queue)
    {
        $rabbitMessage = new AMQPMessage($message->toString(), array(
            'delivery_mode' => 2 // Make message persistent
        ));
        $this->_logger
            ->info("Sending message to queue '{$queue}': {$rabbitMessage->body}");

        $transport = new Varien_Object;
        Mage::dispatchEvent('rabbitmq_publish_before', ['message' => $rabbitMessage, 'transport' => $transport]);
        $this->_getChannel()->basic_publish($rabbitMessage, '', $queue);
        Mage::dispatchEvent('rabbitmq_publish_after', ['message' => $rabbitMessage, 'transport' => $transport]);
    }
}