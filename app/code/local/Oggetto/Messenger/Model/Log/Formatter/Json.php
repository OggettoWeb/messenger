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
 * JSON formatter
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Messenger_Model_Log_Formatter_Json
    implements Zend_Log_Formatter_Interface
{
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param array $event event data
     * @return string formatted line to write to the log
     */
    public function format($event)
    {
        return Zend_Json::encode($this->_eventData($event)) . PHP_EOL;
    }

    /**
     * Get event data
     *
     * @param array|mixed $event Event
     * @return array
     */
    private function _eventData($event)
    {
        if (!is_array($event)) {
            return [];
        }

        $data = [];
        foreach ($event as $_name => $_value) {
            if (is_object($_value)) {
                $data[$_name] = $this->_formatObject($_value);
            } else {
                $data[$_name] = $_value;
            }
        }

        return $data;
    }

    /**
     * Format exception
     *
     * @param Exception $e Exception
     * @return array
     */
    private function _formatException(Exception $e)
    {
        $result = [
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
            'trace'   => $e->getTrace()
        ];

        if ($previous = $e->getPrevious()) {
            $result['previous'] = $this->_formatException($e->getPrevious());
        }

        return $result;
    }

    /**
     * Format object
     *
     * @param object $object Object
     * @return string
     */
    private function _formatObject($object)
    {
        if (method_exists($object, '__toString')) {
            return (string)$object;
        }
        return get_class($object);
    }
}