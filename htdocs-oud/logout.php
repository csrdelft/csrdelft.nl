<?php

# logout.php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
$lid->logout();

# url checken
if (isset($_POST['url']) and preg_match("/^[-\w?&=.\/]+$/", $_POST['url']))
		header("Location: http://csrdelft.nl{$_POST['url']}");

exit;

?>
