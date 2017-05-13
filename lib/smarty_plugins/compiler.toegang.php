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
function smarty_compiler_toegang($params, Smarty &$smarty)
{
    return "<?php if (\CsrDelft\model\security\LoginModel::mag($params[0])) { ?>";
}
/* vim: set expandtab: */
