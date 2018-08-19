<?php
define('HTTP_REFERER', '');
define('REQUEST_URI', '');

include 'defines.include.php';
include 'common.functions.php';
include 'autoload.php';

foreach (glob("lib/smarty_plugins/*.php") as $filename)
{
	include $filename;
}
?>