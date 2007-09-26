<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# include.common.php
# -------------------------------------------------------------------


// http://nl.php.net/manual/en/function.ip2long.php
// User Contributed Notes
function matchCIDR($addr, $cidr) {
   list($ip, $mask) = explode('/', $cidr);
   $bitmask = ($mask != 0) ? 0xffffffff >> (32 - $mask) : 0x00000000;
   return ((ip2long($addr) & $bitmask) == (ip2long($ip) & $bitmask));
}

function email_like($email) {
	return preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $email);
}

function url_like($url) {
    #                      http://          user:pass@
    return preg_match('#^((ht|f)tp(s?)\://)(\w+:\w+@)?' .
    #    f            oo.bar.   org       :80                
        '([a-zA-Z0-9]([-\w]+\.)+(\w{2,5}))(:\d{1,5})?' .
    #    /path       ?foo=bar      &bar=baz
        '([-\w./]*)?((\?\w+=\w+)?(&\w+=\w+)*)?$#', $url);
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
function naam($voornaam, $achternaam, $tussenvoegsel){
	$naam=$voornaam.' ';
	if($tussenvoegsel!='') $naam.=$tussenvoegsel.' ';
	$naam.=$achternaam;
	return $naam;
}
function opConfide() {
	return ( isset($_SERVER['REMOTE_ADDR']) and defined('CONFIDE_IP') and in_array($_SERVER['REMOTE_ADDR'],explode(':',CONFIDE_IP)) );
}
function isFeut(){
	return isset($_SERVER['REMOTE_ADDR']) and defined('FEUT_IP') and $_SERVER['REMOTE_ADDR']==FEUT_IP;
}
function getDateTime(){
	return date('Y-m-d H:i:s');
}
function pr($sString){
	$admin=array('145.94.61.229', '145.94.59.158', '192.168.16.101', '127.0.0.1');
	if(in_array($_SERVER['REMOTE_ADDR'], $admin)){
		echo '<pre id="pubcie_debug">'.print_r($sString, true).'</pre>';
	}else{
		echo 'Er is een foutje, de webmeester is er al mee bezig...';
	}
}
function namen2uid($sNamen, $lid){
	$return=array();
	$sNamen=trim($sNamen);
	$sNamen=str_replace(array(', ', "\r\n", "\n"), ',', $sNamen);
	
	$aNamen=explode(',', $sNamen);
	$return=false;
	foreach($aNamen as $sNaam){
		$aNaamOpties=array();
		$aZoekNamen=$lid->zoekLeden($sNaam, 'naam', 'alle', 'achternaam', 'leden');
		if(count($aZoekNamen)==1){
			$naam=$aZoekNamen[0]['voornaam'].' ';
			if(trim($aZoekNamen[0]['tussenvoegsel'])!=''){ $naam.=$aZoekNamen[0]['tussenvoegsel'].' '; }
			$naam.=$aZoekNamen[0]['achternaam'];
			$return[]=array('uid' => $aZoekNamen[0]['uid'], 'naam' => $naam );
		}elseif(count($aZoekNamen)==0){
			
		}else{
			//geen enkelvoudige match, dan een array teruggeven
			foreach($aZoekNamen as $aZoekNaam){
				$aNaamOpties[]=array(
					'uid' => $aZoekNaam['uid'], 
					'naam' => naam($aZoekNaam['voornaam'], $aZoekNaam['achternaam'], $aZoekNaam['tussenvoegsel']) );
			}
			$return[]['naamOpties']=$aNaamOpties;
		}
	}
	return $return;
}
function getOrPost($key){
	if (isset($_POST[$key])){
		return $_POST[$key];
	}elseif (isset($_GET[$key])){
		return $_GET[$key];
	}else{
		return '';
	}
}
function sort_achternaam_uid($a, $b) {
	//sorteer op achternaam ASC, uid DESC
	$vals = array('achternaam' => 'ASC', 'uid' => 'DESC');
	while(list($key, $val) = each($vals)) {
	  if($val == 'DESC') {
	    if($a[$key] > $b[$key]){ return -1; }
	    if($a[$key] < $b[$key]){ return 1;  }
	  }
	  if($val == 'ASC') {
	    if($a[$key] < $b[$key]){ return -1; }
	    if($a[$key] > $b[$key]){ return 1;  }
	  }
	}
}
function strNthPos($haystack, $needle, $nth = 1){
   //Fixes a null return if the position is at the beginning of input
   //It also changes all input to that of a string ^.~
   $haystack = ' '.$haystack;
   if (!strpos($haystack, $needle))
       return false;
   $offset=0;
   for($i = 1; $i < $nth; $i++)
       $offset = strpos($haystack, $needle, $offset) + 1;
   return strpos($haystack, $needle, $offset) - 1;
}
?>
