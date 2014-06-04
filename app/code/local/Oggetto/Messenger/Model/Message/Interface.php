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
 * Message interface
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
interface Oggetto_Messenger_Model_Message_Interface
{
    /**
     * Build message string
     *
     * @return string
     */
    public function toString();

    /**
     * Init message from string
     *
     * @param string $string String
     * @return Oggetto_Messenger_Model_Message_Interface
     */
    public function init($string);

    /**
     * Get message meta information
     *
     * @return mixed
     */
    public function getMeta();

    /**
     * Get message contents
     *
     * @return mixed
     */
    public function getData();

    /**
     * Set message meta information
     *
     * @param mixed $meta Meta info
     * @return void
     */
    public function setMeta($meta);

    /**
     * Set message contents
     *
     * @param mixed $data Message contents
     * @return void
     */
    public function setData($data);

    /**
     * Test if message matches criterion
     *
     * @param Varien_Object $criterion Criterion
     * @return boolean
     */
    public function matchesCriterion(Varien_Object $criterion);
}