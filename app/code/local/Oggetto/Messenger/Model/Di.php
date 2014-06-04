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
 * Messenger DI container
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Di
{
    /**
     * Container instance
     *
     * @var Zend\Di\Di
     */
    protected $_di;

    /**
     * Init the dependencies
     *
     * @return Oggetto_Messenger_Model_Di
     */
    public function __construct()
    {
        $this->_di = new Zend\Di\Di;

        $this->_initTransport();
        $this->_initMessages();
        $this->_initLogger();

        Mage::dispatchEvent('messenger_di_initialized', ['di' => $this->_di]);
    }

    /**
     * Init default Messenger logger
     *
     * @return void
     */
    protected function _initLogger()
    {
        $this->_di->instanceManager()
            ->addAlias('log', 'Oggetto_Messenger_Model_Log_Logger');
        $this->_di->instanceManager()->setParameters(
            'Oggetto_Messenger_Model_Log_Loggable',
            ['logger' => 'log']
        );
    }

    /**
     * Init Messenger transport dependencies
     *
     * @return void
     */
    protected function _initTransport()
    {
        // Exchange with Messenger by RabbitMQ
        $this->_di->instanceManager()->addTypePreference(
            'Oggetto_Messenger_Model_Transport_Interface',
            'Oggetto_Messenger_Model_Transport_Rabbitmq');

        // Setup messages routing
        $router = Mage::getModel('messenger/transport_rabbitmq_router');
        $router->initFromConfig(Mage::app()->getConfig());
        $this->_di->instanceManager()->setParameters(
            'Oggetto_Messenger_Model_Transport_Rabbitmq', array('router' => $router));
    }

    /**
     * Init messages decencies
     *
     * @return void
     */
    private function _initMessages()
    {
        $this->_di->instanceManager()->addTypePreference(
            'Oggetto_Messenger_Model_Message_Interface',
            'Oggetto_Messenger_Model_Message_Xml');
    }

    /**
     * Get model class with injection (as a singleton)
     *
     * @param string $path   Path
     * @param array  $params Params
     * @return mixed
     */
    public function get($path, $params = [])
    {
        $className = Mage::getConfig()->getModelClassName($path);
        return $this->_di->get($className, $params);
    }

    /**
     * Get model class with injection (as new instance)
     *
     * @param string $path   Path
     * @param array  $params Params
     *
     * @return mixed
     */
    public function newInstance($path, $params = [])
    {
        $className = Mage::getConfig()->getModelClassName($path);
        return $this->_di->newInstance($className);
    }

    /**
     * Get DI container
     *
     * @return Zend\Di\Di
     */
    public function container()
    {
        return $this->_di;
    }
}
