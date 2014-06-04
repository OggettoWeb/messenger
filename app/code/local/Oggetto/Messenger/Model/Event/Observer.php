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
 * Events observer
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Event_Observer
{
    /**
     * Observers list
     *
     * @var array
     */
    protected $_observers = array();

    /**
     * Observe event
     *
     * @param Oggetto_Messenger_Model_Event $event Event
     * @return void
     */
    public function observe(Oggetto_Messenger_Model_Event $event)
    {
        foreach ($this->getObservers() as $_observer) {
            if ($_observer->match($event)) {
                $_observer->observe($event);
            }
        }
    }

    /**
     * Get available observers
     *
     * @return array
     */
    public function getObservers()
    {
        return $this->_observers;
    }

    /**
     * Set available observers
     *
     * @param array $observers Array of observers
     * @return Oggetto_Messenger_Model_Event_Observer
     */
    public function setObservers(array $observers)
    {
        $this->_observers = $observers;
        return $this;
    }

    /**
     * Init observers from Magento config
     *
     * @param Mage_Core_Model_Config $config Config
     * @return Oggetto_Messenger_Model_Event_Observer
     */
    public function initFromConfig(Mage_Core_Model_Config $config)
    {
        $configNodes = $config->getNode()->xpath('global/messenger/event_observers/*');
        if (!$configNodes) {
            return $this;
        }

        $observers = [];
        foreach ($configNodes as $_observer) {
            $observers[$_observer->getName()] =
                Mage::getSingleton('messenger/di')->get((string) $_observer->class);
        }
        $this->setObservers($observers);
        return $this;
    }
}