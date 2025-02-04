<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Abstract base class for Highlighter renderers
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package    Text_Highlighter
 * @author     Andrey Demenev <demenev@on-line.jar.ru>
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    CVS: $Id: Renderer.php 2 2005-06-13 10:42:23Z alex $
 * @link       http://pear.php.net/package/Text_Highlighter
 */

/**
 * Abstract base class for Highlighter renderers
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 * @abstract
 */

class Text_Highlighter_Renderer
{
    /**
     * Renderer options
     *
     * @var array
     * @access protected
     */
    var $_options = array();

    /**
     * Constructor
     *
     * @access public
     *
     * @param  array $options  Rendering options. Renderer-specific.
     */
    function __construct($options = array())
    {
        $this->_options = $options;
    }

    /**
     * PHP4 compatable constructor
     *
     * @access public
     *
     * @param  array $options  Rendering options. Renderer-specific.
     */
    function Text_Highlighter_Renderer($options = array())
    {
        $this->__construct($options);
    }

    /**
     * Resets renderer state
     *
     * @access public
     *
     * @param  array $options  Rendering options. Renderer-specific.
     */
    function reset()
    {
        return;
    }

    /**
     * Preprocesses code
     *
     * @access public
     *
     * @param  string $str Code to preprocess
     * @return string Preprocessed code
     */
    function preprocess($str)
    {
        return $str;
    }

    /**
     * Accepts next token
     *
     * @abstract
     * @access public
     *
     * @param  string $class   Token class
     * @param  string $content Token content
     */
    function acceptToken($class, $content)
    {
        return;
    }

    /**
     * Signals that no more tokens are available
     *
     * @access public
     *
     */
    function finalize()
    {
        return;
    }

    /**
     * Get generated output
     *
     * @abstract
     * @return mixed Renderer-specific
     * @access public
     *
     */
    function getOutput()
    {
        return;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
