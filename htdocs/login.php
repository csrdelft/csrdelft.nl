<?php

# login.php

# instellingen & rommeltjes
require_once('include.config.php');


# ok_url en user/pass invoer checken
if (isset($_POST['url']) and preg_match("/^[-\w?&=.\/]+$/", $_POST['url'])
	and isset($_POST['user']) and isset($_POST['pass'])
	/* and $_POST['user'] != '' and $_POST['pass'] != '' */ ) {

	$checkip = isset($_POST['checkip']) and $_POST['checkip'] == 'true';
	
	if ($lid->login(strval($_POST['user']), strval($_POST['pass']), $checkip)) {
		header("Location: ". CSR_SERVER . $_POST['url']);
	} else {
		$_SESSION['auth_error'] = "Ongeldige gebruiker of wachtwoord";
		header("Location: ". CSR_SERVER . $_POST['url']);
	}
}

exit;

?>
