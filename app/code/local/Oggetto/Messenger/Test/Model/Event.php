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
 * @copyright  Copyright (C) 2012 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Event test case
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Event extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test returns name which has been initialized
     *
     * @return void
     */
    public function testReturnsNameWhichHasBeenInitialized()
    {
        $event = Mage::getModel('messenger/event')->setName('something_happend');
        $this->assertEquals('something_happend', $event->getName());
    }

    /**
     * Test returns data which has been initialized
     *
     * @return void
     */
    public function testReturnsDataWhichHasBeenInitialized()
    {
        $event = Mage::getModel('messenger/event')->setData(['foo']);
        $this->assertEquals(['foo'], $event->getData());
    }

    /**
     * Test allows to add part of the data
     *
     * @return void
     */
    public function testAllowsToAddPartOfTheData()
    {
        $event = Mage::getModel('messenger/event')->setData(['foo' => 1]);
        $event->addData(['bar' => 2]);

        $this->assertEquals(['foo' => 1, 'bar' => 2], $event->getData());
    }
}

