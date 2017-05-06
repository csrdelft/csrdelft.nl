<?php

/**
 * pasfotos.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 * Zet een stel uid's om in pasfoto's
 */
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';

if (isset($_GET['string'])) {
	if (!LoginModel::mag('P_OUDLEDEN_READ')) {
		echo 'niet voldoende rechten';
	} else {
		$string = trim(urldecode($_GET['string']));
		$uids = explode(',', $string);
		$link = !isset($_GET['link']);
		foreach ($uids as $uid) {
			if ($link) {
				ProfielModel::getLink($uid, 'pasfoto');
			} else {
				ProfielModel::getNaam($uid, 'pasfoto');
			}
		}
	}
} elseif (isset($_GET['image'])) {
	if (isset($_GET['uid'])) {
		$uid = $_GET['uid'];
	} else {
		$uid = LoginModel::getUid();
	}
	//cache-dingen regelen: 6 dagen vooruit.
	header('Pragma: public');
	header('Cache-Control: maxage=21000');
	header('Expires: ' . gmdate('D, d M Y H:i:s', (time() + 21000)) . ' GMT');

	//we geven de pasfoto voor het gegeven uid direct aan de browser, als we lid-leesrechten hebben
	if (!LoginModel::mag('P_OUDLEDEN_READ') OR ! AccountModel::isValidUid($uid)) {
		header('Content-Type: image/jpeg');
		echo file_get_contents(PHOTOS_PATH . 'pasfoto/geen-foto.jpg');
	} else {
		$profiel = ProfielModel::get($uid);
		$types = array('jpg', 'png', 'gif');

		$pasfoto = $profiel->getPasfotoPath();

		if (in_array(substr($pasfoto, -3), $types)) {
			header('Content-Type: image/' . substr($pasfoto, -3));
		} else { //assumption is the mother of all...
			header('Content-Type: image/jpeg');
		}
		echo file_get_contents(PHOTOS_PATH . $pasfoto);
	}
}