<?php

use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';

if (!LoginModel::mag(P_ADMIN)) {
	redirect(CSR_ROOT);
}

phpinfo();
