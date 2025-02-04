<?php
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Stig Bakken <ssb@fast.no>                                   |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id: Remote.php 2 2005-06-13 10:42:23Z alex $

require_once 'PEAR.php';

/**
 * This is a class for doing remote operations against the central
 * PEAR database.
 */
class PEAR_Remote extends PEAR
{
    // {{{ properties

    var $config_object = null;

    // }}}

    // {{{ PEAR_Remote(config_object)

    function PEAR_Remote($config_object)
    {
        $this->PEAR();
        $this->config_object = $config_object;
    }

    // }}}

    // {{{ call(method, [args...])

    function call($method)
    {
        if (!extension_loaded("xmlrpc")) {
            return $this->raiseError("xmlrpc support not loaded");
        }
        $params = array_slice(func_get_args(), 1);
        $request = xmlrpc_encode_request($method, $params);
        $server_host = $this->config_object->get("master_server");
        if (empty($server_host)) {
            return $this->raiseError("PEAR_Remote::call: no master_server configured");
        }
        $server_port = 80;
        $fp = @fsockopen($server_host, $server_port);
        if (!$fp) {
            return $this->raiseError("PEAR_Remote::call: fsockopen(`$server_host', $server_port) failed");
        }
        $len = strlen($request);
        fwrite($fp, ("POST /xmlrpc.php HTTP/1.0\r\n".
                     "Host: $server_host:$server_port\r\n".
                     "Content-type: text/xml\r\n".
                     "Content-length: $len\r\n".
                     "\r\n$request"));
        $response = '';
        while (trim(fgets($fp, 2048)) != ''); // skip headers
        while ($chunk = fread($fp, 10240)) {
            $response .= $chunk;
        }
        fclose($fp);
        $ret = xmlrpc_decode($response);
        if (is_array($ret) && isset($ret['__PEAR_TYPE__'])) {
            if ($ret['__PEAR_TYPE__'] == 'error') {
                if (isset($ret['__PEAR_CLASS__'])) {
                    $class = $ret['__PEAR_CLASS__'];
                } else {
                    $class = "PEAR_Error";
                }
                if ($ret['code']     === '') $ret['code']     = null;
                if ($ret['message']  === '') $ret['message']  = null;
                if ($ret['userinfo'] === '') $ret['userinfo'] = null;
                if (strtolower($class) == 'db_error') {
                    return $this->raiseError(DB::errorMessage($ret['code']),
                                             $ret['code'], null, null,
                                             $ret['userinfo']);
                } else {
                    return $this->raiseError($ret['message'], $ret['code'],
                                             null, null, $ret['userinfo']);
                }
            }
        }
    }

    // }}}
}

?>