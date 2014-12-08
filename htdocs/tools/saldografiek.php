<?php

require_once 'configuratie.include.php';
require_once 'chart/chart.php';
require_once 'lid/saldi.class.php';

/**
 * saldografiek.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 */
if (isset($_GET['uid']) AND ( Lid::isValidUid($_GET['uid']) OR $_GET['uid'] == '0000')) {
	$uid = $_GET['uid'];
	if ($uid != '0000') {
		$lid = LidCache::getLid($uid);
	}
} else {
	$lid = LoginModel::instance()->getLid();
}

$cie = 'soccie';
if (isset($_GET['maalcie'])) {
	$cie = 'maalcie';
}

$timespan = 11;
if (isset($_GET['timespan']) AND $_GET['timespan'] == (int) $_GET['timespan']) {
	$timespan = $_GET['timespan'];
}

if (LoginModel::mag('P_LEDEN_MOD,groep:' . $cie) OR LoginModel::getUid() === $uid) {
	$saldi = new Saldi($uid, $cie, $timespan);

	$chart = new chart(500, 200);

	if ($uid == '0000') {
		$chart->set_title('Som van de saldi');
	} else {
		$chart->set_title('Saldo voor ' . $lid->getNaam());
	}

	$chart->set_x_ticks($saldi->getKeys(), 'date');
	$chart->plot($saldi->getValues(), false, 'blue');

	$chart->add_legend($saldi->getNaam(), 'blue');

	$chart->set_margins(60, 10, 20, 23);
	$chart->set_labels(false, 'Saldo [euro]');
	$chart->stroke();
}
