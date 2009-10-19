<?php
/*
 * pasfotos.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Zet een stel uid's om in pasfoto's
 */
require_once 'include.config.php';

if(isset($_GET['string'])){
	if($loginlid->hasPermission('P_LEDEN_READ')){
		$string=trim(urldecode($_GET['string']));
		$uids=explode(',', $string);
		$link=!isset($_GET['link']);

		echo '<div class="pasfotomatrix">';
		foreach($uids as $uid){
			if(Lid::isValidUid($uid)){
				$lid=LidCache::getLid($uid);
				if($lid instanceof Lid){
					if($link){
						echo '<a href="/communicatie/profiel/'.$uid.'" title="'.$lid->getNaam().'">';
					}
					echo $lid->getPasfoto(true);
					if($link){ echo '</a>'; }
				}
			}
		}
		echo '</div>';
	}else{
		echo 'niet voldoende rechten';
	}	
}elseif(isset($_GET['uid'], $_GET['image'])){
	//we geven de pasfoto voor het gegeven uid direct aan de browser, als we lid-leesrechten hebben
	if(Lid::isValidUid($_GET['uid']) AND $loginlid->hasPermission('P_LEDEN_READ')){
		$lid=LidCache::getLid($_GET['uid']);
		$types=array('png', 'gif', 'jpg');
		if(in_array(substr($lid->getPasfoto(false), -3), $types)){
			header('Content-type: image/'.substr($lid->getPasfoto(false), -3));
		}else{
			header('Content-type: image/jpg');
		}
		echo file_get_contents($lid->getPasfoto(false));
	}else{
		header('Content-type: image/jpeg');
		echo file_get_contents(PICS_PATH.'/pasfoto/geen-foto.jpg');
	}
}
?>

