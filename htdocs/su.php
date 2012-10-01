<?php
require_once 'configuratie.include.php';

switch($_GET['actie']){
	case 'su':
		if(!$loginlid->hasPermission('P_ADMIN')){
			setMelding('Geen su-rechten!',-1);
		}else{
			$loginlid->su($_GET['uid']);
			setMelding('U bekijkt de webstek nu als '.Lid::getNaamLinkFromUid($_GET['uid']).'!',1);
		}
		header('Location: '.CSR_ROOT);
		break;
	
	case 'endSu':
		if(!$loginlid->isSued()){
			setMelding('Niet gesued!',-1);
		}else{
			$loginlid->endSu();
			setMelding('Switch-useractie is beëindigd.',1);
		}
		header('Location: '.CSR_ROOT);
		break;
}
?>
