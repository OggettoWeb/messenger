<?php
require_once Mage::getBaseDir() . DS . 'vendor' . DS . 'autoload.php';

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
 * Events observer test case
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Event_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test observes event with available observers
     *
     * @return void
     */
    public function testObservesEventWithOneOfObservers()
    {
        $event = Mage::getModel('messenger/event');
        $matched = $this->getModelMock('messenger/event_observer_interface', array('match', 'observe'), true);
        $unmatched = $this->getModelMock('messenger/event_observer_interface', array('match', 'observe'), true);

        $matched->expects($this->once())->method('match')
            ->with($this->equalTo($event))
            ->will($this->returnValue(true));
        $unmatched->expects($this->once())->method('match')
            ->with($this->equalTo($event))
            ->will($this->returnValue(false));
        $matched->expects($this->once())->method('observe')->with($this->equalTo($event));
        $unmatched->expects($this->never())->method('observe');

        Mage::getModel('messenger/event_observer')
            ->setObservers(array($matched, $unmatched))
            ->observe($event);
    }

    /**
     * Test retrieves observes from config
     *
     * @return void
     * @loadExpectation
     */
    public function testRetrievesObservesFromConfig()
    {
        $testObserver = $this->getModelMock('messenger/event_observer_interface');
        $di = $this->getModelMock('messenger/di', array('_initTransport', 'get'));
        $di->expects($this->any())->method('get')
            ->with($this->equalTo('messenger/event_observer_test'))
            ->will($this->returnValue($testObserver));
        $this->replaceByMock('model', 'messenger/di', $di);

        $configNode = new Mage_Core_Model_Config_Element(<<<XML
<config>
    <global>
        <messenger>
            <event_observers>
                <observer_foo>
                    <class>messenger/event_observer_test</class>
                </observer_foo>
            </event_observers>
        </messenger>
    </global>
</config>
XML
        );
        $config = $this->getModelMock('core/config', array('getNode'));
        $config->expects($this->any())->method('getNode')
            ->will($this->returnValue($configNode));

        $observers = $this->getModelMock('messenger/event_observer', array('setObservers'));
        $observers->expects($this->once())->method('setObservers')
            ->with($this->equalTo(['observer_foo' => $testObserver]));

        $observers->initFromConfig($config);
    }

    /**
     * Test does not retrieve observers from empty config
     *
     * @return void
     */
    public function testDoesNotRetrieveObserversFromEmptyConfig()
    {
        $di = $this->getModelMock('messenger/di', array('_initTransport'));
        $this->replaceByMock('model', 'messenger/di', $di);

        $configNode = new Mage_Core_Model_Config_Element(<<<XML
<config>
    <foo>bar</foo>
</config>
XML
        );
        $config = $this->getModelMock('core/config', array('getNode'));
        $config->expects($this->any())->method('getNode')
            ->will($this->returnValue($configNode));

        $observers = $this->getModelMock('messenger/event_observer', array('setObservers'));
        $observers->expects($this->never())->method('setObservers');

        $observers->initFromConfig($config);
    }
}