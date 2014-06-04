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
 * Event
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Event
{
    /**
     * Event name
     *
     * @var string
     */
    protected $_name;

    /**
     * Event data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Set event name
     *
     * @param string $name Name
     * @return Oggetto_Messenger_Model_Event
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Set event data
     *
     * @param array $data Data
     * @return Oggetto_Messenger_Model_Event
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Add data to event
     *
     * @param array $data Data
     * @return Oggetto_Messenger_Model_Event
     */
    public function addData(array $data)
    {
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get event data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}