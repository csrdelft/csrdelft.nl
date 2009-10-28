#!/usr/bin/php
<?php 
error_reporting(E_ALL);


session_id('maaltrack-cli');

# instellingen & rommeltjes
chdir('../lib/');
require_once 'include.config.php';

require_once 'maaltijden/class.maaltrack.php';
$maaltrack = new MaalTrack();

$maaltrack->corveeAutoMailer();

?>
