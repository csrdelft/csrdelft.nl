<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrubb.php
# -------------------------------------------------------------------
#  wrapper
# -------------------------------------------------------------------

require_once 'ubb/eamBBParser.class.php';


class CsrUBB extends eamBBParser{

	static private $instance;


	public function instance(){
		return new CsrUBB();
	}

	private function __construct(){
		$this->eamBBParser();
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
		if(isset($arguments['citaat']) AND Lid::isValidUid($arguments['citaat'])){
			$lid=LidCache::getLid($arguments['citaat']);
			if($lid instanceof Lid){
				$text.=' van '.$lid->getNaamLink('user', 'link');
			}
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
		if(isset($parameters['lid']) AND Lid::isValidUid($parameters['lid'])){
			$lid=LidCache::getLid($parameters['lid']);
			if($lid instanceof Lid){
				$text=$lid->getNaamLink('user', 'link');
			}else{
				$text='Dit lid bestaat niet';
			}
		}else{
			$text='[lid] Geen correct uid opgegeven ('.mb_htmlentities($parameters['lid']).').<br />';
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
		if(!LoginLid::instance()->hasPermission($permissie)){
			$content='';
		}
		return $content;
	}
	/*
	 * Toont content als instelling een bepaalde waarde heeft,
	 * standaard 'ja';
	 *
	 * [instelling=voorpagina_maaltijdblokje][maaltijd=next][/instelling]
	 */
	function ubb_instelling($arguments=array()){
		$content = $this->parseArray(array('[/instelling]'), array());
		if(!isset($arguments['instelling'])){
			return 'Geen of een niet bestaande instelling ('.mb_htmlentities($arguments['instelling']).') opgegeven.';
		}
		$testwaarde='ja';
		if(isset($arguments['waarde'])){
			$testwaarde=$arguments['waarde'];
		}
		try{
			if(Instelling::get($arguments['instelling'])==$testwaarde){
				return $content;
			}
		}catch(Exception $e){
			return '[instelling]: '.$e->getMessage();
		}
	}
	/*
	 * Omdat we niet willen dat dingen die in privé staan alsnog gezien
	 * kunnen worden bij het citeren, slopen we hier alles wat in privé-tags staat weg.
	 */
	public static function filterPrive($string){
		if(LoginLid::instance()->hasPermission('P_LOGGED_IN')){
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
			require_once 'savedquery.class.php';
			$query=new SavedQuery((int)$parameters['query']);
			$return=$query->getHtml();
		}else{
			$return='[query] Geen geldig query-id opgegeven.<br />';
		}
		return $return;
	}


	/*
	 * ubb_video();
	 *
	 * universele videotag, gewoon urls erin stoppen. Ik heb een poging
	 * gedaan hem een beetje vergevingsgezind te laten zijn...
	 *
	 * Tot nu toe youtube, vimeo, dailymotion, 123video, godtube
	 *
	 * [video]http://www.youtube.com/watch?v=Zo0LJrw5nCs[/video]
	 * [video]Zo0LJrw5nCs[/video]
	 * [video]http://vimeo.com/1582112[/video]
	 */

	function ubb_video($parameters){
		$content = $this->parseArray(array('[/video]'), array());

		//determine type and id
		$id='';
		if(preg_match('/^[0-9a-zA-Z\-_]{11}$/', $content) OR strstr($content, 'youtube')){
			$type='youtube';
			if(strlen($content)==11){
				$id=$content;
			}else{
				if(preg_match('|^(http://)?(www\.)?youtube\.com/watch\?v=([0-9a-zA-Z\-_]{11}).*$|', $content, $matches)>0){
					$id=$matches[3];
				}
			}
		}elseif(strstr($content, 'vimeo')){
			$type='vimeo';
			if(preg_match('|^(http://)?(www\.)?vimeo\.com/(clip\:)?(\d+).*$|', $content, $matches)>0){
				$id=$matches[4];
			}
		}elseif(strstr($content, '123video')){
			$type='123video';
			//example url: http://www.123video.nl/playvideos.asp?MovieID=946848
			if(preg_match('|^(http://)?(www\.)?123video\.nl/playvideos\.asp\?MovieID=(\d+)(.*)$|', $content, $matches)>0){
				$id=$matches[3];
			}
		}elseif(strstr($content, 'dailymotion')){
			$type='dailymotion';
			if(preg_match('|^(http://)?(www\.)?dailymotion\.com/video/([a-z0-9]+)(_.*)?$|', $content, $matches)>0){
				$id=$matches[3];
			}
		}elseif(strstr($content, 'godtube')){
			$type='godtube';
			//example: http://www.godtube.com/watch/?v=9CFEMMNU
			if(preg_match('|^(http://)?(www\.)?godtube\.com/watch/\?v=([a-zA-Z0-9]+)$|', $content, $matches)>0){
				$id=$matches[3];
			}
		}else{
			$type='unknown';
		}

		if($id==''){
			return '[video ('.$type.')] ongeldige url: ('.mb_htmlentities($content).')';
		}

		//render embed html
		switch($type){
			case 'youtube':
				if(isset($this->youtube[$id]) AND !isset($parameters['nodefer'])){
					return '<a href="#youtube'.$content.'" onclick="youtubeDisplay(\''.$content.'\')" >&raquo; youtube-filmpje (ergens anders op deze pagina)</a>';
				}else{
					//sla het youtube-id op in een array, dan plaatsen we de tweede keer dat
					//het filmpje in een topic geplaatst wordt een linkje.
					$this->youtube[$id]=$id;
					return '<div id="youtube'.$id.'" class="youtubeVideo">
						<a href="http://www.youtube.com/watch?v='.$id.'" class="afspelen" onclick="return youtubeDisplay(\''.$id.'\')"><img width="36" height="36" src="'.CSR_PICS.'forum/afspelen.gif" alt="afspelen" /></a>
						<img src="http://img.youtube.com/vi/'.$id.'/default.jpg" style="width: 130px; height: 97px;"
							alt="klik op de afbeelding om de video te starten"/></div>';
				}
			break;
			case 'vimeo':
				$html=
				'<object width="549" height="309">
					<param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id='.$id.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" />
					<embed src="http://vimeo.com/moogaloop.swf?clip_id='.$id.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="549" height="309">
					</embed>
				</object>';
				return $html;

			break;
			case 'dailymotion':
				return '<object width="560" height="420"><param name="movie" value="http://www.dailymotion.com/swf/video/'.$id.'?width=560&theme=none"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed type="application/x-shockwave-flash" src="http://www.dailymotion.com/swf/video/'.$id.'?width=560&theme=none" width="560" height="420" allowfullscreen="true" allowscriptaccess="always"></embed></object>';
			break;
			case '123video':
				return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" id="123movie_'.$id.'" width="420" height="339"><param name="movie" value="http://www.123video.nl/123video_emb.swf?mediaSrc='.$id.'" /><param name="quality" value="high" /><param name="allowScriptAccess" value="always"/> <param name="allowFullScreen" value="true"></param><embed src="http://www.123video.nl/123video_emb.swf?mediaSrc='.$id.'" quality="high" width="420" height="339" allowfullscreen="true" type="application/x-shockwave-flash"  allowscriptaccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
			break;
			case 'godtube':
				return '<object height="255" width="400" type="application/x-shockwave-flash" data="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"><param name="movie" value="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"><param name="allowfullscreen" value="true"><param name="allowscriptaccess" value="always"><param name="wmode" value="opaque"><param name="flashvars" value="file=http://www.godtube.com/resource/mediaplayer/'.$id.'.file&image=http://www.godtube.com/resource/mediaplayer/'.$id.'.jpg&screencolor=000000&type=video&autostart=false&playonce=true&skin=http://www.godtube.com//resource/mediaplayer/skin/carbon/carbon.zip&logo.file=http://media.salemwebnetwork.com/godtube/theme/default/media/embed-logo.png&logo.link=http://www.godtube.com/watch/?v='.$id.'&logo.position=top-left&logo.hide=false&controlbar.position=over"></object>';
			default:
				return '[video] Niet-ondersteunde video-website ('.mb_htmlentities($content).')';
			break;
		}
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
			$html='[googlevideo] Ongeldig googlevideo-id';
		}
		return $html;
	}

	function ubb_vimeo($parameters){
		$content = $this->parseArray(array('[/vimeo]'), array());
		if(preg_match('/^\d*$/', $content)){
			$html='<object width="549" height="309">
			<param name="allowfullscreen" value="true" />
			<param name="allowscriptaccess" value="always" />
			<param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id='.$content.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" />
			<embed src="http://vimeo.com/moogaloop.swf?clip_id='.$content.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="549" height="309">
			</embed>
			</object>';
		}else{
			$html='[vimeo] Ongeldig vimeo-id';
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

		require_once 'groepen/groepcontent.class.php';
		$groeptag=new GroepUbbContent((int)$content);
		return $groeptag->getHTML();
	}

	/*
	 * ubb_document();
	 *
	 * [document]1234[/document]
	 *
	 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
	 */
	protected function ubb_document($parameters){
		$id=(int)$this->parseArray(array('[/document]'), array());

		require_once 'documenten/documentcontent.class.php';
		try{
			$document=new Document($id);
			$content=new DocumentUbbContent($document);
			return $content->getHTML();
		}catch(Exception $e){
			return '<div class="ubb_document">[document] Ongeldig document (id:'.$id.')</div>';
		}
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
			return '[maaltijd] Geen maaltijdID opgegeven of ongeldig ID (id:'.mb_htmlentities($parameters['maaltijd']).')';
		}
		require_once 'maaltijden/maaltijdcontent.class.php';
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
		<li>[youtube]<em>youtube-id</em>[/youtube], [googlevideo]..[/googlevideo] of [vimeo]..[/vimeo] voor een filmpje direct in je post</li>
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
		if(isset($parameters['mededelingen'])){
			require_once('mededelingen/mededeling.class.php');
			require_once('mededelingen/mededelingencontent.class.php');
			$type=$parameters['mededelingen'];
			if($type=='top3leden'){
				$mededelingenContent=new MededelingenContent(0);
				$return=$mededelingenContent->getTopBlock();
			}else if($type=='top3oudleden'){
				$mededelingenContent=new MededelingenContent(0);
				$return=$mededelingenContent->getTopBlock(true);
			}else{
				$return='Geen geldig type ('.$type.').';
			}
		}else{
			$return='[mededelingen] Geen geldig mededelingenblok.';
		}
		return $return;
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
			return '[map] Geen adres opgegeven';
		}
		$address=htmlspecialchars($address);
		$mapid='map'.md5($address);

		$width=300;
		$height=200;
		$style='';
		if(isset($parameters['w']) && $parameters['w']<800){
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
			$jscall="loadGmaps('$mapid','$address');";
		}

		$html='';
		if(!$this->mapJsLoaded){
			$html.='<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAATQu5ACWkfGjbh95oIqCLYxRY812Ew6qILNIUSbDumxwZYKk2hBShiPLD96Ep_T-MwdtX--5T5PYf1A" type="text/javascript"></script><script type="text/javascript" src="/layout/js/gmaps.js"></script>';
			$this->mapJsLoaded=true;
		}
		$html.='<div class="ubb_gmap" id="'.$mapid.'" '.$style.'></div><script type="text/javascript">'.$jscall.'</script>';

		return $html;
	}

	/*
	 * Peiling ubb-tag. Door Piet-Jan Spaans.
	 * [peiling=2]
	 */
	public function ubb_peiling($parameters){
		require_once 'peilingcontent.class.php';
		if(isset($parameters['peiling']) AND is_numeric($parameters['peiling'])){
			$peilingid = (int)$parameters['peiling'];
			try{
				$peiling=new Peiling($peilingid);
			}catch(Exception $e){
				return '[peiling] Er bestaat geen peiling met (id:'.$peilingid.')';
			}
			$peilingcontent=new PeilingContent($peiling);

			return $peilingcontent->getHTML();
		}else{
			return '[peiling] Geen geldig peilingblok.';
		}
	}
}
//we staan normaal geen HTML toe, met deze kan dat wel.
class CsrHtmlUBB extends CsrUBB{
	static private $instance;
	public function instance(){
		//als er nog geen instantie gemaakt is, die nu maken
		if(!isset(self::$instance)){
			self::$instance=new CsrHtmlUBB();
		}
		return self::$instance;
	}
	private function __construct(){
		$this->eamBBParser();
		$this->paragraph_mode = false;
		$this->allow_html=true;
	}
}
?>
