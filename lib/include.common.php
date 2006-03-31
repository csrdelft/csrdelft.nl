<?php

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

# http://nl.php.net/manual/en/function.ip2long.php
# User Contributed Notes
function matchCIDR($addr, $cidr) {
   list($ip, $mask) = explode('/', $cidr);
   $mask = 0xffffffff << (32 - $mask);
   return ((ip2long($addr) & $mask) == (ip2long($ip) & $mask));
}

function email_like($email) {
	$regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
	if ( !preg_match($regexp, $email) ) return false;
	return true;
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

function opConfide() {
	if (isset($_SERVER['REMOTE_ADDR']) and in_array($_SERVER['REMOTE_ADDR'],explode(':',CONFIDE_IP))) return true;
	else return false;
}
function getDateTime(){
	return date('Y-m-d H:i:s');
}
?>
