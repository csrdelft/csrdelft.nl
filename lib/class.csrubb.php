<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrubb.php
# -------------------------------------------------------------------
#  wrapper
# -------------------------------------------------------------------

require_once('ubb/eamBBParser.class.php');

class CsrUBB extends eamBBParser{
	private $lid;

	public function __construct(){
		$this->eamBBParser();
		$this->lid=Lid::instance();
		$this->paragraph_mode = false;
	}

	function ubb_citaat($arguments=array()){
		if($this->quote_level == 0){
	    	$this->quote_level = 1;
	    	$content = $this->parseArray(array('[/citaat]'), array());
			$this->quote_level = 0;
		} else {
			$this->quote_level++;
			$delcontent = $this->parseArray(array('[/citaat]'), array());
			$this->quote_level--;
			unset($delcontent);
			$content = '...';
		}

		$text='<div class="citaatContainer"><strong>Citaat';
		if(isset($arguments['citaat']) AND $this->lid->isValidUid($arguments['citaat'])){
			$text.=' van '.$this->lid->getNaamLink($arguments['citaat'], 'user', true);
		}elseif(isset($arguments['citaat']) AND trim($arguments['citaat'])!=''){
			$text.=' van '.str_replace('_', '&nbsp;', $arguments['citaat']);
		}else{
			//geen naam ofzo...
		}
		$text.=':</strong><div class="citaat">'.trim($content).'</div></div>';
		return $text;
	}
	/*
	 * ubb_reldate();
	 * Geef de relatieve datum terug.
	 */
	function ubb_reldate($parameters=array()){
		$content = $this->parseArray(array('[/reldate]'), array());
		return reldate($content);

	}
	/*
	 * ubb_lid().
	 *
	 * [lid=0436] => Am. Waagmeester
	 *
	 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
	 */
	function ubb_lid($parameters){
		if(isset($parameters['lid'])){
			$text=$this->lid->getNaamLink($parameters['lid'], 'user', true);
		}else{
			$text='geen uid opgegeven';
		}
		return $text;
	}

	/*
	 * ubb_prive().
	 *
	 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
	 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
	 */
	function ubb_prive($arguments=array()){
		if(isset($arguments['prive'])){
			$permissie=$arguments['prive'];
		}else{
			$permissie='P_LOGGED_IN';
		}
		//content moet altijd geparsed worden, anders blijft de inhoud van de
		//tag gewoon staan.
		$content = $this->parseArray(array('[/prive]'), array());
		if(!$this->lid->hasPermission($permissie)){
			$content='';
		}
		return $content;
	}
	/*
	 * Omdat we niet willen dat dingen die in privé staan alsnog gezien
	 * kunnen worden bij het citeren, slopen we hier alles wat in privé-tags staat weg.
	 */
	public static function filterPrive($string){
		if(Lid::instance()->hasPermission('P_LOGGED_IN')){
			return $string;
		}else{
			// .* is greedy by default, dat wil zeggen, matched zoveel mogelijk.
			// door er .*? van te maken matched het zo weinig mogelijk, dat is precies
			// wat we hier willen, omdat anders [prive]foo[/prive]bar[prive]foo[/prive]
			// niets zou opleveren.
			// de /s modifier zorgt ervoor dat een . ook alle newlines matched.
			return preg_replace('/\[prive=?.*?\].*?\[\/prive\]/s', '', $string);
		}
	}

	/*
	 * Deze methode kan resultaten van query's die in de database staan printen in een
	 * tabelletje.
	 *
	 * [query=1]
	 */
	function ubb_query($parameters){
		if(isset($parameters['query'])){
			require_once('class.savedquery.php');
			$query=new SavedQuery((int)$parameters['query']);
			$return=$query->getHtml();
		}else{
			$return='Geen geldige query';
		}
		return $return;
	}

	/*
	 * ubb_youtube();
	 *
	 * [youtube]youtubeid[/youtube]
	 *
	 * geeft een miniatuurafbeelding weer van een youtube-video waarop geklikt kan worden om
	 * het filmpje af te spelen.
	 */
	private $youtube=array();
	function ubb_youtube($parameters){
		$content = $this->parseArray(array('[/youtube]'), array());
		//alleen de eerste 11 tekens zijn relevant...
		$content=substr($content, 0, 11);
		if(preg_match('/[0-9a-zA-Z\-_]{11}/', $content)){
			//als we in een quote-tag zijn, geen embed weergeven maar een link naar de embed,
			//en het filmpje ook maar meteen starten.
			if($this->quote_level>0 OR isset($this->youtube[$content])){
				$html='<a href="#youtube'.$content.'" onclick="youtubeDisplay(\''.$content.'\')" >&raquo; youtube-filmpje (ergens anders op deze pagina)</a>';
			}else{
				$html='<div id="youtube'.$content.'" class="youtubeVideo">
					<a href="http://www.youtube.com/watch?v='.$content.'" class="afspelen" onclick="return youtubeDisplay(\''.$content.'\')"><img width="36" height="36" src="'.CSR_PICS.'forum/afspelen.gif" alt="afspelen" /></a>
					<img src="http://img.youtube.com/vi/'.$content.'/default.jpg" style="width: 130px; height: 97px;"
						alt="klik op de afbeelding om de video te starten"/></div>';
				//sla het youtube-id op in een array, dan plaatsen we de tweede keer dat
				//het filmpje in een topic geplaatst wordt een linkje.
				$this->youtube[$content]=$content;
			}
		}else{
			$html='Ongeldig youtube-id: '.mb_htmlentities($content).'. Kies alleen de 11 tekens na v=';
		}
		return $html;
	}

	function ubb_googlevideo($parameters){
		$content = $this->parseArray(array('[/googlevideo]'), array());
		if(preg_match('/-?\d*/', $content)){
			$html='<embed style="width:400px; height:326px;" id="VideoPlayback" type="application/x-shockwave-flash"
src="http://video.google.com/googleplayer.swf?docId='.$content.'"></embed>';
		}else{
			$html='Ongeldig googlevideo-id';
		}
		return $html;
	}
	/*
	 * ubb_groep()
	 *
	 * [groep]123[/groep]
	 * Geeft een groep met kortebeschrijving en een lijstje met leden weer.
	 * Als de groep aanmeldbaar is komt er ook een aanmeldknopje bij.
	 */
	protected function ubb_groep($parameters){
		$content=$this->parseArray(array('[/groep]'), array());

		require_once 'groepen/class.groepcontent.php';
		$groeptag=new GroepUbbContent((int)$content);
		return $groeptag->getHTML();
	}
	/*
	 * ubb_maaltijd();
	 *
	 * [maaltijd=next], [maaltijd=1234]
	 *
	 * Geeft een blokje met maaltijdgegevens, aantal aanmeldingen en een
	 * aanmeldknopje weer.
	 */
	public function ubb_maaltijd($parameters){
		if(!isset($parameters['maaltijd']) OR ($parameters['maaltijd']!='next' AND !preg_match('/\d+/', $parameters['maaltijd']))){
			return 'Geen maaltijdID opgegeven of ongeldig ID.';
		}
		require_once 'maaltijden/class.maaltijdcontent.php';
		return MaaltijdContent::getMaaltijdubbtag(trim($parameters['maaltijd']));
	}
	public function ubb_offtopic(){
		$content = $this->parseArray(array('[/offtopic]'), array());
		return '<div class="offtopic">'.$content.'</div>';
	}
	function ubb_1337(){
        $html = $this->parseArray(array('[/1337]'), array());

        $html = str_replace('er ', '0r ',$html);
        $html = str_replace('you', 'j00',$html);
        $html = str_replace('elite', '1337',$html);
        $html = strtr($html, "abelostABELOST", "48310574831057");
        return $html;
    }

    function ubb_clear($parameters){
		switch (@$parameters['clear']){
			case 'left':	$sClear='left';		break;
			case 'right':	$sClear='right';	break;
			default:		$sClear='both';
		}
		return '<br style="height: 0px; clear: '.$sClear.';" />';
    }

	static function getUbbHelp(){
return <<<UBBVERHAAL
<div id="ubbhulpverhaal">
	<span id="ubbsluiten" onclick="toggleDiv('ubbhulpverhaal')" title="Opmaakhulp verbergen">&times;</span>
	<h2>Tekst opmaken</h2>
	U kunt uw berichten opmaken met een simpel opmaaktaaltje wat ubb genoemd wordt. Het lijkt wat op html, maar dan met vierkante haken:<br />
	<ul>
		<li>[b]...[/b] voor <strong>vette tekst</strong></li>
		<li>[i]...[/i] voor <em>cursieve tekst</em></li>
		<li>[u]...[/u] voor <span style="text-decoration: underline;">onderstreepte tekst</span></li>
		<li>[s]...[/s] voor <span style="text-decoration: line-through;">doorgestreepte tekst</span></li>
		<li>[email=pubcie@csrdelft.nl]Bericht naar de Pubcie[/email] voor een email-verwijzing</li>
		<li>[url=http://csrdelft.nl]Webstek van C.S.R.[/url] voor een verwijzing</li>
		<li>[img]http://csrdelft.nl/plaetje.jpg[/img] voor een plaetje</li>
		<li>[citaat][/citaat] voor een citaat. [citaat=<em>lidnummer</em>][/citaat] voor een citaat van een lid.</li>
		<li>[lid=<em>lidnummer</em>] voor een link naar het profiel van een lid of oudlid</li>
		<li>[youtube]<em>youtube-id</em>[/youtube] of [googlevideo]..[/googlevideo] voor een filmpje direct in je post</li>
		<li>[offtopic]...[/offtopic] voor een stukje tekst van-het-onderwerp.</li>
		<li>[ubboff]...[/ubboff] voor een stukje met ubb-tags zonder dat ze ge&iuml;nterpreteerd worden</li>
	</ul>
	Gebruik deze mogelijkheden spaarzaam, ga niet ineens alles vet maken of kleurtjes geven!<br />
	<br />

</div>
UBBVERHAAL;

	}

	/*
	 * Deze methode kan de belangrijkste mededelingen (doorgaans een top3) weergeven.
	 *
	 * [mededelingen=top3]
	 */
	public function ubb_mededelingen($parameters){
		if(isset($parameters['mededelingen']) AND $parameters['mededelingen']=='top3'){
			require_once('class.nieuwscontent.php');
			require_once('class.nieuws.php');
			$nieuws = new Nieuws();
			$nieuws->setAantalTopBerichten(3);
			$nieuws->setStandaardRank(255);
			$nieuwscontent = new NieuwsContent($nieuws);
			$return=$nieuwscontent->getTopBlock();
		}else{
			$return='Geen geldig mededelingenblok.';
		}
		return $return;
	}

	# Items voor in de zijbalk
	public function ubb_agendaitem(){
		$content = $this->parseArray(array('[/agendaitem]'), array());
		return '<div class="item"><a href="/actueel/agenda/">'.$content.'</a></div>';
	}

	# Commentaar-tag
	public function ubb_commentaar(){
		$content = $this->parseArray(array('[/commentaar]'), array());
		return '';
	}
	
	/*
	 * Google-maps ubb-tag. Door Piet-Jan Spaans.
	 * [map dynamic=false w=100 h=100]Oude Delft 9[/map]
	 */
	private $mapJsLoaded=false;
	public function ubb_map($parameters){
		$address = $this->parseArray(array('[/map]'), array());
		if(trim($address)==''){
			return 'Geen adres opgegeven';
		}
		$address=htmlspecialchars($address);
		$mapid='map'.md5($address);
		
		$width=300;
		$height=200;
		$style='';
		if(isset($parameters['w']) && $parameters['w']<600){
			$width = $parameters['w'];			
		}
		if(isset($parameters['h']) && $parameters['h']<600){
			$height= $parameters['h'];
		}
		if(isset($parameters['w']) || isset($parameters['h'])){
			$style='style="width:'.$width.'px;height:'.$height.'px;"';			
		}		
		
		$jscall = "writeStaticGmap('$mapid', '$address',$width,$height);";
		if(isset($parameters['dynamic']) && $parameters['dynamic']=='true'){		
				$jscall = "loadGmaps('$mapid','$address');";
		} 
		
		$html='';
		if(!$this->mapJsLoaded){
			$html.='<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAATQu5ACWkfGjbh95oIqCLYxRY812Ew6qILNIUSbDumxwZYKk2hBShiPLD96Ep_T-MwdtX--5T5PYf1A" type="text/javascript"></script><script type="text/javascript" src="/layout/js/gmaps.js"></script>';
			$this->mapJsLoaded=true;
		}		
		$html.= 
<<<MAPHTML
		<div class="ubb_gmap" id="$mapid" $style></div><script type="text/javascript">$jscall</script>
MAPHTML;

		return $html;
	}
}

?>
