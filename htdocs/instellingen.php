<?php
/*
 * instellingen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */

require_once 'configuratie.include.php';
require_once 'lid/instellingencontent.class.php';

if(!$loginlid->hasPermission('P_LOGGED_IN')){
	header('location: '.CSR_ROOT);
	exit;
}
//we lopen de post-array langs, als daar een veld met een instellingnaam in zit
//stoppen we die in de instellingketzor.
foreach($_POST as $key => $value){
	if(LidInstellingen::has($key)){
		LidInstellingen::set($key, $value);
	}
}
//als het in het profiel opgeslagen moet worden doen we dat.
if(isset($_POST['save'])){
	LidInstellingen::save();
}

$main=new Csrdelft(new InstellingenContent());

$main->view();

?>
