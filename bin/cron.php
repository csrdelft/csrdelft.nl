#!/usr/bin/php
<?php
/**
 * cron.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Entry point voor uitvoeren van CRON-jobs.
 */
session_id('cron-cli');

chdir('../lib/');
require_once 'configuratie.include.php';

// Corvee herinneringen
try {
	require_once 'maalcie/model/CorveeHerinneringenModel.class.php';
	$verstuurd_errors = CorveeHerinneringenModel::stuurHerinneringen();
} catch (Exception $e) {
	#TODO: logging
}
