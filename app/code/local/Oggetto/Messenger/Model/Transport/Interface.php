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
 * Messages transport interface
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
interface Oggetto_Messenger_Model_Transport_Interface
{
    /**
     * Start receiving messages continuously
     *
     * @param Oggetto_Messenger_Model_Message_Interface $messagePrototype Messages class to be received
     * @param array|Closure                             $callback         Callback to be called on message receive
     *
     * @return void
     */
    public function startReceiving(Oggetto_Messenger_Model_Message_Interface $messagePrototype, $callback);

    /**
     * Send message
     *
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     * @return void
     */
    public function send(Oggetto_Messenger_Model_Message_Interface $message);
}