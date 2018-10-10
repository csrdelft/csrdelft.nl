<?php
define('HTTP_REFERER', '');
define('REQUEST_URI', '');
define('MODE', '');

include 'defines.defaults.php';
include 'common/common.functions.php';
include 'common/common.view.functions.php';
include 'autoload.php';

foreach (glob("lib/smarty_plugins/*.php") as $filename)
{
	include $filename;
}
?>
