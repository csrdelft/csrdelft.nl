<?php

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	invokeRefresh(CSR_ROOT);
}

phpinfo();
