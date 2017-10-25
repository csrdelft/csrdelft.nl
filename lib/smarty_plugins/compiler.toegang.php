<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

use CsrDelft\model\security\LoginModel;

/**
 * @param array $params
 * @param Smarty $smarty
 *
 * @return string
 */
function smarty_compiler_toegang($params, Smarty &$smarty) {
	return '<?php if (' . LoginModel::class . '::mag(' . $params[0] . ')) { ?>';
}
/* vim: set expandtab: */
