{*
// berichtID setten als dat nog niet gedaan is.
if(empty($this->_berichtID))
	$this->_berichtID = $this->_nieuws->getBelangrijksteMededelingId();

$includeVerborgen=false;
if($lid->hasPermission('P_NEWS_MOD')){ $includeVerborgen=true; }
$aBerichten=$this->_nieuws->getMessages(0, $includeVerborgen);
*}
<div class="mededelingen-overzichtlijst">

{* $this->getOverzichtLijst($aBerichten);
echo $this->getNieuwBerichtLink();
if(!is_array($aBerichten) OR empty($aBerichten)) {
	echo 'Zoals het is, zoals het was, o Civitas!<br />(Geen mededelingen gevonden dus…)<br /><br />';
}else{
	$bEersteRecord=true;
	$iHuidigeJaarWeeknummer=date('oW')+1; // Volgende week.
	foreach ($aBerichten as $aBericht) {
		$iJaarWeeknummer=date('oW', $aBericht['datum']); // De week van dit record (yyyymm)
		if($iJaarWeeknummer < $iHuidigeJaarWeeknummer){ // Indien we een andere week aan het printen zijn dan de vorige
			// Voor de eerste keer niets sluiten.
			if(!$bEersteRecord) { echo '</div>'; }
			else { $bEersteRecord=false; }
			// Nieuw blok beginnen.
			echo '<div class="mededelingenlijst-block">';
			// Even casten om de 0 ervoor weg te halen (bijvoorbeeld bij week 05).
			echo '<div class="mededelingenlijst-block-titel">Week '.(int)date('W', $aBericht['datum']).'</div>';
			$iHuidigeJaarWeeknummer = $iJaarWeeknummer;
		}
		$id='';
		$class='mededelingenlijst-item';
		if($aBericht['verborgen']=='1'){
			$class.=' verborgen-item';
		}
		if($aBericht['id']==$this->_berichtID){
			$id.='id="actief" ';
		}
		echo '<div '.$id.'class="'.$class.'">';
		if($aBericht['categorieplaatje']!=''){
			echo '<div class="mededelingenlijst-plaatje"><a href="'.NIEUWS_ROOT.$aBericht['id'].'">
				<img src="'.CSR_PICS.'nieuws/'.$aBericht['categorieplaatje'].'" width="10px" height="10px" alt="'.$aBericht['categorienaam'].'" /></a></div>';
		}
		$sDate=date('(d-m)',$aBericht['datum']);
		echo '<div class="itemtitel">'.$sDate.' <a href="'.NIEUWS_ROOT.$aBericht['id'].'">';
		echo $this->knipTekst(mb_htmlentities($aBericht['titel']), 35, 1).'</a></div>';
		echo '</div>'; // mededelingenlijst-item
	}//einde foreach bericht
	echo '</div>'; //sluit laatste block
}
echo '<br />'.$this->getNieuwBerichtLink();
*}


</div>
<div style="width: 400px; float: left;">
	<div class="nieuwsbericht">
	
	{* $this->getBericht(); *}
		<div class="nieuwsbody">
			<div class="nieuwstitel">{$selectedMededeling->getTitel()|escape:'html'}</div>
		{*//verborgen berichten aangeven, enkel bij mensen met P_NEWS_MOD
		if($aBericht['verborgen']=='1'){ echo '<em>[verborgen] </em>';	}
		*}
			<img class="nieuwsplaatje" src="{$csr_pics}nieuws/{$selectedMededeling->getPlaatje()}" width="200px" height="200px" alt="{$selectedMededeling->getPlaatje()}" />
			<i>{$selectedMededeling->getDatum()}</i>     {*date('d-m-Y H:i', *}
			<br />{$ubb->getHTML($selectedMededeling->getTekst())}<br />
		</div>
		{*
		if($this->_nieuws->isNieuwsMod()){
			<a href="'.NIEUWS_ROOT.'bewerken/'.$iBerichtID.'"><img src="'.CSR_PICS.'forum/bewerken.png'.'" alt="bewerken" /></a>
			<a href="'.NIEUWS_ROOT.'verwijderen/'.$iBerichtID.'" onclick="return confirm(\'Weet u zeker dat u dit nieuwsbericht wilt verwijderen?\')"><img src="'.CSR_PICS.'forum/verwijderen.png'.'" alt="verwijderen" /></a>
		}
		*}
			
			
	</div>
	<div id="mededelingen-top3block">
	
	{* $this->getTopBlock();
	$aBerichten=$this->_nieuws->getTop($this->_nieuws->getAantalTopBerichten());
	if(is_array($aBerichten) AND !empty($aBerichten)){
		foreach($aBerichten as $aBericht){
			$sLink = '<a href="'.NIEUWS_ROOT.$aBericht['id'].'" title="'.mb_htmlentities($aBericht['titel']).'">';
			$sResultaat.='<div class="mededeling-grotebalk">';
			$sResultaat.='<div class="plaatje">'.$sLink;
			$sResultaat.='<img src="'.CSR_PICS.'nieuws/'.$aBericht['plaatje'].'" width="70px" height="70px" alt="'.$this->knipTekst(mb_htmlentities($aBericht['titel'],10,5)).'" />';
			$sResultaat.='</a></div>';
			$sResultaat.='<div class="titel">'.$sLink.$this->knipTekst(mb_htmlentities($aBericht['titel']), 34, 1).'</a></div>';
			$sResultaat.='<div class="bericht">'.$this->knipTekst($this->ubb->getHTML($aBericht['tekst']), 46, 4).'</div>';
			$sResultaat.='</div>';
		}
	}
	*}	
			
	</div>
</div>