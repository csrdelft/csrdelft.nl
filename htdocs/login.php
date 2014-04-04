<?php

# login.php

require_once 'configuratie.include.php';

# ok_url en user/pass invoer checken
if (isset($_POST['url']) and preg_match("/^[-\w?&=.\/]+$/", $_POST['url']) and isset($_POST['user']) and isset($_POST['pass'])) {

	$checkip = isset($_POST['checkip']) and $_POST['checkip'] == 'true';

	if (LoginLid::instance()->login(strval($_POST['user']), strval($_POST['pass']), $checkip)) {
		header("Location: " . CSR_SERVER . $_POST['url']);
	} else {
		if ($_POST['user'] == 'aquifer' OR $_POST['user'] == '0801') {
			$_SESSION['auth_error'] = '
				<span style="position: fixed; right: 300px; width: 300px; border: 1px solid red; padding: 2px; background-color: white;">Het spijt ons heel erg, maar met de gegeven
				inloggegevens is het niet mogelijk in te loggen. Zou het
				eventueel mogelijk zijn dat u, geheel per ongeluk, een fout heeft
				gemaakt met invoeren? In dat geval bieden wij u onze nederige
				excuses aan en vragen wij u het nog eens te proberen.</span>';
		} else {
			$_SESSION['auth_error'] = "Login gefaald!";
		}
		header("Location: " . CSR_SERVER . $_POST['url']);
	}
} else {
	$smarty = new CsrSmarty();
	$smarty->display('login.tpl');
}