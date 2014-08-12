<?php

require_once 'configuratie.include.php';

if (!LoginSession::mag('P_ADMIN')) {
	header('location: ' . CSR_ROOT);
	exit;
}

phpinfo();
