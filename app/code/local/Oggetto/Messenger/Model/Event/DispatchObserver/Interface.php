<?php
/**
 * Oggetto Web extension for Magento
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
 * the Oggetto Erp module to newer versions in the future.
 * If you wish to customize the module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @copyright  Copyright (C) 2013 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Event dispatch observer interface
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
interface Oggetto_Messenger_Model_Event_DispatchObserver_Interface
{
    /**
     * Handle before dispatch logic
     *
     * @param Oggetto_Messenger_Model_Event             $event   Event
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     *
     * @return mixed
     */
    public function beforeDispatch(
        Oggetto_Messenger_Model_Event $event,
        Oggetto_Messenger_Model_Message_Interface $message
    );

    /**
     * Handle after dispatch logic
     *
     * @param Oggetto_Messenger_Model_Event             $event   Event
     * @param Oggetto_Messenger_Model_Message_Interface $message Message
     *
     * @return mixed
     */
    public function afterDispatch(
        Oggetto_Messenger_Model_Event $event,
        Oggetto_Messenger_Model_Message_Interface $message
    );
}