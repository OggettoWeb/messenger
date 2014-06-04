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
 * XML-formatted message test case
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Message_Xml extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test inits data from string
     *
     * @param string $xml XML
     *
     * @return void
     * @dataProvider dataProvider
     * @loadExpectation
     */
    public function testInitsDataFromString($xml)
    {
        $message = Mage::getModel('messenger/message_xml')->init($xml);
        $this->assertEquals($this->expected()->getMeta(), $message->getMeta());
        $this->assertEquals($this->expected()->getMessageData(), $message->getData());
    }

    /**
     * Test throws exception on empty message
     *
     * @param string $xml XML
     * @return void
     * @dataProvider dataProvider
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Message without body cannot be initialized
     */
    public function testThrowsExceptionOnEmptyMessage($xml)
    {
        Mage::getModel('messenger/message_xml')->init($xml);
    }

    /**
     * Test builds message string from data
     *
     * @param array $data Message data
     * @param array $meta Message meta data
     *
     * @return void
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function testBuildsMessageStringFromData($data, $meta)
    {
        $message = Mage::getModel('messenger/message_xml');
        $message->setMeta($meta);
        $message->setData($data);

        $xml = preg_replace('/\s+/', ' ', $message->toString());
        $expected = preg_replace('/\s+/', ' ', $this->expected()->getXml());
        $this->assertEquals($expected, trim($xml));
    }

    /**
     * Test matches criterion by meta name
     *
     * @return void
     */
    public function testMatchesCriterionByMetaName()
    {
        $criterion = new Varien_Object(array('name' => 'foo'));
        $message = Mage::getModel('messenger/message_xml');

        $message->setMeta(array('name' => 'foo'));
        $this->assertTrue($message->matchesCriterion($criterion));

        $message->setMeta(array('name' => 'bar'));
        $this->assertFalse($message->matchesCriterion($criterion));
    }
}