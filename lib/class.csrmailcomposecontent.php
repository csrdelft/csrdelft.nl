<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrmailcomposecontent.php
# -------------------------------------------------------------------
# Verzorgt het componeren van de mail
# -------------------------------------------------------------------


require_once ('class.mysql.php');

class Csrmailcomposecontent extends Csrmailcontent{
	
	function _getHeaders(){
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
Subject: C.S.R.-courant ".strftime('%e %B %Y')."\r\n\r\n";
		return $sUitvoer;
	}
	
	function getKopjes($aBerichten){
		foreach($aBerichten as $aBericht){
			//ros alles in een array, met categorie als element.
			$aKopjes[$aBericht['cat']][]=array('titel'=> $aBericht['titel'], 'ID' => $aBericht['ID']);
		}
		return $aKopjes;
	}	
	
	function zend($sEmailAan){
		$sHeaders=$this->_getHeaders();
		$sMail=$this->_getBody();
		
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

	function view(){
		echo $this->_getBody();
	}
}//einde classe
?>
