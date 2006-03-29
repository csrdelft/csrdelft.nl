<?php

# Camping CMS
# OogOpslag Internet (c)2005
# Hans van Kranenburg

# include.bbcode.php

define('IN_PHPBB', true);

# inladen bbcode files van phpbb2
require_once("functions_post.php");
require_once("lang_main.php");
require_once("bbcode.php");

# de achterliggende bbcode functies worden gebruikt door middel van deze
# wrappers. voor elke transformatie van data is er een functie beschikbaar.

# bbview wordt gebruikt als tekst die uit de database komt
# op het scherm gezet wordt.
function bbview ($message, $uid) {
	$message = htmlentities($message, ENT_COMPAT, 'UTF-8');
	//$message = htmlentities($message);
	
	$message = str_replace("\n", "<br />\n", $message);
	$message = str_replace("\r", "", $message);
	$message = bbencode_second_pass($message, $uid);
	$message =html_unconvert($message);
	return $message;
}

# bbedit wordt gebruikt als tekst in een editvak wordt afgebeeld
# stukjes uit posting.php gehaald van phpbb
function bbedit ($message, $uid) {
 	# Haal de uid-getallen uit de bbcode tags
	$message = preg_replace('/\:(([a-z0-9]:)?)' . $uid . '/s', '', $message);
	# We willen in ieder geval dubbele quotes omzetten vanwege het tekstvak...
	$message = htmlentities($message, ENT_QUOTES, 'UTF-8');
	//$message = htmlentities($message);
	$message = str_replace('<br />', "\n", $message);
	$message = unprepare_message($message);
	return $message;
}

# bbsave wordt gebruikt als data in het object richting de database moet
function bbnewuid () { return make_bbcode_uid(); }
function bbsave ($message, $uid, $db) {
	# uid is een nieuwe bbcode uid
	$message = prepare_message($message, false, true, false, $uid);
	return mysql_real_escape_string($message, $db);
}

# bbprevies is voor tekst die in het object zit en afgebeeld wordt
function bbpreview ($message) {
    #$message = bbencode_first_pass($message, 0);
	$message = prepare_message($message, false, true, false, 1);
	$message = str_replace("\n", "<br>", $message);
    $message = bbencode_second_pass($message, 1);
 	return $message;
}
function html_unconvert($string){
	$toConvert=array('&amp;lt;', 	'&amp;gt;', '&amp;amp;');
	$ConvertTo=array('&lt;', 			'&gt;', 		'&amp;');
	return str_replace($toConvert, $ConvertTo, $string);
}
?>
