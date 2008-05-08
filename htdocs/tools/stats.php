<?php
error_reporting(E_ALL);


require_once('include.config.php');


if(!$lid->hasPermission('P_ADMIN')){
	header('location: '.CSR_ROOT);
	exit;
}
	
$pagina=new csrdelft(new stats());
$pagina->view();

class stats{

	var $_db;
	var $_lid;
	function stats(){
		$this->_lid=Lid::get_lid();
		$this->_db=MySql::get_MySql();
	}
	function view(){
		$lid=$this->_lid;
		$db=$this->_db;	
		if(isset($_GET['uid'])){
			$this->uidLog($db->escape($_GET['uid']), $db, $lid);
		}elseif(isset($_GET['ip'])){
			$this->ipLog($db->escape($_GET['ip']), $db, $lid);
		}else{
			$this->hoofdLog($db, $lid);
		}
	}

	function hoofdLog($db, $lid){
		$sLogQuery="
			SELECT 
				log.uid AS uid,  moment,
				voornaam, tussenvoegsel, achternaam, 
				ip, locatie, url, referer, useragent 
			FROM 
				log 
			INNER JOIN 
				lid ON(log.uid=lid.uid) 
			WHERE 1 ";
		if(isset($_GET['sjaars'])){
			$sLogQuery.="AND status='S_NOVIET' ";
		}
			$sLogQuery.="ORDER BY 
				moment DESC
			LIMIT
				0, 30;";
		$rLog=$db->query($sLogQuery);
		echo '<table class="forumtable"><tr><td class="forumhoofd">tijd</td><td class="forumhoofd">Naam</td><td class="forumhoofd">hostnaam</td><td class="forumhoofd">url</td>';
		echo '<td class="forumhoofd">useragent</td><td class="forumhoofd">referer</td></tr>';
		while($aLogRegel=$db->next($rLog)){
			$naam=$aLogRegel['voornaam'];
			if($aLogRegel['tussenvoegsel']!=''){ $naam.=' '.$aLogRegel['tussenvoegsel']; }
			$naam.=' '.$aLogRegel['achternaam'];
			
			echo '<tr><td class="forumtitel">'.date('D H:i', strtotime($aLogRegel['moment'])).'</td>';
			echo '<td class="forumtitel" ><a href="?uid='.htmlspecialchars($aLogRegel['uid']).'">+</a> <a href="/leden/communicatie/profiel/'.htmlspecialchars($aLogRegel['uid']).'" target="_blank">'.$naam.'</a></td>';
			echo '<td class="forumtitel"><a href="?ip='.htmlspecialchars($aLogRegel['ip']).'">+</a> 
				'.gethostbyaddr($aLogRegel['ip']).' <strong>('.$aLogRegel['locatie'].')</strong></td>';
			echo '<td class="forumtitel" ';
			if(preg_match('/toevoegen/', $aLogRegel['url'])){ echo 'style="background-color: yellow;"'; 
			}elseif(preg_match('/maak-stemming/', $aLogRegel['url'])){ echo 'style="background-color: #CC0000;"';
			}elseif(preg_match('/zoeken/', $aLogRegel['url'])){ echo 'style="background-color: #33FF99;"';}
			echo '><a href="http://csrdelft.nl'.$aLogRegel['url'].'" target="_blank">'.$aLogRegel['url'].'</a></td>';
			echo '<td class="forumtitel">'.$aLogRegel['useragent'].'</td>';
			if($aLogRegel['referer']==''){
				echo '<td class="forumtitel">-</td>';
			}else{
				if(!preg_match('/http\:\/\/csrdelft.nl/', $aLogRegel['referer'])){
					if(preg_match('/google/i', $aLogRegel['referer'])){
						$iQpos=2+strpos($aLogRegel['referer'], 'q=');
						$iLengte=strpos($aLogRegel['referer'], '&')-$iQpos-3;
						
						$fragment=urldecode(substr($aLogRegel['referer'], $iQpos, $iLengte));
						echo '<td class="forumtitel">google:<br /><a href="'.$aLogRegel['referer'].'" target="_blank">'.$fragment.'</a><td>';
					}else{
						echo '<td class="forumtitel"><a href="'.$aLogRegel['referer'].'" target="_blank">'.$aLogRegel['referer'].'</a><td>';
					}
				}else{
					echo '<td class="forumtitel">intern</td>';
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}
	function uidLog($uid, $db, $lid){
		$sLogQuery="
			SELECT 
				log.uid AS uid, moment,
				voornaam, tussenvoegsel, achternaam, 
				ip, locatie, url, referer, useragent
			FROM 
				log 
			INNER JOIN 
				lid ON(log.uid=lid.uid) 
			WHERE
				log.uid='".$uid."'
			ORDER BY 
				moment DESC
			LIMIT
				0, 30;";
		$rLog=$db->query($sLogQuery);
		echo 'Laatste bezoeken van <strong>'.$lid->getCivitasName($uid).'</strong>';
		echo '<table class="forumtable"><tr><td class="forumhoofd">tijd</td><td class="forumhoofd">hostnaam</td><td class="forumhoofd">url</td>';
		echo '<td class="forumhoofd">useragent</td><td class="forumhoofd">referer</td></tr>';
		while($aLogRegel=$db->next($rLog)){
			echo '<tr><td class="forumtitel">'.date('D H:i', strtotime($aLogRegel['moment'])).'</td>';
			echo '<td class="forumtitel"><a href="?ip='.htmlspecialchars($aLogRegel['ip']).'">+</a> ';
			echo gethostbyaddr($aLogRegel['ip']).' <strong>('.$aLogRegel['locatie'].')</strong></td>';
			echo '<td class="forumtitel"><a href="http://csrdelft.nl'.$aLogRegel['url'].'" target="_blank">'.$aLogRegel['url'].'</a></td>';
			echo '<td class="forumtitel">'.$aLogRegel['useragent'].'</td>';
			if($aLogRegel['referer']==''){
				echo '<td class="forumtitel">-</td>';
			}else{
				if(!preg_match('/http\:\/\/csrdelft.nl/', $aLogRegel['referer'])){
					if(preg_match('/google/i', $aLogRegel['referer'])){
						$iQpos=2+strpos($aLogRegel['referer'], 'q=');
						$iLengte=strpos($aLogRegel['referer'], '&')-$iQpos-3;
						
						$fragment=urldecode(substr($aLogRegel['referer'], $iQpos, $iLengte));
						echo '<td class="forumtitel">google:<br /><a href="'.$aLogRegel['referer'].'" target="_blank">'.$fragment.'</a><td>';
					}else{
						echo '<td class="forumtitel"><a href="'.$aLogRegel['referer'].'" target="_blank">'.$aLogRegel['referer'].'</a><td>';
					}
				}else{
					echo '<td class="forumtitel">intern</td>';
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}
	function ipLog($ip, $db, $lid){
		$sLogQuery="
			SELECT 
				log.uid AS uid, moment, 
				voornaam, tussenvoegsel, achternaam, 
				ip, locatie, url, referer, useragent
			FROM 
				log 
			INNER JOIN 
				lid ON(log.uid=lid.uid) 
			WHERE
				log.ip='".$ip."'
			ORDER BY 
				moment DESC
			LIMIT
				0, 30;";
		$rLog=$db->query($sLogQuery);
		echo 'Laatste bezoeken van het ip <strong>'.gethostbyaddr($_GET['ip']).'</strong>';
		echo '<table class="forumtable"><tr><td class="forumhoofd">moment</td><td class="forumhoofd">naam</td><td class="forumhoofd">url</td>';
		echo '<td class="forumhoofd">useragent</td><td class="forumhoofd">referer</td></tr>';
		while($aLogRegel=$db->next($rLog)){
			
			$naam=$aLogRegel['voornaam'];
			if($aLogRegel['tussenvoegsel']!=''){ $naam.=' '.$aLogRegel['tussenvoegsel']; }
			$naam.=' '.$aLogRegel['achternaam'];
			echo '<tr>';
			echo '<td class="forumtitel">'.date('D H:i', strtotime($aLogRegel['moment'])).'</td>';
			echo '<td class="forumtitel"><a href="?uid='.htmlspecialchars($aLogRegel['uid']).'">+</a> <a href="/leden/communicatie/profiel/'.htmlspecialchars($aLogRegel['uid']).'" target="_blank">'.$naam.'</a></td>';
			echo '<td class="forumtitel"><a href="http://csrdelft.nl'.$aLogRegel['url'].'" target="_blank">'.$aLogRegel['url'].'</a></td>';
			echo '<td class="forumtitel">'.$aLogRegel['useragent'].'</td>';
			if($aLogRegel['referer']==''){
				echo '<td class="forumtitel">-</td>';
			}else{
				if(!preg_match('/http\:\/\/csrdelft.nl/', $aLogRegel['referer'])){
					if(preg_match('/google/i', $aLogRegel['referer'])){
						$iQpos=2+strpos($aLogRegel['referer'], 'q=');
						$iLengte=strpos($aLogRegel['referer'], '&')-$iQpos-3;
						
						$fragment=urldecode(substr($aLogRegel['referer'], $iQpos, $iLengte));
						echo '<td class="forumtitel">google:<br /><a href="'.$aLogRegel['referer'].'" target="_blank">'.$fragment.'</a><td>';
					}else{
						echo '<td class="forumtitel"><a href="'.$aLogRegel['referer'].'" target="_blank">'.$aLogRegel['referer'].'</a><td>';
					}
				}else{
					echo '<td class="forumtitel">intern</td>';
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}
}// einde stats klasse
?>
