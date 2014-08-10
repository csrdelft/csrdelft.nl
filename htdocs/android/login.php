<?php

# Android login.php

require_once 'configuratie.include.php';

# user/pass invoer checken
if (isset($_POST['user']) and isset($_POST['pass'])) {

	if (LoginLid::instance()->login(strval($_POST['user']), strval($_POST['pass']), false)) { // CheckIp on false
		echo 'true';
	} else {

		echo 'false';
	}
}
