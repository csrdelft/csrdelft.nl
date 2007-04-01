<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.maaltijdinfocontent.php
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.maaltrack.php');

class MaaltijdInfoContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_maaltrack;
	var $_maaltijd;

	### public ###

	function MaaltijdInfoContent (&$lid, &$maaltrack, &$maaltijd) {
		$this->_lid =& $lid;
		$this->_maaltrack =& $maaltrack;
		$this->_maaltijd =& $maaltijd;
	}

	function view() {
		$maaltijdinfo=new Smarty_csr();
		$maaltijdinfo->caching=false;
		
		$aMaal['id']=$this->_maaltijd->getMaalId();
		$aMaal['informatie']=$this->_maaltijd->getInfo();
		//eigenlijk is dit ook info
		$aMaal['datum']=$this->_maaltijd->getDatum();
		
				
		# is er een foutboodschap?
		$aMaal['error']=$this->_maaltrack->getError();

		
		$maaltijdinfo->assign('maaltijd', $aMaal);
		$maaltijdinfo->assign('datumFormaat', '%A %e %B'); 
		$maaltijdinfo->display('maaltijdinfo.tpl');
		
		
		

		# namen van de corveers, we kunnen niet zomaar getFullName gebruiken met een lege string,
		# omdat dan de naam van de huidige ingelogde gebruiker wordt teruggegeven
		foreach(array('tp', 'kok1','kok2','afw1','afw2','afw3') as $corveer)
			$minfo[$corveer . 'naam'] = ($minfo[$corveer] == '') ? '' : $this->_lid->getFullName($minfo[$corveer]);
		
		printf(<<<EOT

<table width="100%%" class="lijnhoktable">
<tr><td colspan="2" width="100%%" class="lijnhoktitel">Maaltijdinformatie</td></tr>
<tr>
<td width="50%%" class="lijnhoktekst">


	<table cellpadding="0" cellspacing="5" marginwidth="0" marginheight="0" border="0" align="left" width="100%%">

	<tr><td valign="top"><b>Datum/Tijd:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Menu/Omschrijving:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Aantal Inschr:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Max Inschr:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Abosoort:</b></td><td valign="top">%s</td></tr>

	</table>

</td>
<td width="50%%" class="lijnhoktekst">

	<table cellpadding="0" cellspacing="5" marginwidth="0" marginheight="0" border="0" align="left" width="100%%">

	<tr><td valign="top"><b>Tafelpraeses:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Kok 1:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Kok 2:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Afw 1:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Afw 2:</b></td><td valign="top">%s</td></tr>
	<tr><td valign="top"><b>Afw 3:</b></td><td valign="top">%s</td></tr>

	</table>
	
</td>
</tr>

EOT
			,
			strftime('%a %e %b %H:%M', $minfo['datum']),
			mb_htmlentities($minfo['tekst']),
			$minfo['aantal'],
			$minfo['max'],
			mb_htmlentities($this->_maaltrack->getAboTekst($minfo['abosoort'])),
			$minfo['tpnaam'],
			$minfo['kok1naam'],
			$minfo['kok2naam'],
			$minfo['afw1naam'],
			$minfo['afw2naam'],
			$minfo['afw3naam']
		);

		# link maken naar maaltijdlijst als men moderator is of op Confide is
		#if($this->_lid->hasPermission('P_MAAL_MOD') or opConfide()){
		#	$m['tekst']='<a href="/leden/maaltijdlijst.php?maalid='.$m['id'].'">'.$m['tekst'].'</a>';
		#}
		
		print(<<<EOT

</table>
EOT
		);

	}
}

?>
