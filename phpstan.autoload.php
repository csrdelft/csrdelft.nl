<?php
define('HTTP_REFERER', '');
define('REQUEST_URI', '');
define('MODE', '');

include 'defines.defaults.php';


foreach (glob("lib/smarty_plugins/*.php") as $filename)
{
	include $filename;
}
?>
