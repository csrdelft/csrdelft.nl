<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * @param array $params
 * @param Smarty $smarty
 *
 * @return string
 */
function smarty_compiler_geentoegang($params, Smarty &$smarty) {
	return "<?php } else { ?>";
}
/* vim: set expandtab: */
