<?php

/*
function getVar($varname, $default = 0) {
	# het is belangrijk dat eerst POST getest wordt, en dan GET
	# er zijn functies die hiervan uitgaan. als POST niet eerst getest wordt kunnen er
	# veiligheidsgaten ontstaan doordat de invoervariabele die in bv. de [new] actie wordt
	# gebruikt gevalideerd wordt vanuit GET en vervolgens gebruikt wordt direct uit POST
	# waardoor ongecontroleerde input wordt gebruikt.
	if (isset($_POST[$varname])) return $_POST[$varname];
	if (isset($_GET[$varname])) return $_GET[$varname];
	return $default;

	#($result = $_POST[$varname]) or ($result = $_GET[$varname]) or ($result = $default);
	#return $result;
}

function phplog($service, $msg) {
	openlog($service, LOG_ODELAY, LOG_LOCAL0);
	syslog(LOG_INFO, $msg);
	closelog();
}
*/

# http://nl.php.net/manual/en/function.ip2long.php
# User Contributed Notes
function matchCIDR($addr, $cidr) {
   list($ip, $mask) = explode('/', $cidr);
   $mask = 0xffffffff << (32 - $mask);
   return ((ip2long($addr) & $mask) == (ip2long($ip) & $mask));
}

function email_like($email) {
	return preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $email);
}

//http://nl.php.net/manual/en/function.in_array.php
function array_values_in_array($needles, $haystack) {
	if(is_array($needles)){
		$valid=true;
		foreach($needles as $needle){
			if(!in_array($needle, $haystack)){
				$valid=false;	
			}
		}
		return $valid;
	}else{
		return in_array($needles, $haystack);
	}  
}
function kapStringNetjesAf(&$sTekst, $iMaxTekens){
	//test of tekst überhaupt te lang is
	if(mb_strlen($sTekst)>$iMaxTekens){
		//tekst is te lang. Afk(n)appen dan maar?
		$sRanzigAfgekort=mb_substr($sTekst, 0, $iMaxTekens);
		//controleren of er op een spatie is afgekapt.
		if($sTekst[$iMaxTekens]==' ' OR $sTekst[$iMaxTekens-1]==' '){
			//er is op een spatie afgekapt.
			$bAfgekapt=true;
			$sTekst=trim($sRanzigAfgekort);
		}else{
			//kijk waar de laatste spatie zit.
			$iSpatiePositie=mb_strrpos($sRanzigAfgekort, ' ');
			if($iSpatiePositie===false){
				//geen spatie meer aanwezig voor het afkappunt. 
				//Gewoon ranzig afkappen met puntjes dus
				$bAfgekapt=true;
				$sTekst=trim($sRanzigAfgekort);
			}else{
				//alles na laatste spatie eraf slopen.
				$sTekst=trim(mb_substr($sRanzigAfgekort, 0, $iSpatiePositie));
				$bAfgekapt=true;
			}
		}
	}else{
		$bAfgekapt=false;
	}
	return $bAfgekapt;
}
//over de hele site dezelfde htmlentities gebruiken....
function mb_htmlentities($string){
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

// Returns true if $string is valid UTF-8 and false otherwise.
function is_utf8($string) {
   
   // From http://w3.org/International/questions/qa-forms-utf-8.html
   return preg_match('%^(?:
         [\x09\x0A\x0D\x20-\x7E]            # ASCII
       | [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
       |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
       | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
       |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
       |  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
       | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
       |  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
   )*$%xs', $string);
   
} // function is_utf8

function opConfide() {
	return ( isset($_SERVER['REMOTE_ADDR']) and defined('CONFIDE_IP') and in_array($_SERVER['REMOTE_ADDR'],explode(':',CONFIDE_IP)) );
}
function getDateTime(){
	return date('Y-m-d H:i:s');
}
function pr($sString){
	if($_SERVER['REMOTE_ADDR']=='145.94.59.158'){
		echo '<pre>'.print_r($sString, true).'</pre>';
	}else{
		echo 'Er is een foutje, de webmeester is er al mee bezig...';
	}
}
function namen2uid($sNamen, $lid){
	$sNamen=trim($sNamen);
	$sNamen=str_replace(', ', ',', $sNamen);
	$aNamen=explode(',', $sNamen);

	$return=false;
	foreach($aNamen as $sNaam){
		$aZoekNamen=$lid->zoekLeden($sNaam, 'naam', 'alle', 'achternaam', 'leden');
		if(count($aZoekNamen)==1){
			$naam=$aZoekNamen[0]['voornaam'].' ';
			if(trim($aZoekNamen[0]['tussenvoegsel'])!=''){ $naam.=$aZoekNamen[0]['tussenvoegsel']; }
			$naam.=$aZoekNamen[0]['achternaam'];
			$return[]=array('uid' => $aZoekNamen[0]['uid'], 'naam' => $naam );
		}elseif(count($aZoekNamen)==0){
			
		}else{
			//geen enkelvoudige match, dan een array teruggeven
			$return[]['naamOpties']=$aZoekNamen;
		}
	}
	return $return;
}
?>
