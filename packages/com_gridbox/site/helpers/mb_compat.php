<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding = 'utf-8')
    {
        return strtolower($str);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen($str)
    {
        return strlen($str);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = 0, $encoding = 'utf-8')
    {
        return substr($str, $start, $length);
    }
}
if (!function_exists('mb_substr')) {
    function mb_strpos($haystack, $needle, $offset = 0, $encoding = 'utf-8')
    {
        return strpos($haystack, $needle, $offset);
    }
}