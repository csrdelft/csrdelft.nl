<?php
require_once('include.config.php');

switch($_GET['actie']){
	case 'su':
		if(!$loginlid->hasPermission('P_ADMIN')){
			throw new Exception('Geen su-rechten!');
		}
		$loginlid->su($_GET['uid']);
		header('Location: '.CSR_ROOT);
		break;
	
	case 'endSu':
		if(!$loginlid->isSued()){
			throw new Exception('Niet gesued!');
		}		
		$loginlid->endSu();
		header('Location: '.CSR_ROOT);
		break;
}
?>
