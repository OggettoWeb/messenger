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
 * Rabbit MQ messaging router
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Transport_Rabbitmq_Router
{
    /**
     * Routing map
     *
     * @var array
     */
    protected $_map = array();

    /**
     * Add queue map
     *
     * @param Varien_Object $criterion Message creiterion
     * @param string        $queue     Queue to deliver
     *
     * @return Oggetto_Messenger_Model_Transport_Rabbitmq_Router
     */
    public function addMap(Varien_Object $criterion, $queue)
    {
        $this->_map[] = array(
            'criterion' => $criterion,
            'queue'     => $queue
        );
    }

    /**
     * Find queue name where to route message
     *
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     * @return string|null
     */
    public function findMessageQueue(Oggetto_Messenger_Model_Message_Interface $message)
    {
        foreach ($this->_map as $_map) {
            if ($message->matchesCriterion($_map['criterion'])) {
                return $_map['queue'];
            }
        }
    }

    /**
     * Init router from config
     *
     * @param Mage_Core_Model_Config $config Config
     * @return void
     */
    public function initFromConfig(Mage_Core_Model_Config $config)
    {
        $routerConfig = $config->getNode('global/messenger/publish_router');
        if (!$routerConfig) {
            return;
        }
        foreach ($routerConfig->children() as $_route) {
            $this->addMap(new Varien_Object($_route->criterion->asArray()), $_route->queue);
        }
    }
}