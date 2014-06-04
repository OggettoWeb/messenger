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
 * XML-formatted message
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Message_Xml implements Oggetto_Messenger_Model_Message_Interface
{
    /**
     * Meta info
     *
     * @var array
     */
    protected $_meta;

    /**
     * Message data
     *
     * @var array
     */
    protected $_data;

    /**
     * Build message string
     *
     * @return string
     */
    public function toString()
    {
        $meta = $this->getMeta();
        $class = isset($meta['_class']) ? $meta['_class'] : 'message';

        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('update');
        $xml->startElement($class);
        $this->_writeMetaToXml($xml, $this->getMeta());
        foreach ($this->getData() as $_node => $_value) {
            $this->_writeDataToXml($xml, $_node, $_value);
        }
        $xml->endElement(); // class
        $xml->endElement(); // update

        $xml->endDocument();
        return $xml->outputMemory(true);
    }

    /**
     * Write meta data to xml
     *
     * @param XMLWriter $xml  XML
     * @param array     $meta Meta
     *
     * @return void
     */
    protected function _writeMetaToXml(XMLWriter $xml, array $meta)
    {
        unset($meta['_class']);
        foreach ($meta as $_code => $_value) {
            $xml->writeAttribute($_code, $_value);
        }
    }

    /**
     * Write data to xml
     *
     * @param XMLWriter    $xml   XML
     * @param string       $node  Node name
     * @param string|array $value Node value
     *
     * @return void
     */
    protected function _writeDataToXml(XMLWriter $xml, $node, $value)
    {
        if (is_array($value)) {
            if ($this->_isList($value)) {
                $this->_writeListToXml($xml, $node, $value);
            } else {
                $this->_writeFieldsToXml($xml, $node, $value);
            }
        } else {
            $this->_writeTextToXml($xml, $node, $value);
        }
    }

    /**
     * Write text value to xml
     *
     * @param XMLWriter $xml  XML
     * @param string    $node Node name
     * @param string    $text Node text
     *
     * @return void
     */
    protected function _writeTextToXml(XMLWriter $xml, $node, $text)
    {
        $xml->startElement($node);
        $xml->text($text);
        $xml->endElement();
    }

    /**
     * Write associated fields list to xml
     *
     * @param XMLWriter $xml   XML
     * @param string    $node  Node name
     * @param array     $items Items data
     *
     * @return void
     */
    protected function _writeFieldsToXml($xml, $node, $items)
    {
        $xml->startElement($node);
        foreach ($items as $_key => $_value) {
            $this->_writeDataToXml($xml, $_key, $_value);
        }
        $xml->endElement();
    }

    /**
     * Write data list to xml
     *
     * @param XMLWriter $xml   XML
     * @param string    $node  List items node name
     * @param mixed     $items Items
     *
     * @return void
     */
    protected function _writeListToXml($xml, $node, $items)
    {
        foreach ($items as $_value) {
            $this->_writeDataToXml($xml, $node, $_value);
        }
    }

    /**
     * Check if data node represents list structure
     * List structures are indexed lists, like:
     *  0 => value 1
     *  1 => value 2
     *  2 => value 3
     *
     * @param array $dataNode Data node
     * @return boolean
     */
    protected function _isList($dataNode)
    {
        return array_values($dataNode) === $dataNode;
    }

    /**
     * Init message from string
     *
     * @param string $string String
     * @throws InvalidArgumentException
     * @return Oggetto_Messenger_Model_Message_Interface
     */
    public function init($string)
    {
        $xml = new Varien_Simplexml_Element($string);
        if (!$xml->count()) {
            throw new InvalidArgumentException(
                Mage::helper('messenger')->__('Message without body cannot be initialized')
            );
        }

        $children = $xml->children();
        $body = $children[0];

        $this->_meta['_class'] = $body->getName();
        foreach ($body->attributes() as $_code => $_value) {
            $this->_meta[$_code] = (string) $_value;
        }

        $this->_data = $this->_xmlToArray($body);
        return $this;
    }

    /**
     * Convert xml to array
     *
     * @param Varien_Simplexml_Element $element XML element
     * @return array|string
     */
    protected function _xmlToArray(Varien_Simplexml_Element $element)
    {
        if (!$element->hasChildren()) {
            return (string) $element;
        }

        $result = array();
        foreach ($element->children() as $_name => $_child) {
            if (isset($result[$_name])) {
                if (!is_array($result[$_name]) || !isset($result[$_name]['__list'])) {
                    $result[$_name] = array($result[$_name]);
                    $result[$_name]['__list'] = true;
                }
                $result[$_name][] = $this->_xmlToArray($_child);
            } else {
                $result[$_name] = $this->_xmlToArray($_child);
            }
        }

        // Remove temp __list mark
        array_walk($result, function (&$node) {
            if (is_array($node)) {
                unset($node['__list']);
            }
        });

        return $result;
    }

    /**
     * Get message meta information
     *
     * @return array
     */
    public function getMeta()
    {
        return (array) $this->_meta;
    }

    /**
     * Get message contents
     *
     * @return array
     */
    public function getData()
    {
        return (array) $this->_data;
    }

    /**
     * Set message meta information
     *
     * @param array $meta Meta info
     * @return Oggetto_Messenger_Model_Message_Xml
     */
    public function setMeta($meta)
    {
        $this->_meta = $meta;
        return $this;
    }

    /**
     * Set message contents
     *
     * @param array $data Message contents
     * @return Oggetto_Messenger_Model_Message_Xml
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Test if message matches criterion
     *
     * @param Varien_Object $criterion Matching criterion
     * @return boolean
     */
    public function matchesCriterion(Varien_Object $criterion)
    {
        $meta = $this->getMeta();
        $name = isset($meta['name']) ? $meta['name'] : null;

        return $criterion->getName() && $criterion->getName() == $name;
    }
}