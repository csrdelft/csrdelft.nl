<?php

use CsrDelft\model\security\LoginModel;
use function CsrDelft\redirect;

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	redirect(CSR_ROOT);
}

phpinfo();
