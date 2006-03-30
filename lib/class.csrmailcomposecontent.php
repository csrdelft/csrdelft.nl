<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrmailcomposecontent.php
# -------------------------------------------------------------------
# Verzorgt het componeren van de mail
# -------------------------------------------------------------------
# Historie:
# 01-10-2005 Jieter
# . gemaakt
#

require_once ('class.mysql.php');

class Csrmailcomposecontent {
	
	var $_csrmail;
	var $_sMode;
	
	function Csrmailcomposecontent(&$csrmail, $sMode='compose'){
		$this->_csrmail=$csrmail;
		$this->_sMode=$sMode;
	}
	
	function getDraft(){
		$sTemplate=file_get_contents(LIB_PATH.'/templates/csrmail/'.CSRMAIL_TEMPLATE);
		$aBerichten=$this->_csrmail->getBerichten();
		if(is_array($aBerichten)){
			$aKopjes=$this->getKopjes($aBerichten);
			//lege array's klussen voor als er geen data is voor de categorie
			$aInhoud['bestuur']=$aInhoud['csr']=$aInhoud['overig']='';
			if(isset($aKopjes['bestuur'])){
				foreach($aKopjes['bestuur'] as $sTitel){
					$aInhoud['bestuur'].='<li>'.htmlentities($sTitel, ENT_COMPAT, 'UTF-8').'</li>'."\r\n";
				}
			}else{
				$aInhoud['bestuur']='<li>Geen berichten</li>';
			}
			if(isset($aKopjes['csr'])){
				foreach($aKopjes['csr'] as $sTitel){
					$aInhoud['csr'].='<li>'.htmlentities($sTitel, ENT_COMPAT, 'UTF-8').'</li>'."\r\n";
				}
			}
			if(isset($aKopjes['overig'])){
				foreach($aKopjes['overig'] as $sTitel){
					$aInhoud['overig'].='<li>'.htmlentities($sTitel, ENT_COMPAT, 'UTF-8').'</li>'."\r\n";
				}
			}
			reset($aBerichten);
			$sBerichten='';
			foreach($aBerichten as $aBericht){
				$sBerichten.='<h4>'.$this->process($aBericht['titel']) .'</h4>'."\r\n";
				$sBerichten.='<p>'.$this->process($aBericht['bericht']).'</p>'."\r\n";
			}   
			$sTemplate=str_replace('[inhoud-bestuur]', $aInhoud['bestuur'], $sTemplate);
			$sTemplate=str_replace('[inhoud-csr]', $aInhoud['csr'], $sTemplate);
			$sTemplate=str_replace('[inhoud-overig]', $aInhoud['overig'], $sTemplate);
			$sTemplate=str_replace('[berichten]', $sBerichten, $sTemplate);
		}else{
			$sTeplate='Geen berichten aanwezig;';
		}
		return $sTemplate;
	}
	function getHeaders(){
		setlocale (LC_ALL, 'nl_NL@euro');
		$instellingen=parse_ini_file(ETC_PATH.'/csrmail.ini');
		
		$sUitvoer="From: PubCie <pubcie@csrdelft.nl>
To: leden@csrdelft.nl
Organization: C.S.R. Delft
MIME-Version: 1.0
Content-Type: text/html; charset=utf-8
User-Agent: telnet localhost 25
X-Complaints-To: pubcie@csrdelft.nl
Approved: ".$instellingen['password']."
Subject: C.S.R. Post ".strftime('%e %B %Y')."\r\n\r\n";
		return $sUitvoer;
	}
	function getKopjes($aBerichten){
		foreach($aBerichten as $aBericht){
			//ros alles in een array, met categorie als element.
			$aKopjes[$aBericht['cat']][]=$aBericht['titel'];
		}
		return $aKopjes;
	}
	
	function process($sString){
		$sString=stripslashes($sString);
		$sString=htmlentities($sString, ENT_COMPAT, 'UTF-8');
		$sString=trim($sString);
		 $aUbbCodes=array(
      array("[b]", "<strong>"),
      array("[/b]", "</strong>"),
      array("[i]", "<em>"),
      array("[/i]", "</em>"),
      array("[u]", "<span class=\"onderlijn\">"),
      array("[/u]", "</span>"));
    foreach($aUbbCodes as $ubbCode){
    	$sString=str_replace($ubbCode[0], $ubbCode[1], $sString);
 		}
		//linkjes
		$sString=eregi_replace("\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" >\\2</a>", $sString);
		$sString=nl2br($sString);
		return $sString;
	}
	function geefBewerkKetzer(){
	
	}
	function geefBerichtMetControls($aBericht, $bFirst=false, $bLast=false){
		$sUitvoer='<tr><td>[ ';
		if(!$bFirst){
			$sUitvoer.='<a href="csrmailcompose.php?ID='.$aBericht['ID'].'&amp;omhoog">omhoog</a> | ';
		}
		if(!$bLast){
			$sUitvoer.='<a href="csrmailcompose.php?ID='.$aBericht['ID'].'&amp;omlaag">laag</a>';
		}
			
		$sUitvoer.='] <br /> [ 
			<a href="csrmailcompose.php?'.$aBericht['ID'].'&amp;bewerk">bewerken</a> | 
			<a href="csrmailcompose.php?'.$aBericht['ID'].'&amp;verwijder">verwijderen</a> ]';
		
		$sUitvoer.='</td>
			<td><h1>'.$this->process($aBericht['titel']).'</h1><p>'.$this->process($aBericht['bericht']).'</p></td>
			</tr>';
		return $sUitvoer;
	}
	function zend($sEmailAan){
		$sHeaders=$this->getHeaders();
		$sMail=$this->getDraft();
		
		$smtp=fsockopen('localhost', 25, $feut, $fout);
		echo 'Zo, mail verzenden naar '.$sEmailAan.'.<pre>';
		echo fread($smtp,1024);
		fwrite($smtp, "HELO localhost\r\n");
		echo "HELO localhost\r\n";
		echo fread($smtp, 1024);
		fwrite($smtp, "MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		echo htmlspecialchars("MAIL FROM:<pubcie@csrdelft.nl>\r\n");
		echo fread($smtp, 1024);
		fwrite($smtp, "RCPT TO:<".$sEmailAan.">\r\n");
		echo htmlspecialchars("RCPT TO:<".$sEmailAan.">\r\n");
		echo fread($smtp, 1024);
		fwrite($smtp, "DATA\r\n");
		echo htmlspecialchars("DATA\r\n");
		echo fread($smtp, 1024);
		fwrite($smtp, $sHeaders."\r\n");
		echo htmlspecialchars($sHeaders."\r\n");
		fwrite($smtp, $sMail."\r\n");
		echo htmlspecialchars("[mail hier]\r\n");
		fwrite($smtp, "\r\n.\r\n");
		echo htmlspecialchars("\r\n.\r\n");
		echo fread($smtp, 1024);
		
		echo '</pre>';
	}
	//function lees($
	function view(){
		switch($this->_sMode){
			case 'compose':
				if(isset($_GET['ID'])){
				
				}else{

				}
				$this->geefBewerkKetzer();
			break;
			case 'preview': 
				echo $this->getHeaders();
				echo $this->getDraft();
			break;
			case 'zendPubcie';
				//$this->zend('csrmail@lists.jeugdkerken.nl');
				$this->zend('pubcie@csrdelft.nl');
			break;
			default;
		}//einde switch
	}
}//einde classe
?>
