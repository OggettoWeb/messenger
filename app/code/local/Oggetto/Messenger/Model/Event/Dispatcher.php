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
 * @copyright  Copyright (C) 2012 Oggetto Web (http://oggettoweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Event dispatcher model
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Event_Dispatcher
{
    /**
     * Event message prototype
     *
     * @var Oggetto_Messenger_Model_Message_Interface
     */
    private $_messagePrototype;

    /**
     * Event message transport
     *
     * @var Oggetto_Messenger_Model_Transport_Interface
     */
    private $_transport;

    /**
     * Dispatch observers
     *
     * @var Oggetto_Messenger_Model_Event_DispatchObserver_Interface[]
     */
    private $_observers = [];

    /**
     * Set message prototype
     *
     * @param Oggetto_Messenger_Model_Message_Interface $prototype Prototype
     * @return Oggetto_Messenger_Model_Event_Receiver
     */
    public function setMessagePrototype(Oggetto_Messenger_Model_Message_Interface $prototype)
    {
        $this->_messagePrototype = $prototype;
        return $this;
    }

    /**
     * Set transport
     *
     * @param Oggetto_Messenger_Model_Transport_Interface $transport Transport
     * @return Oggetto_Messenger_Model_Event_Receiver
     */
    public function setTransport(Oggetto_Messenger_Model_Transport_Interface $transport)
    {
        $this->_transport = $transport;
        return $this;
    }

    /**
     * Dispatch event via specific message to transport
     *
     * @param Oggetto_Messenger_Model_Event $event Event
     * @return void
     */
    public function dispatch(Oggetto_Messenger_Model_Event $event)
    {
        $message = $this->buildMessage($event, clone $this->_messagePrototype);

        $this->_beforeDispatch($event, $message);
        $this->_transport->send($message);
        $this->_afterDispatch($event, $message);
    }

    /**
     * Build message from event
     *
     * @param Oggetto_Messenger_Model_Event             $event   Event
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     *
     * @return Oggetto_Messenger_Model_Message_Interface
     */
    public function buildMessage(
        Oggetto_Messenger_Model_Event $event,
        Oggetto_Messenger_Model_Message_Interface $message
    ) {
        $message->setMeta(array('_class' => 'event', 'name' => $event->getName()));
        $message->setData($event->getData());
        return $message;
    }

    /**
     * Attach dispatch observer
     *
     * @param Oggetto_Messenger_Model_Event_DispatchObserver_Interface $observer Observer
     * @return Oggetto_Messenger_Model_Event_Dispatcher
     */
    public function attachObserver(Oggetto_Messenger_Model_Event_DispatchObserver_Interface $observer)
    {
        $this->_observers[] = $observer;
        return $this;
    }

    /**
     * Handle before dispatch logic
     *
     * @param Oggetto_Messenger_Model_Event             $event   Event
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     *
     * @return mixed
     */
    private function _beforeDispatch(
        Oggetto_Messenger_Model_Event $event,
        Oggetto_Messenger_Model_Message_Interface $message
    ) {
        foreach ($this->_observers as $_observer) {
            $_observer->beforeDispatch($event, $message);
        }
    }

    /**
     * Handle after dispatch logic
     *
     * @param Oggetto_Messenger_Model_Event             $event   Event
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     *
     * @return mixed
     */
    private function _afterDispatch(
        Oggetto_Messenger_Model_Event $event,
        Oggetto_Messenger_Model_Message_Interface $message
    ) {
        foreach ($this->_observers as $_observer) {
            $_observer->afterDispatch($event, $message);
        }
    }
}