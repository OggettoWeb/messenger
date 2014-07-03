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
 * Messenger logger builder
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Log_Logger
    extends Zend_Log
{
    /**
     * Class constructor.  Create a new logger
     *
     * @param Zend_Log_Writer_Abstract|null $writer Default writer
     */
    public function __construct(Zend_Log_Writer_Abstract $writer = null)
    {
        parent::__construct($writer ? : $this->_initDefaultWriter($writer));

        $pid = bin2hex(openssl_random_pseudo_bytes(5));
        $this->setEventItem('pid', $pid);
    }

    /**
     * Init default logger
     *
     * @return Zend_Log_Writer_Abstract
     */
    private function _initDefaultWriter()
    {
        $writer = Zend_Log_Writer_Stream::factory(
            ['stream' => Mage::getBaseDir('log') . DS . Mage::getStoreConfig('messenger/log/file')]
        );
        $formatter = Mage::getModel('messenger/log_formatter')->factory(
            Mage::getStoreConfig('messenger/log/format')
        );

        $writer->setFormatter($formatter);
        return $writer;
    }

    /**
     * Packs message and priority into Event array
     *
     * @param string  $message  Message to log
     * @param integer $priority Priority of message
     *
     * @return array Event array
     **/
    protected function _packEvent($message, $priority)
    {
        return array_merge(array(
                'timestamp'    => $this->_timestamp(),
                'message'      => $message,
                'priority'     => $priority,
                'priorityName' => $this->_priorities[$priority]
            ),
            $this->_extras
        );
    }

    /**
     * Get current timestamp with milliseconds
     *
     * @return string
     */
    private function _timestamp()
    {
        return date(sprintf('Y-m-d\TH:i:s%sP', substr(microtime(), 1, 8)));
    }
}
