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
 * Json formatter
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Test
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Log_Formatter_Json
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test formats event data as JSON
     *
     * @return void
     */
    public function testFormatsEventDataAsJson()
    {
        $event = [
            'timestamp'    => '2013-20-01 12:30:45',
            'priorityName' => 'INFO',
            'priority'     => 42,
            'source'       => 'Messenger',
            'pid'          => 'qwerty',
            'message'      => 'Foo bar',
            'foo'          => 'bar'
        ];

        $formatter = Mage::getModel('messenger/log_formatter_json');
        $this->assertEquals(Zend_Json::encode($event) . PHP_EOL, $formatter->format($event));
    }
    
    /**
     * Test formats objects to strings
     *
     * @return void
     */
    public function testFormatsObjectsToStrings()
    {
        $object = $this->getMock('stdClass', ['__toString']);
        $object->expects($this->any())->method('__toString')
            ->will($this->returnValue('Foo'));

        $event = [
            'foo' => $object
        ];
        $formatter = Mage::getModel('messenger/log_formatter_json');
        $this->assertEquals(
            Zend_Json::encode(['foo' => 'Foo']) . PHP_EOL,
            $formatter->format($event)
        );
    }

    /**
     * Test formats non-stringable objects to class names
     *
     * @return void
     */
    public function testFormatsNonStringableObjectsToClassNames()
    {
        $object = new stdClass;
        $event = [
            'foo' => $object
        ];

        $formatter = Mage::getModel('messenger/log_formatter_json');
        $this->assertEquals(
            Zend_Json::encode(['foo' => 'stdClass']) . PHP_EOL,
            $formatter->format($event)
        );
    }
}