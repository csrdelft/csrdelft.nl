<?php
/*
 * instellingen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */

require_once 'include.config.php';
require_once 'lid/class.instellingencontent.php';

if(!$loginlid->hasPermission('P_LOGGED_IN')){
	header('location: '.CSR_ROOT);
	exit;
}
//we lopen de post-array langs, als daar een veld met een instellingnaam in zit
//stoppen we die in de instellingketzor.
foreach($_POST as $key => $value){
	if(Instelling::has($key)){
		Instelling::set($key, $value);
	}
}
//als het in het profiel opgeslagen moet worden doen we dat.
if(isset($_POST['save'])){
	Instelling::save();
}

$main=new Csrdelft(new InstellingenContent());

$main->view();

?>
