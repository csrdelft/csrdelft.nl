<?php

use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	redirect(CSR_ROOT);
}

echo AccountModel::instance()->maakWachtwoord(filter_input(INPUT_GET, 'pass', FILTER_SANITIZE_STRING));
