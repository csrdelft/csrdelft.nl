<?php

/**
 * pasfotos.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 * Zet een stel uid's om in pasfoto's
 */
require_once 'configuratie.include.php';

if (isset($_GET['string'])) {
	if (LoginLid::mag('P_LEDEN_READ')) {
		$string = trim(urldecode($_GET['string']));
		$uids = explode(',', $string);
		$link = !isset($_GET['link']);

		echo '<div class="pasfotomatrix">';
		foreach ($uids as $uid) {
			Lid::naamLink($uid, 'pasfoto', ($link ? 'link' : 'plain'));
		}
		echo '</div>';
	} else {
		echo 'niet voldoende rechten';
	}
} elseif (isset($_GET['image'])) {
	if (isset($_GET['uid'])) {
		$uid = $_GET['uid'];
	} else {
		$uid = LoginLid::instance()->getUid();
	}
	//cache-dingen regelen: 6 dagen vooruit.
	header('Pragma: public');
	header('Cache-Control: maxage=21000');
	header('Expires: ' . gmdate('D, d M Y H:i:s', (time() + 21000)) . ' GMT');

	//we geven de pasfoto voor het gegeven uid direct aan de browser, als we lid-leesrechten hebben
	if (Lid::isValidUid($uid) AND LoginLid::mag('P_LEDEN_READ')) {
		$lid = LidCache::getLid($uid);
		$types = array('jpg', 'png', 'gif');

		//TEMP: Voor senatorenopdracht 2014.
		$pasfoto = $lid->getDuckfotoPath();
		//EINDE TEMP

		if (in_array(substr($pasfoto, -3), $types)) {
			header('Content-type: image/' . substr($pasfoto, -3));
		} else { //assumption is the mother of all...
			header('Content-type: image/jpeg');
		}
		echo file_get_contents(PICS_PATH . $pasfoto);
	} else {
		header('Content-type: image/jpeg');
		echo file_get_contents(PICS_PATH . '/pasfoto/geen-foto.jpg');
	}
}
