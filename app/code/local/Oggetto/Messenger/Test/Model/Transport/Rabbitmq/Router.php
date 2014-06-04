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
 * RabbitMQ transport router test case
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Test_Model_Transport_Rabbitmq_Router extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test finds message in routing map by criterion
     *
     * @return void
     */
    public function testFindsMessageQueueInRoutingMapByCriterion()
    {
        $matchingCriterion = new Varien_Object;
        $notMatchingCriterion = new Varien_Object;

        $message = $this->getModelMock('messenger/message_interface', array('matchesCriterion'), true);
        $message->expects($this->at(0))->method('matchesCriterion')
            ->with($this->equalTo($notMatchingCriterion))
            ->will($this->returnValue(false));
        $message->expects($this->at(1))->method('matchesCriterion')
            ->with($this->equalTo($matchingCriterion))
            ->will($this->returnValue(true));

        $router = Mage::getModel('messenger/transport_rabbitmq_router');
        $router->addMap($notMatchingCriterion, 'not_matching_queue');
        $router->addMap($matchingCriterion, 'matching_queue');

        $this->assertEquals('matching_queue', $router->findMessageQueue($message));
    }

    /**
     * Test retrieves routes from Magento config
     *
     * @return void
     */
    public function testRetrievesRoutesFromMagentoConfig()
    {
        $router = $this->getModelMock('messenger/transport_rabbitmq_router', array('addMap'));
        $router->expects($this->once())->method('addMap')
            ->with(
                $this->equalTo(new Varien_Object(['foo' => 'bar'])),
                $this->equalTo('test_message')
            );

        $configNode = new Mage_Core_Model_Config_Element(<<<XML
<publish_router>
    <route_example>
        <criterion>
            <foo>bar</foo>
        </criterion>
        <queue>test_message</queue>
    </route_example>
</publish_router>
XML
        );
        $config = $this->getModelMock('core/config');
        $config->expects($this->once())->method('getNode')
            ->with($this->equalTo('global/messenger/publish_router'))
            ->will($this->returnValue($configNode));

        $router->initFromConfig($config);
    }

    /**
     * Test does not retrieve routes from empty config
     *
     * @return void
     */
    public function testDoesNotRetrieveRoutesFromEmptyConfig()
    {
        $router = $this->getModelMock('messenger/transport_rabbitmq_router', array('addMap'));
        $router->expects($this->never())->method('addMap');

        $config = $this->getModelMock('core/config');
        $config->expects($this->once())->method('getNode')
            ->with($this->equalTo('global/messenger/publish_router'))
            ->will($this->returnValue(null));

        $router->initFromConfig($config);
    }
}