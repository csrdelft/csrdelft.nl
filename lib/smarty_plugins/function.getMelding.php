<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {getMelding} function plugin
 *
 * Type:     function
 * Name:     getMelding
 * Input:
 *
 * Examples:
 * <pre>
 *  {getMelding}
 * </pre>
 * @version  1.0
 * @author   Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @param    array
 * @param    Smarty
 * @return   string
 */
function smarty_function_getMelding($params, &$smarty) {

	return getMelding();
}
