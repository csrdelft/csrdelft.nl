<?php
define('HTTP_REFERER', '');
define('REQUEST_URI', '');

include 'autoload.php';

foreach (glob("lib/smarty_plugins/*.php") as $filename)
{
	include $filename;
}
?>