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
	
	{if $selectedMededeling!=null}		{*	If there is no Mededeling selected, there is something
											wrong, but we don't wat the user to know. *}
		<div class="nieuwsbericht">
			<div class="nieuwsbody">
				<div class="nieuwstitel">{$selectedMededeling->getTitel()|escape:'html'}</div>
			{if $selectedMededeling->isVerborgen()}
				<em>[verborgen]</em><br />
			{/if}
				<img class="nieuwsplaatje" src="{$csr_pics}nieuws/{$selectedMededeling->getPlaatje()}" width="200px" height="200px" alt="{$selectedMededeling->getPlaatje()}" />
				<i>{$selectedMededeling->getDatum()}</i><br />
				{$ubb->getHTML($selectedMededeling->getTekst())}<br />
			</div>
		{if $selectedMededeling->loginlidMagBewerken()}
			<a href="{$nieuws_root}bewerken/{$selectedMededeling->getId()}">
				<img src="{$csr_pics}forum/bewerken.png" alt="bewerken" />
			</a>
			<a href="{$nieuws_root}verwijderen/{$selectedMededeling->getId()}" onclick="return confirm('Weet u zeker dat u deze mededeling wilt verwijderen?');">
				<img src="{$csr_pics}forum/verwijderen.png" alt="verwijderen" />
			</a>
		{/if}
		</div>
	{/if}
	
	{* Het Topmost block *}
	<div id="mededelingen-top3block">
	{foreach from=$topmost item=mededeling}
		<div class="mededeling-grotebalk">
			<div class="plaatje">
				<a href="{$nieuws_root}{$mededeling->getId()}">
					<img src="{$csr_pics}nieuws/{$mededeling->getPlaatje()}" width="70px" height="70px" alt="{$mededeling->getPlaatje()|escape:'html'}" />
 				</a>
			</div>
			<div class="titel">
				<a href="{$nieuws_root}{$mededeling->getId()}">
					{$mededeling->getAfgeknipteTitel()}
 				</a>
 			</div>
			<div class="bericht">{$mededeling->getAfgeknipteTekst()}</div>
		</div>
	{/foreach}
	</div>
</div>