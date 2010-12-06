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
function makepasswd($pass) {
	$salt = mhash_keygen_s2k(MHASH_SHA1, $pass, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
	return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass.$salt).$salt);
}
function email_like($email) {
	return preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $email);
}

function url_like($url) {
	#					  http://		  user:pass@
	return preg_match('#^(([a-zA-z]{1,6}\://)(\w+:\w+@)?' .
	#	f			oo.bar.   org	   :80
		'([a-zA-Z0-9]([-\w]+\.)+(\w{2,5}))(:\d{1,5})?)?' .
	#	/path	   ?file=http://foo:bar@w00t.l33t.h4x0rz/
		'(/~)?[-\w./]*([-@()\#?/&;:+,._\w= ]+)?$#', $url);
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
		 [\x09\x0A\x0D\x20-\x7E]			# ASCII
	   | [\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}	# planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}		  # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}	# plane 16
   )*$%xs', $string);

} // function is_utf8
function opConfide() {
	return ( isset($_SERVER['REMOTE_ADDR']) and defined('CONFIDE_IP') and in_array($_SERVER['REMOTE_ADDR'],explode(':',CONFIDE_IP)) );
}
function isFeut(){
	return isset($_SERVER['REMOTE_ADDR']) and defined('FEUT_IP') and $_SERVER['REMOTE_ADDR']==FEUT_IP;
}
function getDateTime(){
	return date('Y-m-d H:i:s');
}
// function isGeldigeDatum
// pre: $datum is een string die begint met 'yyyy-mm-dd'. Wat daarna komt maakt niet uit.
// post: true is teruggegeven als de datum in de string geldig is (volgens checkdate()). Anders is false teruggegeven.
function isGeldigeDatum($datum){
	// De string opdelen en checken of er genoeg delen zijn.
	$delen=explode('-', $datum);
	if(count($delen)<3){ return false; }

	// Checken of we geldige strings hebben, voordat we ze casten naar ints.
	$jaar=$delen[0];
	if(!is_numeric($jaar) OR strlen($jaar)!=4){ return false; }
	$maand=$delen[1];
	if(!is_numeric($maand) OR strlen($maand)!=2){ return false; }
	$dag=substr($delen[2], 0, 2); // Alleen de eerste twee karakters pakken.
	if(!is_numeric($dag) OR strlen($dag)!=2){ return false; }

	// De strings casten naar ints en de datum laten checken.
	return checkdate((int)$maand, (int)$dag, (int)$jaar);
}
function pr($sString, $cssID='pubcie_debug'){
	$admin=array('145.94.61.229', '145.94.59.158', '192.168.16.101', '127.0.0.1');
	if(in_array($_SERVER['REMOTE_ADDR'], $admin)){
		echo '<pre id="'.$cssID.'">'.print_r($sString, true).'</pre>';
	}
}
function namen2uid($sNamen, $filter='leden'){

	$return=array();
	$sNamen=trim($sNamen);
	$sNamen=str_replace(array(', ', "\r\n", "\n"), ',', $sNamen);

	$aNamen=explode(',', $sNamen);
	$return=false;
	foreach($aNamen as $sNaam){
		$aNaamOpties=array();
		$aZoekNamen=Zoeker::zoekLeden($sNaam, 'naam', 'alle', 'achternaam', $filter);
		if(count($aZoekNamen)==1){
			$naam=$aZoekNamen[0]['voornaam'].' ';
			if(trim($aZoekNamen[0]['tussenvoegsel'])!=''){ $naam.=$aZoekNamen[0]['tussenvoegsel'].' '; }
			$naam.=$aZoekNamen[0]['achternaam'];
			$return[]=array('uid' => $aZoekNamen[0]['uid'], 'naam' => $naam );
		}elseif(count($aZoekNamen)==0){

		}else{
			//geen enkelvoudige match, dan een array teruggeven
			foreach($aZoekNamen as $aZoekNaam){
				$lid=LidCache::getLid($aZoekNaam['uid']);
				$aNaamOpties[]=array(
					'uid' => $aZoekNaam['uid'],
					'naam' => $lid->getNaam());
			}
			$return[]['naamOpties']=$aNaamOpties;
		}
	}
	return $return;
}
//$type: null, post, get (gebruik om alléén post of alléén get te checken)
function getOrPost($key, $type = null, $default = ''){
	if ($type != 'get' && isset($_POST[$key])){
		return $_POST[$key];
	}elseif ($type != 'post' && isset($_GET[$key])){
		return $_GET[$key];
	}else{
		return $default;
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
/*
 * Geeft een array terug met alleen de opgegeven keys.
 *
 * @param	$in		ééndimensionele array.
 * @param	$keys	Keys die uit de in-array gereturned moeten worden.
 * @return			Array met alleen keys die in $keys zitten
 *
 * @author			Jan Pieter Waagmeester (jieter@jpwaag.com)
 */
function array_get_keys($in, $keys){
	if(!is_array($in) OR !is_array($keys)){
		return false;
	}
	$out=array();
	foreach($keys as $key){
		if(isset($in[$key])){
			$out[$key]=$in[$key];
		}
	}
	return $out;
}
function reldate($datum){
	$nu=time();
	$moment=strtotime($datum);
	$verschil=$nu-$moment;
	if($verschil<=60){
		$return='<em>'.$verschil.' ';
		if($verschil==1) {$return.='seconde';}else{$return.='seconden';}
		$return.='</em> geleden';
	}elseif($verschil<=60*60){
		$return='<em>'.floor($verschil/60);
		if(floor($verschil/60)==1){	$return.=' minuut'; }else{$return.=' minuten'; }
		$return.='</em> geleden';
	}elseif($verschil<=(60*60*4)){
		$return='<em>'.floor($verschil/(60*60)).' uur</em> geleden';
	}elseif(date('Y-m-d')==date('Y-m-d', $moment)){
		$return='vandaag om '.date("G:i", $moment);
	}elseif(date('Y-m-d', $moment)==date('Y-m-d', strtotime('1 day ago'))){
		$return='gisteren om '.date("G:i", $moment);
	}else{
		$return=date("G:i j-n-Y", $moment);
	}
	return $return;
}

function internationalizePhonenumber($phonenumber, $prefix='+31'){
	$number=str_replace(array(' ', '-'), '', $phonenumber);
	if($number[0]==0){
		return $prefix.substr($number, 1);
	}else{
		return $phonenumber;
	}
}

/* plaatje vierkant croppen.
 * http://abeautifulsite.net/blog/2009/08/cropping-an-image-to-make-square-thumbnails-in-php/
 */
function square_crop($src_image, $dest_image, $thumb_size = 64, $jpg_quality = 90) {

	// Get dimensions of existing image
	$image = getimagesize($src_image);

	// Check for valid dimensions
	if( $image[0] <= 0 || $image[1] <= 0 ) return false;

	// Determine format from MIME-Type
	$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

	// Import image
	switch( $image['format'] ) {
		case 'jpg':
		case 'jpeg':
			$image_data = imagecreatefromjpeg($src_image);
		break;
		case 'png':
			$image_data = imagecreatefrompng($src_image);
		break;
		case 'gif':
			$image_data = imagecreatefromgif($src_image);
		break;
		default:
			// Unsupported format
			return false;
		break;
	}

	// Verify import
	if( $image_data == false ) return false;

	// Calculate measurements
	if( $image[0] > $image[1] ) {
		// For landscape images
		$x_offset = ($image[0] - $image[1]) / 2;
		$y_offset = 0;
		$square_size = $image[0] - ($x_offset * 2);
	} else {
		// For portrait and square images
		$x_offset = 0;
		$y_offset = ($image[1] - $image[0]) / 2;
		$square_size = $image[1] - ($y_offset * 2);
	}

	// Resize and crop
	$canvas = imagecreatetruecolor($thumb_size, $thumb_size);
	if( imagecopyresampled(
		$canvas,
		$image_data,
		0,
		0,
		$x_offset,
		$y_offset,
		$thumb_size,
		$thumb_size,
		$square_size,
		$square_size
	)) {

		// Create thumbnail
		switch( strtolower(preg_replace('/^.*\./', '', $dest_image)) ) {
			case 'jpg':
			case 'jpeg':
				$return=imagejpeg($canvas, $dest_image, $jpg_quality);
			break;
			case 'png':
				$return=imagepng($canvas, $dest_image);
			break;
			case 'gif':
				$return=imagegif($canvas, $dest_image);
			break;
			default:
				// Unsupported format
				$return=false;
			break;
		}

		//plaatje ook voor de webserver leesbaar maken.
		if($return){
			chmod($dest_image,  0644);
		}
		return $return;
	} else {
		return false;
	}
}
function format_filesize($size) {
	$units = array(' B', ' KB', ' MB', ' GB', ' TB');
	for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
	return round($size, 2).$units[$i];
}
?>
