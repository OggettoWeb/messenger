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
 * Events receiver
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Event_Receiver
{
    /**
     * Events observer instance cache
     *
     * @var Oggetto_Messenger_Model_Event_Observer
     */
    private $_observer;

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
     * Receive events via specific messages from transport
     *
     * @param mixed $callback Callback
     * @return void
     */
    public function receive($callback)
    {
        $this->_transport->startReceiving($this->_messagePrototype, $callback);
    }

    /**
     * Process received message
     *
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     * @return void
     */
    public function processMessage(Oggetto_Messenger_Model_Message_Interface $message)
    {
        $this->_getObserver()->observe($this->buildEvent($message));
    }

    /**
     * Get events observer instance
     *
     * @return Oggetto_Messenger_Model_Event_Observer
     */
    protected function _getObserver()
    {
        if (!$this->_observer) {
            $this->_observer = Mage::getModel('messenger/event_observer')
                ->initFromConfig(Mage::app()->getConfig());
        }
        return $this->_observer;
    }

    /**
     * Build event from message
     *
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     * @return Oggetto_Messenger_Model_Event
     */
    public function buildEvent(Oggetto_Messenger_Model_Message_Interface $message)
    {
        $meta = new Varien_Object((array) $message->getMeta());
        return Mage::getModel('messenger/event')
            ->setName($meta->getName())
            ->setData((array) $message->getData());
    }
}