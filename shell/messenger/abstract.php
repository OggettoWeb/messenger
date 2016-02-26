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
define('MAGE_HOME', ($path = getenv('MAGE_HOME')) ? $path : realpath(__DIR__.'/../../'));
require_once MAGE_HOME.'/shell/abstract.php';
require_once MAGE_HOME.'/vendor/autoload.php';
require_once MAGE_HOME.'/lib/Varien/Profiler.php';

/**
 * Abstract messenger shell class
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
abstract class Oggetto_Shell_Messenger_Abstract extends Mage_Shell_Abstract
{
    /**
     * Init the script
     *
     * @return void
     */
    public function _construct()
    {
        $this->_initLogger();
    }

    /**
     * Get log
     *
     * @return Zend_Log
     */
    protected function _log()
    {
        return Mage::getSingleton('messenger/di')->container()->get('log');
    }

    /**
     * Setup console logger
     *
     * @return void
     */
    private function _initLogger()
    {
        $writer = Zend_Log_Writer_Stream::factory(['stream' => 'php://stdout']);
        $formatter = Mage::getModel('messenger/log_formatter')->factory($this->getArg('log-format'));
        $writer->setFormatter($formatter);

        Mage::getSingleton('messenger/di')->container()
            ->instanceManager()->setParameters('log', ['writer' => $writer]);
    }
}

Varien_Profiler::enable();
