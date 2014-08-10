<?php

# login.php

require_once 'configuratie.include.php';

$return = Array("loginSucces" => false);

# ok_url en user/pass invoer checken
if (isset($_GET['url']) and preg_match("/^[-\w?&=.\/]+$/", $_GET['url'])
		and isset($_GET['user']) and isset($_GET['pass'])) {

	$checkip = isset($_GET['checkip']) and $_GET['checkip'] == 'true';

	if (LoginLid::instance()->login(strval($_GET['user']), strval($_GET['pass']), $checkip)) {
		$return["loginSucces"] = true;
	} else {
		if ($_GET['user'] == 'aquifer' OR $_GET['user'] == '0801') {
			$_SESSION['auth_error'] = '
				<span style="position: fixed; right: 300px; width: 300px; border: 1px solid red; padding: 2px; background-color: white;">Het spijt ons heel erg, maar met de gegeven
				inloggegevens is het niet mogelijk in te loggen. Zou het
				eventueel mogelijk zijn dat u, geheel per ongeluk, een fout heeft
				gemaakt met invoeren? In dat geval bieden wij u onze nederige
				excuses aan en vragen wij u het nog eens te proberen.</span>';
		} else {
			$_SESSION['auth_error'] = "Login gefaald!";
		}
	}
}

echo json_encode($return);
