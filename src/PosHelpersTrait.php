<?php

namespace Ankapix\SanalPos;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use SimpleXMLElement;

/**
 * Trait PosHelpersTrait
 * @package Ankapix\SanalPos
 */
trait PosHelpersTrait
{
    /**
     * API URL
     *
     * @var string
     */
    public $url;

    /**
     * 3D Pay Gateway URL
     *
     * @var string
     */
    public $gateway;

    /**
     * Create XML DOM Document
     *
     * @param array $nodes
     * @param string $encoding
     * @return string the XML, or false if an error occurred.
     */
    public function createXML(array $nodes, $encoding = 'UTF-8')
    {
        $rootNodeName = array_keys($nodes)[0];
        $encoder = new XmlEncoder();

        $xml = $encoder->encode($nodes[$rootNodeName], 'xml', [
            XmlEncoder::ROOT_NODE_NAME => $rootNodeName,
            XmlEncoder::ENCODING  => $encoding
        ]);
        return $xml;
    }

    /**
     * Print Data
     *
     * @param $data
     * @return null|string
     */
    public function printData($data)
    {
        if ((is_object($data) || is_array($data)) && !count((array)$data)) {
            $data = null;
        }

        return (string)$data;
    }

    /**
     * Is success
     *
     * @return bool
     */
    public function isSuccess()
    {
        $success = false;
        if (isset($this->response) && $this->response->status == 'approved') {
            $success = true;
        }

        return $success;
    }

    /**
     * Is error
     *
     * @return bool
     */
    public function isError()
    {
        return !$this->isSuccess();
    }

    /**
     * Converts XML string to object
     *
     * @param string data
     * @return object
     */
    public function XMLStringToObject($data)
    {
        $encoder = new XmlEncoder();
        $xml = $encoder->decode($data, 'xml');
        return (object)json_decode(json_encode($xml));
    }
    /**
    * Get Ip Address
    *
    * @return string
    */
    public function getIpAdress()
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = $_SERVER;
        }

        if (array_key_exists('X-Forwarded-For', $headers)) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $headers['X-Forwarded-For'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && (!isset($_SERVER['REMOTE_ADDR']) || preg_match('/^127\..*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^172\.16.*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^192\.168\.*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^10\..*/i', trim($_SERVER['REMOTE_ADDR'])))) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return $ips[0];
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
