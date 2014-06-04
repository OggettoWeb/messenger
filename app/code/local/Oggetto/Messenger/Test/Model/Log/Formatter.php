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
 * @copyright  Copyright (C) 2014 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Formatter log
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Test
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Log_Formatter
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test builds text formatter
     *
     * @return void
     */
    public function testBuildsTextFormatter()
    {
        $formatter = Mage::getModel('messenger/log_formatter');
        $this->assertInstanceOf('Oggetto_Messenger_Model_Log_Formatter_Text', $formatter->factory('text'));
    }

    /**
     * Test builds JSON formatter
     *
     * @return void
     */
    public function testBuildsJsonFormatter()
    {
        $formatter = Mage::getModel('messenger/log_formatter');
        $this->assertInstanceOf('Oggetto_Messenger_Model_Log_Formatter_Json', $formatter->factory('json'));
    }

    /**
     * Test builds default formatter
     *
     * @return void
     */
    public function testBuildsDefaultFormatter()
    {
        $formatter = Mage::getModel('messenger/log_formatter');
        $this->assertInstanceOf('Oggetto_Messenger_Model_Log_Formatter_Text', $formatter->factory('asdasd'));
    }
}