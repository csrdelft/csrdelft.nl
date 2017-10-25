<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * @param $params
 * @param $smarty
 *
 * @return string
 */
function smarty_compiler_toegangclose($params, &$smarty) {
	return "<?php } ?>";
}
/* vim: set expandtab: */
