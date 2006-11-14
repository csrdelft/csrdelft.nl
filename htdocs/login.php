<?php

# login.php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


# ok_url en user/pass invoer checken
if (isset($_POST['url']) and preg_match("/^[-\w?&=.\/]+$/", $_POST['url'])
	and isset($_POST['user']) and isset($_POST['pass'])
	/* and $_POST['user'] != '' and $_POST['pass'] != '' */ ) {

	if ($lid->login(strval($_POST['user']), strval($_POST['pass']))) {
		header("Location: ". CSR_SERVER . $_POST['url']);
	} else {
		$_SESSION['auth_error'] = "Ongeldige gebruiker of wachtwoord";
		header("Location: ". CSR_SERVER . $_POST['url']);
	}
}
	echo $_POST['url'];
exit;

?>
