<?php

require_once 'configuratie.include.php';
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\security\LoginModel;

if (!LoginModel::mag('P_LEDEN_READ')) {
	header('HTTP/1.0 403 Forbidden');
	echo "Niet toegestaan";
} else {
	$image = new Afbeelding(safe_combine_path(PASFOTO_PATH, $_GET['filename']));
	if ($image == null) {
		header('HTTP/1.0 403 Forbidden');
		echo "Niet toegestaan";
	}
	$image->serve();
}