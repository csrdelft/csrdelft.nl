<?php
require_once 'include.config.php';
require_once 'mededelingen/class.mededeling.php';

define('MEDEDELINGEN_ROOT','actueel/mededelingen/');

if(!Mededeling::isModerator()){
	header('location: '.CSR_ROOT.'/actueel/mededelingen');
	$_SESSION['mededelingen_foutmelding']='U heeft daar niets te zoeken.';
	exit;
}

if(isset($_GET['mededelingId']) AND is_numeric($_GET['mededelingId']) AND $_GET['mededelingId']>0){
	try{
		$mededeling=new Mededeling((int)$_GET['mededelingId']);
	} catch (Exception $e) {
		header('location: '.CSR_ROOT.MEDEDELINGEN_ROOT);
		$_SESSION['melding']='Mededeling met id '.(int)$_GET['mededelingId'].' bestaat niet.';
	}
	header('location: '.CSR_ROOT.MEDEDELINGEN_ROOT.$mededeling->getId());
	$mededeling->keurGoed();
	$_SESSION['melding']='Mededeling is nu voor iedereen zichtbaar.';
}else{
	header('location: '.CSR_ROOT.MEDEDELINGEN_ROOT);
	$_SESSION['melding']='Geen mededelingId gezet.';
}

?>
