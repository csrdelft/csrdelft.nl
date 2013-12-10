<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# csrubb.class.php

require_once 'ubb/eamBBParser.class.php';


class CsrUBB extends eamBBParser{

	static private $instance;

	public static function instance(){
		return new CsrUBB();
	}

	private function __construct(){
		$this->eamBBParser();
		$this->paragraph_mode = false;
	}

	function getHTML($ubb)
	{
		parent::getHTML($ubb);
		
		if (Instelling::get('layout_neuzen') == 'overal' || LoginLid::instance()->getLid()->getLichting() == 2013) {
			$pointer = 0;
			$counter = 0;
			while ($pointer < strlen($this->HTML)) {
				$char = substr($this->HTML, $pointer, 1);
				if ($char == '<') {
					$counter += 1;;
				}
				elseif ($char == '>') {
					$counter -= 1;
				}
				elseif ($char == 'o' && $counter == 0) {
					$neus = $this->ubb_neuzen($char);
					$this->HTML = substr($this->HTML, 0, $pointer) . $neus . substr($this->HTML, $pointer+1);
					$pointer += strlen($neus);
					continue;
				}
				$pointer++;
			}
		}
		return $this->HTML;
	}

	function ubb_url($arguments = array()){
		$content = $this->parseArray(array('[/url]', '[/rul]'), array());
		if (isset($arguments['url'])) { // [url=
			$href = $arguments['url'];
		}
		elseif (isset($arguments['rul'])) { // [rul=
			$href = $arguments['rul'];
		}
		else { // [url][/url]
			$href = $content;
		}
		// only valid patterns
		if (startsWith($href, '/')) { // locale paden
			$href = CSR_SERVER . $href;
		}
		elseif (!filter_var($href, FILTER_VALIDATE_URL)) { // http vergeten?
			$href = 'http://'. $href;
		}
		$pos = strpos($href, '://');
		if ($pos > 2 && $pos < 6 && filter_var($href, FILTER_VALIDATE_URL)) {
			$confirm = ' class="verlaatstek"';
			if (startsWith($href, CSR_ROOT) || startsWith($href, CSR_PICS)) {
				$confirm = '';
			}
			$result = '<a href="'. $href .'"'. $confirm .'>'. $content .'</a>';
		}
		else {
			$result = '[Ongeldige URL, tip: gebruik tinyurl.com]';
		}
		return $result;
	}

	function ubb_neuzen($arguments=array()){
		if (is_array($arguments)) {
			$content = $this->parseArray(array('[/neuzen]'), array());
		}
		else {
			$content = $arguments;
		}
		if (Instelling::get('layout_neuzen') != 'nee') {
			$neus = '<img src="http://plaetjes.csrdelft.nl/famfamfam/bullet_red.png" width="16" height="16" alt="o" style="float: none; padding: 0px; margin: -5px; background-color: inherit; border: none;">';
			$content = str_replace('o', $neus, $content);
		}
		return $content;
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
		return '<span title="'.mb_htmlentities($content).'">'.reldate($content).'</span>';

	}
	/*
	 * ubb_lid().
	 *
	 * [lid=0436] => Am. Waagmeester
	 *
	 * of
	 * [lid]0436[/lid]
	 *
	 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
	 */
	function ubb_lid($parameters){
		if(isset($parameters['lid'])){
			$uid=$parameters['lid'];
		}else{
			$uid = $this->parseArray(array('[/lid]'), array());
		}
		$uid=trim($uid);

		if(Lid::isValidUid($uid)){
			$lid=LidCache::getLid($uid);
			if($lid instanceof Lid){
				return $lid->getNaamLink('user', 'link');
			}else{
				return '[lid] Dit lid bestaat niet ('.mb_htmlentities($uid).').<br />';
			}
		}else{
			return '[lid] Geen correct uid opgegeven ('.mb_htmlentities($uid).').<br />';
		}
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
		$forbidden = array();
		if(!LoginLid::instance()->hasPermission($permissie)){
			$this->ubb_mode = false;
			$forbidden = array('prive');
		}
		$content = $this->parseArray(array('[/prive]'), $forbidden);
		if(!LoginLid::instance()->hasPermission($permissie)){
			$content='';
			$this->ubb_mode = true;
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
	 * [query=1] of [query]1[/query]
	 */
	function ubb_query($parameters){
		if(isset($parameters['query'])){
			$queryID=$parameters['query'];
		}else{
			$queryID=$this->parseArray(array('[/query]'), array());
		}
		$queryID=(int)$queryID;

		if($queryID!=0){
			require_once 'savedquery.class.php';
			$sqc=new SavedQueryContent(new SavedQuery((int)$parameters['query']));

			return $sqc->render_queryResult();
		}else{
			return '[query] Geen geldig query-id opgegeven.<br />';
		}
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
	 *
	 * tag parameters:
	 * 		force	Forceer weergave filmpje ook als het al een keer op de pagina voorkomt.
	 * 		width	Breedte van het filmpje
	 * 		height	Hoogte van het filmpje
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

		//error message if no valid id found in tag content.
		if($id==''){
			return '[video ('.$type.')] ongeldige url: ('.mb_htmlentities($content).')';
		}

		//video size
		$width=560;
		$height=420;
		if(isset($parameters['width']) AND (int)$parameters['width']>100){
			$width=(int)$parameters['width'];
		}
		if(isset($parameters['height']) AND (int)$parameters['height']>100){
			$height=(int)$parameters['height'];
		}

		//render embed html
		switch($type){
			case 'youtube':
				if(isset($this->youtube[$id]) AND !isset($parameters['force'])){
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
				return '<object width="'.$width.'" height="'.$height.'">
					<param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id='.$id.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" />
					<embed src="http://vimeo.com/moogaloop.swf?clip_id='.$id.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="'.$width.'" height="'.$height.'">
					</embed>
				</object>';
			break;
			case 'dailymotion':
				return '<object width="'.$width.'" height="'.$height.'"><param name="movie" value="http://www.dailymotion.com/swf/video/'.$id.'?width=560&theme=none"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed type="application/x-shockwave-flash" src="http://www.dailymotion.com/swf/video/'.$id.'?width=560&theme=none" width="'.$width.'" height="'.$height.'" allowfullscreen="true" allowscriptaccess="always"></embed></object>';
			break;
			case '123video':
				return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" id="123movie_'.$id.'" width="'.$width.'" height="'.$height.'"><param name="movie" value="http://www.123video.nl/123video_emb.swf?mediaSrc='.$id.'" /><param name="quality" value="high" /><param name="allowScriptAccess" value="always"/> <param name="allowFullScreen" value="true"></param><embed src="http://www.123video.nl/123video_emb.swf?mediaSrc='.$id.'" quality="high" width="'.$width.'" height="'.$height.'" allowfullscreen="true" type="application/x-shockwave-flash"  allowscriptaccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
			break;
			case 'godtube':
				return '<object height="'.$height.'" width="'.$width.'" type="application/x-shockwave-flash" data="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"><param name="movie" value="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"><param name="allowfullscreen" value="true"><param name="allowscriptaccess" value="always"><param name="wmode" value="opaque"><param name="flashvars" value="file=http://www.godtube.com/resource/mediaplayer/'.$id.'.file&image=http://www.godtube.com/resource/mediaplayer/'.$id.'.jpg&screencolor=000000&type=video&autostart=false&playonce=true&skin=http://www.godtube.com//resource/mediaplayer/skin/carbon/carbon.zip&logo.file=http://media.salemwebnetwork.com/godtube/theme/default/media/embed-logo.png&logo.link=http://www.godtube.com/watch/?v='.$id.'&logo.position=top-left&logo.hide=false&controlbar.position=over"></object>';
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

	function ubb_twitter($parameters){
		$content = $this->parseArray(array('[/twitter]'), array());
		//widget size
		$lines=4;
		$width=355;
		$height=300;
		if(isset($parameters['lines']) AND (int)$parameters['lines']>0){
			$lines=(int)$parameters['lines'];
		}
		if(isset($parameters['width']) AND (int)$parameters['width']>100){
			$width=(int)$parameters['width'];
		}
		if(isset($parameters['height']) AND (int)$parameters['height']>100){
			$height=(int)$parameters['height'];
		}

		$html=<<<HTML
			<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
			<script>
			new TWTR.Widget({
			  version: 2,
			  type: 'profile',
HTML;
		$html.=" rpp: ".$lines.",
			  interval: 30000,
			  width: ".$width.",
			  height: ".$height.",
			  theme: {
				shell: {
				  background: '#F0F0F0',
				  color: '#000000'
				},
				tweets: {
				  background: 'whiteSmoke',
				  color: '#000000',
				  links: '#0A338D'
				}
			  },
			  features: {
				scrollbar: false,
				loop: false,
				live: false,
				behavior: 'all'
			  }
			}).render().setUser('".mb_htmlentities($content)."').start();
			</script>";
		return $html;
	}
	/*
	 * ubb_groep()
	 *
	 * [groep]123[/groep]
	 * of
	 * [groep=123]
	 *
	 * Geeft een groep met kortebeschrijving en een lijstje met leden weer.
	 * Als de groep aanmeldbaar is komt er ook een aanmeldknopje bij.
	 */
	protected function ubb_groep($parameters){
		if(isset($parameters['groep'])){
			$groepid=$parameters['groep'];
		}else{
			$groepid=$this->parseArray(array('[/groep]'), array());
		}

		require_once 'groepen/groep.class.php';
		require_once 'groepen/groepcontent.class.php';
		try{
			$groep = new Groep($groepid);
			$groeptag = new GroepUbbContent($groep);
			return $groeptag->getHTML();
		}catch(Exception $e){
			return '[groep] Geen geldig groep-id ('.mb_htmlentities($groepid).')';
		}
	}
	/*
	 * ubb_boek()
	 *
	 * [boek]123[/boek]
	 * of
	 * [boek=123]
	 *
	 * Geeft titel en auteur van een boek.
	 * Een kleine indicator geeft met kleuren beschikbaarheid aan
	 */
	protected function ubb_boek($parameters){
		if(isset($parameters['boek'])){
			$boekid=$parameters['boek'];
		}else{
			$boekid=$this->parseArray(array('[/boek]'), array());
		}

		require_once 'bibliotheek/boek.class.php';
		require_once 'bibliotheek/bibliotheekcontent.class.php';
		try{
			$boek=new Boek((int)$boekid);
			$content=new BoekUbbContent($boek);
			return $content->getHTML();
		}catch(Exception $e){
			return '[boek] Boek [boekid:'.(int)$boekid.'] bestaat niet.';
		}
	}

	/**
	 * ubb_fotoalbum
	 *
	 * [fotoalbum]/pad/naar/album[/fotoalbum]
	 *
	 * Parameters:
	 * 	rows	Aantal regels weergeven
	 * 			rows=4
	 *
	 * 	big		Lijstje met indexen van afbeeldingen die groot moeten
	 * 			worden.
	 * 			big=0,5,14 | big=a | big=b |
	 *
	 *	compact	Compacte versie van de tag weergeven
	 * 			compact=true
	 *
	 *
	 *
	 */
	protected function ubb_fotoalbum($parameters){
		$albuminvoer=$this->parseArray(array('[/fotoalbum]'), array());

		require_once 'fotoalbumcontent.class.php';

		//we kunnen urls uit de browser direct in de tag kopieren
		$pad=urldecode($albuminvoer);

		$album=null;
		if($pad!=''){
			//eventueel '/fotoalbum' aan het begin wegverwijderen.
			if(substr($pad, 0, 10)=='/fotoalbum'){
				$pad=substr($pad, 10);
			}
			//de albumnaam bedenken. Op een of andere wijze doet het album dat niet zelf :(
			$albuminvoer=array_filter(explode('/', $albuminvoer));
			$albumnaam=urldecode(end($albuminvoer));

			$album=new Fotoalbum($pad, $albumnaam);

			//album bestaat niet, we geven een foutmelding
			if(!$album->exists()){
				return '<div class="ubb_block">Fotoalbum niet gevonden: '.mb_htmlentities($pad).'</div>';
			}
		}

		$fotoalbumtag=new FotoalbumUbbContent($album);

		if($this->quote_level>0 || isset($parameters['compact'])){
			$fotoalbumtag->makeCompact();
		}

		if(isset($parameters['rows'])){
			$fotoalbumtag->setRows((int)$parameters['rows']);
		}
		if(isset($parameters['bigfirst'])){
			$fotoalbumtag->setBig(0);
		}
		if(isset($parameters['big'])){
			if($parameters['big']=='first'){
				$fotoalbumtag->setBig(0);
			}else{
				$fotoalbumtag->setBig($parameters['big']);
			}
		}
		return $fotoalbumtag->getHTML();
	}
	/*
	 * ubb_document();
	 *
	 * [document]1234[/document]
	 * of
	 * [document=1234]
	 *
	 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
	 */
	protected function ubb_document($parameters){
		if(isset($parameters['document'])){
			$id=$parameters['document'];
		}else{
			$id=$this->parseArray(array('[/document]'), array());
		}

		require_once 'documenten/documentcontent.class.php';
		try{
			$document=new Document((int)$id);
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
	 * of
	 *
	 * [maaltijd]next[/maaldijd], [maaltijd]123[/maaltijd]
	 *
	 * Geeft een blokje met maaltijdgegevens, aantal aanmeldingen en een
	 * aanmeldknopje weer.
	 */
	public function ubb_maaltijd($parameters){
		if (isset($parameters['maaltijd'])) {
			$mid = $parameters['maaltijd'];
		}
		else{
			$mid = $this->parseArray(array('[/maaltijd]'), array());
		}
		$mid = trim($mid);
		$maaltijd2 = null;
		// init
		require_once 'taken/model/InstellingenModel.class.php';
		\Taken\MLT\InstellingenModel::getAlleInstellingen();
		
		require_once 'taken/model/MaaltijdenModel.class.php';
		require_once 'taken/model/AanmeldingenModel.class.php';
		require_once 'taken/view/MaaltijdKetzerView.class.php';
		try {
			if ($mid === 'next' || $mid === 'eerstvolgende' || $mid === 'next2' || $mid === 'eerstvolgende2') {
				$maaltijden = \Taken\MLT\MaaltijdenModel::getKomendeMaaltijdenVoorLid(\LoginLid::instance()->getUid()); // met filter
				$aantal = sizeof($maaltijden);
				if ($aantal < 1) {
					return 'Geen aankomende maaltijd.';
				}
				$maaltijd = reset($maaltijden);
				if (endsWith($mid, '2') && $aantal >= 2) {
					unset($maaltijden[$maaltijd->getMaaltijdId()]);
					$maaltijd2 = reset($maaltijden);
				}
			}
			elseif (preg_match('/\d+/', $mid)) {
				$maaltijd = \Taken\MLT\MaaltijdenModel::getMaaltijdVoorKetzer((int)$mid); // met filter
				if (!$maaltijd) {
					return '';
				}
			}
		}
		catch (Exception $e) {
			if (strpos($e->getMessage(), 'Not found') !== false) {
				return '<div class="ubb_block ubb_maaltijd">Maaltijd niet gevonden: '. mb_htmlentities($mid) .'</div>';
			}
			return $e->getMessage();
		}
		if (!isset($maaltijd)) {
			return '<div class="ubb_block ubb_maaltijd">Maaltijd niet gevonden: '. mb_htmlentities($mid) .'</div>';
		}
		$aanmeldingen = \Taken\MLT\AanmeldingenModel::getAanmeldingenVoorLid(array($maaltijd->getMaaltijdId() => $maaltijd), \LoginLid::instance()->getUid());
		if (empty($aanmeldingen)) {
			$aanmelding = null;
		}
		else {
			$aanmelding = $aanmeldingen[$maaltijd->getMaaltijdId()];
		}
		$ketzer = new \Taken\MLT\MaaltijdKetzerView($maaltijd, $aanmelding);
		$result = $ketzer->fetch();
		
		if ($maaltijd2 !== null) {
			$aanmeldingen2 = \Taken\MLT\AanmeldingenModel::getAanmeldingenVoorLid(array($maaltijd2->getMaaltijdId() => $maaltijd2), \LoginLid::instance()->getUid());
			if (empty($aanmeldingen2)) {
				$aanmelding2 = null;
			}
			else {
				$aanmelding2 = $aanmeldingen2[$maaltijd2->getMaaltijdId()];
			}
			$ketzer2 = new \Taken\MLT\MaaltijdKetzerView($maaltijd2, $aanmelding2);
			$result .= $ketzer2->fetch();
		}
		return $result;
	}

	public function ubb_offtopic(){
		$content = $this->parseArray(array('[/offtopic]'), array());
		return '<div class="offtopic">'.$content.'</div>';
	}

	public function ubb_spoiler(){
		$content = $this->parseArray(array('[/spoiler]'), array());
		return '<div class="spoiler_button"><button>Toon/verberg spoiler</button></div><div class="spoiler">'.$content.'</div>';
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
		<li>[email=pubcie@csrdelft.n1]Bericht naar de Pubcie[/email] voor een email-verwijzing</li>
		<li>[url=http://csrdelft.nl]Webstek van C.S.R.[/url] voor een verwijzing</li>
		<li>[citaat][/citaat] voor een citaat. [citaat=<em>lidnummer</em>][/citaat] voor een citaat van een lid.</li>
		<li>[lid]<em>lidnummer</em>[/lid] voor een link naar het profiel van een lid of oudlid</li>
		<li>[offtopic]...[/offtopic] voor een stukje tekst van-het-onderwerp.</li>
		<li>[ubboff]...[/ubboff] voor een stukje met ubb-tags zonder dat ze ge&iuml;nterpreteerd worden</li>
	</ul><br />
	<h2>Elementen invoegen</h2>
	<ul>
		<li>[img]http://csrdelft.nl/plaetje.jpg[/img] voor een plaetje</li>
		<li>[video]<em>url</em>[/video], de url van een youtube, vimeo, dailymotion of godtube voor een filmpje direct in je post.</li>
		<li>[groep]<em>groepid</em>[/groep] nummer van de ketzer/groep/commissie.</li>
		<li>[document]<em>documentid</em>[/document] nummer van document</li>
		<li>[peiling=<em>peilingid</em>] nummer van peiling</li>
	</ul><br />
	In de beperking toont zich de meester!<br />
	<br />

</div>
UBBVERHAAL;

	}

	/*
	 * Deze methode kan de belangrijkste mededelingen (doorgaans een top3) weergeven.
	 *
	 * [mededelingen=top3]
	 * of
	 * [mededeling]top3[/mededeling]
	 */
	public function ubb_mededelingen($parameters){
		if(isset($parameters['mededelingen'])){
			$type=$parameters['mededelingen'];
		}else{
			$type=$this->parseArray(array('[/mededelingen]'), array());
		}
		if($type==''){
			return '[mededelingen] Geen geldig mededelingenblok.';
		}

		require_once 'mededelingen/mededeling.class.php';
		require_once 'mededelingen/mededelingencontent.class.php';

		$mededelingenContent=new MededelingenContent(0);
		switch($type){
			case 'top3nietleden': //lekker handig om dit intern dan weer anders te noemen...
				return $mededelingenContent->getTopBlock('nietleden');
			case 'top3leden':
				return $mededelingenContent->getTopBlock('leden');
			case 'top3oudleden':
				return $mededelingenContent->getTopBlock('oudleden');
		}
		return '[mededelingen] Geen geldig type ('.mb_htmlentities($type).').';
	}


	# Commentaar-tag
	public function ubb_commentaar(){
		$this->ubb_mode = false;
		$content = $this->parseArray(array('[/commentaar]'), array('commentaar'));
		$this->ubb_mode = true;
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
			$width=(int)$parameters['w'];
		}
		if(isset($parameters['h']) && $parameters['h']<600){
			$height=(int)$parameters['h'];
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
	 * of
	 * [peiling]2[/peiling]
	 */
	public function ubb_peiling($parameters){
		if(isset($parameters['peiling'])){
			$peilingid=$parameters['peiling'];
		}else{
			$peilingid=$this->parseArray(array('[/peiling]'), array());
		}

		require_once 'peilingcontent.class.php';
		try{
			$peiling=new Peiling((int)$peilingid);
			$peilingcontent=new PeilingContent($peiling);
			return $peilingcontent->getHTML();
		}catch(Exception $e){
			return '[peiling] Er bestaat geen peiling met (id:'.(int)$peilingid.')';
		}
	}

	/* slideshow-tag.
	 *
	 * example:
	 * [slideshow]http://example.com/image_1.jpg[/slideshow]
	 */
	private $slideshowJsIncluded=false;
	public function ubb_slideshow($parameters){
		$content = $this->parseArray(array('[/slideshow]'), array());

		$slides_tainted=explode('[br]', $content);
		$slides=array();
		foreach($slides_tainted as $slide){
			$slide=trim($slide);
			if(url_like($slide) && $slide!=''){
				$slides[]=$slide;
			}
		}


		$width=355;
		$height=238;
		if(isset($parameters['w']) && $parameters['w']<800){
			$width=(int)$parameters['w'];
		}
		if(isset($parameters['h']) && $parameters['h']<600){
			$height=$parameters['h'];
		}

		$style='style="width:'.$width.'px;height:'.$height.'px;';
		if(isset($parameters['float']) && in_array($parameters['float'], array('left', 'right'))){
			$style=' float: '.$parameters['float'].'';
		}
		$style.='"';

		if(count($slides)==0){
			$content='[slideshow]: geen geldige afbeeldingen gegeven';
		}else{
			$content='
				<div class="image_reel">';

			foreach($slides as $slide){
				$content.='<img src="'.$slide.'" alt="slide" />'."\n";
			}
			$content.='</div>';//end image_reel
			$content.='<div class="paging">';
			for($i=1; $i<=count($slides); $i++){
				$content.='<a href="#" rel="'.$i.'">&bull;</a>'."\n";
			}

			$content.='</div>'."\n"; //end paging
			if($this->slideshowJsIncluded===false){
				$content.='<script type="text/javascript" src="/layout/js/ubb_slideshow.js"></script>';
				$this->slideshowJsIncluded=true;
			}
		}

		return '<div class="ubb_slideshow" '.$style.'>'.$content.'</div>';
	}
	/*
	 * Blokje met bijbelrooster voor opgegeven aantal dagen
	 *
	 * [bijbelrooster=10]
	 * of
	 * [bijbelrooster]10[/bijbelrooster]
	 */
	public function ubb_bijbelrooster($parameters){
		if(isset($parameters['bijbelrooster'])){
			$dagen = $parameters['bijbelrooster'];
		}else{
			$dagen = $this->parseArray(array('[/bijbelrooster]'), array());
		}

		require_once 'bijbelrooster.class.php';
		$bijbel = new Bijbelrooster();
		return $bijbel->ubbContent($dagen);
	}

}
//we staan normaal geen HTML toe, met deze kan dat wel.
class CsrHtmlUBB extends CsrUBB{
	static private $instance;
	public static function instance(){
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
