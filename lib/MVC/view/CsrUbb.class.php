<?php

require_once 'ubb/eamBBParser.class.php';

/**
 * CsrUbb.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class CsrUbb extends eamBBParser {

	public function __construct() {
		$this->eamBBParser();
		$this->paragraph_mode = false;
	}

	public static function parse($ubb) {
		$parser = new CsrUbb();
		return $parser->getHTML($ubb);
	}

	function getHTML($ubb) {
		parent::getHTML($ubb);

		if (LidInstellingen::get('layout', 'neuzen') == 'overal') {
			$pointer = 0;
			$counter = 0;
			$counter2 = 0;
			while ($pointer < strlen($this->HTML)) {
				$char = substr($this->HTML, $pointer, 1);
				if ($char == '<') {
					$counter += 1;
				} elseif ($char == '>') {
					$counter -= 1;
				} elseif ($char == '&') {
					$counter2 = 5;
				} elseif ($char == ';') {
					$counter2 = 0;
				} elseif ($char == 'o' && $counter == 0 && $counter2 <= 0) {
					$neus = $this->ubb_neuzen($char);
					$this->HTML = substr($this->HTML, 0, $pointer) . $neus . substr($this->HTML, $pointer + 1);
					$pointer += strlen($neus);
					continue;
				}
				$counter2--;
				$pointer++;
			}
		}
		return $this->HTML;
	}

	/**
	 * Omdat we niet willen dat dingen die in privé staan alsnog gezien kunnen worden 
	 * bij het citeren, slopen we hier alles wat in privé-tags staat weg.
	 */
	public static function filterPrive($string) {
		// .* is greedy by default, dat wil zeggen, matched zoveel mogelijk.
		// door er .*? van te maken matched het zo weinig mogelijk, dat is precies
		// wat we hier willen, omdat anders [prive]foo[/prive]bar[prive]foo[/prive]
		// niets zou opleveren.
		// de /s modifier zorgt ervoor dat een . ook alle newlines matched.
		return preg_replace('/\[prive=?.*?\].*?\[\/prive\]/s', '', $string);
	}

	/**
	 * Dit laad de twitter account van het hidden cash spel.
	 */
	function ubb_hidden($arguments = array()) {
		$html = '<a class="twitter-timeline" href="https://twitter.com/HiddenCashCSR" data-widget-id="477465734352621568">Tweets by @HiddenCashCSR</a>
							<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http://.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
		return $html;
	}

	function ubb_img($arguments = array()) {
		$style = '';
		$class = '';
		if (isset($arguments['float'])) {
			switch ($arguments['float']) {
				case 'left':
					$class = ' float-left';
					break;
				case 'right':
					$class = ' float-right';
					break;
			}
		}
		if (isset($arguments['w']) AND $arguments['w'] > 10) {
			$style = 'width: ' . ((int) $arguments['w']) . 'px;';
		}
		if (isset($arguments['h']) AND $arguments['h'] > 10) {
			$style = 'height: ' . ((int) $arguments['h']) . 'px;';
		}
		$content = $this->parseArray(array('[/img]', '[/IMG]'), array());
		// only valid patterns & prevent CSRF
		if (!url_like(urldecode($content)) OR startsWith($content, CSR_ROOT)) {
			return '[img: Ongeldige URL, tip: gebruik tinyurl.com]';
		}
		// als de html toegestaan is hebben we genoeg vertrouwen om sommige karakters niet te encoderen
		if (!$this->allow_html) {
			$content = htmlspecialchars($content);
		}
		// lazy loading van externe images bijv. op het forum
		if (!startsWith($content, CSR_PICS) OR startsWith($content, CSR_PICS . '/fotoalbum/')) {
			return '<div class="ubb_img_loading" src="' . $content . '" title="' . $content . '" style="' . $style . '"></div>';
		}
		return '<img class="ubb_img' . $class . '" src="' . $content . '" alt="' . $content . '" style="' . $style . '" />';
	}

	/**
	 * [foto]/pad/naar/foto[/foto]
	 * 
	 * Toont de thumbnail met link naar fotoalbum.
	 */
	function ubb_foto($arguments = array()) {
		require_once 'MVC/controller/FotoAlbumController.class.php';
		$url = urldecode($this->parseArray(array('[/foto]'), array()));
		$parts = explode('/', $url);
		$naam = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = PICS_PATH . 'fotoalbum' . implode('/', $parts);
		$album = FotoAlbumModel::getFotoAlbum($path);
		if (!$album) {
			return '<div class="ubb_block">Fotoalbum niet gevonden: ' . $url . '</div>';
		}
		$foto = new Foto($album, $naam);
		$fototag = new FotoUbbView($foto);
		return $fototag->getHTML();
	}

	/**
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
	 * 	compact	Compacte versie van de tag weergeven
	 * 			compact=true
	 *
	 */
	protected function ubb_fotoalbum($arguments = array()) {
		require_once 'MVC/controller/FotoAlbumController.class.php';
		$url = urldecode($this->parseArray(array('[/fotoalbum]'), array()));
		if ($url === 'laatste') {
			$album = FotoAlbumModel::getMostRecentFotoAlbum();
		} else {
			$path = PICS_PATH . 'fotoalbum' . $url;
			$album = FotoAlbumModel::getFotoAlbum($path);
		}
		if (!$album) {
			return '<div class="ubb_block">Fotoalbum niet gevonden: ' . $url . '</div>';
		}
		$fotoalbumtag = new FotoAlbumUbbView($album);
		if ($this->quote_level > 0 || isset($arguments['compact'])) {
			$fotoalbumtag->makeCompact();
		}
		if (isset($arguments['rows'])) {
			$fotoalbumtag->setRows((int) $arguments['rows']);
		}
		if (isset($arguments['bigfirst'])) {
			$fotoalbumtag->setBig(0);
		}
		if (isset($arguments['big'])) {
			if ($arguments['big'] == 'first') {
				$fotoalbumtag->setBig(0);
			} else {
				$fotoalbumtag->setBig($arguments['big']);
			}
		}
		return $fotoalbumtag->getHTML();
	}

	/**
	 * Rul = url
	 */
	function ubb_rul($arguments = array()) {
		return $this->ubb_url($arguments);
	}

	function ubb_url($arguments = array()) {
		$content = $this->parseArray(array('[/url]', '[/rul]'), array());
		if (isset($arguments['url'])) { // [url=
			$href = $arguments['url'];
		} elseif (isset($arguments['rul'])) { // [rul=
			$href = $arguments['rul'];
		} else { // [url][/url]
			$href = $content;
		}
		$href = filter_var($href, FILTER_SANITIZE_URL);
		$extern = ' target="_blank" class="external"'; // externe link
		if (startsWith($href, '/')) { // locale paden
			$href = CSR_ROOT . $href;
			$extern = '';
		} elseif (!startsWith($href, 'http://') AND ! startsWith($href, 'https://')) { // http(s) vergeten?
			$href = 'http://' . $href;
		} elseif (startsWith($href, CSR_ROOT) OR startsWith($href, CSR_PICS)) {
			$extern = '';
		}
		if (filter_var($href, FILTER_VALIDATE_URL)) {
			$result = '<a href="' . $href . '" title="' . $href . '"' . $extern . '>' . $content . '</a>';
		} else {
			$result = '[Ongeldige URL, tip: gebruik tinyurl.com]';
		}
		return $result;
	}

	/* todo
	  function ubb_mail($arguments = array()) {
	  return $this->ubb_email($arguments);
	  }

	  function ubb_email($arguments = array()){
	  $content = $this->parseArray(array('[/email]', '[/mail]'), array());
	  if (isset($arguments['email'])) { // [email=
	  $email = $arguments['email'];
	  }
	  elseif (isset($arguments['mail'])) { // [mail=
	  $email = $arguments['mail'];
	  }
	  else { // [email][/email]
	  $email = $content;
	  }
	  // only valid patterns
	  if (!email_like($email)){
	  return '[Ongeldig e-mail-adres]';
	  }
	  $result = '<a href="mailto:'. $email .'">'. $content .'</a>';
	  // spamprotectie: rot13 de email-tags, en voeg javascript toe om dat weer terug te rot13-en.
	  $result = '<script>document.write("'. str_rot13(addslashes($result)) .'".replace(/[a-zA-Z]/g, function(c){ return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));</script>';
	  return $result;
	  }
	 */

	function ubb_neuzen($arguments = array()) {
		if (is_array($arguments)) {
			$content = $this->parseArray(array('[/neuzen]'), array());
		} else {
			$content = $arguments;
		}
		if (LidInstellingen::get('layout', 'neuzen') != 'nee') {
			$neus = '<img src="' . CSR_PICS . '/famfamfam/bullet_red.png" alt="o" class="neus2013">';
			$content = str_replace('o', $neus, $content);
		}
		return $content;
	}

	function ubb_citaat($arguments = array()) {
		if ($this->quote_level == 0) {
			$this->quote_level = 1;
			$content = $this->parseArray(array('[/citaat]'), array());
			$this->quote_level = 0;
		} else {
			$this->quote_level++;
			$content = $this->parseArray(array('[/citaat]'), array());
			$this->quote_level--;
			$content = '<div onclick="$(this).children(\'.citaatpuntjes\').slideUp();$(this).children(\'.meercitaat\').slideDown();"><div class="meercitaat verborgen">' . $content . '</div><div class="citaatpuntjes" title="Toon citaat">...</div></div>';
		}
		$text = '<div class="citaatContainer"><strong>Citaat';
		$citaat = '';
		if (isset($arguments['citaat'])) {
			$citaat = trim(str_replace('_', ' ', $arguments['citaat']));
		}
		$naam = Lid::naamLink($citaat, 'user', 'visitekaartje');
		if ($naam !== false) {
			$text .= ' van ' . $naam;
		} elseif (array_key_exists('url', $arguments) AND startsWith($arguments['url'], 'http')) {
			if ($citaat == '') {
				$citaat = $arguments['url'];
			}
			$text .= ' van <a href="' . $arguments['url'] . '" title="' . $arguments['url'] . '" target="_blank" class="external">' . $citaat . '</a>';
		} elseif ($citaat !== '') {
			$text .= ' van ' . $citaat;
		}
		$text .= ':</strong><div class="citaat">' . trim($content) . '</div></div>';
		return $text;
	}

	/**
	 * Geef de relatieve datum terug.
	 */
	function ubb_reldate($arguments = array()) {
		$content = $this->parseArray(array('[/reldate]'), array());
		return '<span title="' . mb_htmlentities($content) . '">' . reldate($content) . '</span>';
	}

	/**
	 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
	 * 
	 * Example:
	 * [lid=0436] => Am. Waagmeester
	 * of
	 * [lid]0436[/lid]
	 */
	function ubb_lid($arguments = array()) {
		if (isset($arguments['lid'])) {
			$uid = $arguments['lid'];
		} else {
			$uid = $this->parseArray(array('[/lid]'), array());
		}
		$uid = trim($uid);
		$naam = Lid::naamLink($uid, 'user', 'visitekaartje');
		if ($naam !== false) {
			return $naam;
		} else {
			return '[lid] ' . mb_htmlentities($uid) . '] &notin; db.';
		}
	}

	/**
	 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
	 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
	 */
	function ubb_prive($arguments = array()) {
		if (isset($arguments['prive'])) {
			$permissie = $arguments['prive'];
		} else {
			$permissie = 'P_LOGGED_IN';
		}
//content moet altijd geparsed worden, anders blijft de inhoud van de
//tag gewoon staan.
		$forbidden = array();
		if (!LoginModel::mag($permissie)) {
			$this->ubb_mode = false;
			$forbidden = array('prive');
		}
		$content = $this->parseArray(array('[/prive]'), $forbidden);
		if (!LoginModel::mag($permissie)) {
			$content = '';
			$this->ubb_mode = true;
		}
		return $content;
	}

	/**
	 * Toont content als instelling een bepaalde waarde heeft,
	 * standaard 'ja';
	 *
	 * [instelling=maaltijdblokje module=voorpagina][maaltijd=next][/instelling]
	 */
	function ubb_instelling($arguments = array()) {
		$content = $this->parseArray(array('[/instelling]'), array());
		if (!array_key_exists('instelling', $arguments) OR ! isset($arguments['instelling'])) {
			return 'Geen of een niet bestaande instelling opgegeven: ' . mb_htmlentities($arguments['instelling']);
		}
		if (!array_key_exists('module', $arguments) OR ! isset($arguments['module'])) { // backwards compatibility
			$key = explode('_', $arguments['instelling'], 2);
			$arguments['module'] = $key[0];
			$arguments['instelling'] = $key[1];
		}
		$testwaarde = 'ja';
		if (isset($arguments['waarde'])) {
			$testwaarde = $arguments['waarde'];
		}
		try {
			if (LidInstellingen::get($arguments['module'], $arguments['instelling']) == $testwaarde) {
				return $content;
			}
		} catch (Exception $e) {
			return '[instelling]: ' . $e->getMessage();
		}
	}

	/**
	 * Deze methode kan resultaten van query's die in de database staan printen in een
	 * tabelletje.
	 *
	 * [query=1] of [query]1[/query]
	 */
	function ubb_query($arguments = array()) {
		if (isset($arguments['query'])) {
			$queryID = $arguments['query'];
		} else {
			$queryID = $this->parseArray(array('[/query]'), array());
		}
		$queryID = (int) $queryID;

		if ($queryID != 0) {
			require_once 'savedquery.class.php';
			$sqc = new SavedQueryContent(new SavedQuery((int) $arguments['query']));

			return $sqc->render_queryResult();
		} else {
			return '[query] Geen geldig query-id opgegeven.<br />';
		}
	}

	/**
	 * Universele videotag, gewoon urls erin stoppen. Ik heb een poging
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
	function ubb_video($arguments = array()) {
		$content = $this->parseArray(array('[/video]'), array());

//determine type and id
		$id = '';
		if (preg_match('/^[0-9a-zA-Z\-_]{11}$/', $content) OR strstr($content, 'youtube')) {
			$type = 'youtube';
			if (strlen($content) == 11) {
				$id = $content;
			} else {
				if (preg_match('|^(http://)?(www\.)?youtube\.com/watch\?v=([0-9a-zA-Z\-_]{11}).*$|', $content, $matches) > 0) {
					$id = $matches[3];
				}
			}
		} elseif (strstr($content, 'vimeo')) {
			$type = 'vimeo';
			if (preg_match('|^(http://)?(www\.)?vimeo\.com/(clip\:)?(\d+).*$|', $content, $matches) > 0) {
				$id = $matches[4];
			}
		} elseif (strstr($content, '123video')) {
			$type = '123video';
//example url: http://www.123video.nl/playvideos.asp?MovieID=946848
			if (preg_match('|^(http://)?(www\.)?123video\.nl/playvideos\.asp\?MovieID=(\d+)(.*)$|', $content, $matches) > 0) {
				$id = $matches[3];
			}
		} elseif (strstr($content, 'dailymotion')) {
			$type = 'dailymotion';
			if (preg_match('|^(http://)?(www\.)?dailymotion\.com/video/([a-z0-9]+)(_.*)?$|', $content, $matches) > 0) {
				$id = $matches[3];
			}
		} elseif (strstr($content, 'godtube')) {
			$type = 'godtube';
//example: http://www.godtube.com/watch/?v=9CFEMMNU
			if (preg_match('|^(http://)?(www\.)?godtube\.com/watch/\?v=([a-zA-Z0-9]+)$|', $content, $matches) > 0) {
				$id = $matches[3];
			}
		} else {
			$type = 'unknown';
		}

//error message if no valid id found in tag content.
		if ($id == '') {
			return '[video (' . $type . ')] ongeldige url: (' . mb_htmlentities($content) . ')';
		}

//video size
		$width = 560;
		$height = 420;
		if (isset($arguments['width']) AND (int) $arguments['width'] > 100) {
			$width = (int) $arguments['width'];
		}
		if (isset($arguments['height']) AND (int) $arguments['height'] > 100) {
			$height = (int) $arguments['height'];
		}

//render embed html
		switch ($type) {
			case 'youtube':
				if (isset($this->youtube[$id]) AND ! isset($arguments['force'])) {
					return '<a href="#youtube' . $content . '" onclick="youtubeDisplay(\'' . $content . '\')" >&raquo; youtube-filmpje (ergens anders op deze pagina)</a>';
				} else {
//sla het youtube-id op in een array, dan plaatsen we de tweede keer dat
//het filmpje in een topic geplaatst wordt een linkje.
					$this->youtube[$id] = $id;
					return '<div id="youtube' . $id . '" class="youtubeVideo">
						<a href="http://www.youtube.com/watch?v=' . $id . '" class="afspelen" onclick="return youtubeDisplay(\'' . $id . '\')"><img width="36" height="36" src="' . CSR_PICS . '/forum/afspelen.gif" alt="afspelen" /></a>
						<img src="http://img.youtube.com/vi/' . $id . '/default.jpg" style="width: 130px; height: 97px;"
							alt="klik op de afbeelding om de video te starten"/></div>';
				}
				break;
			case 'vimeo':
				return '<object width="' . $width . '" height="' . $height . '">
					<param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=' . $id . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" />
					<embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $id . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="' . $width . '" height="' . $height . '">
					</embed>
				</object>';
				break;
			case 'dailymotion':
				return '<object width="' . $width . '" height="' . $height . '"><param name="movie" value="http://www.dailymotion.com/swf/video/' . $id . '?width=560&theme=none"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed type="application/x-shockwave-flash" src="http://www.dailymotion.com/swf/video/' . $id . '?width=560&theme=none" width="' . $width . '" height="' . $height . '" allowfullscreen="true" allowscriptaccess="always"></embed></object>';
				break;
			case '123video':
				return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" id="123movie_' . $id . '" width="' . $width . '" height="' . $height . '"><param name="movie" value="http://www.123video.nl/123video_emb.swf?mediaSrc=' . $id . '" /><param name="quality" value="high" /><param name="allowScriptAccess" value="always"/> <param name="allowFullScreen" value="true"></param><embed src="http://www.123video.nl/123video_emb.swf?mediaSrc=' . $id . '" quality="high" width="' . $width . '" height="' . $height . '" allowfullscreen="true" type="application/x-shockwave-flash"  allowscriptaccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
				break;
			case 'godtube':
				return '<object height="' . $height . '" width="' . $width . '" type="application/x-shockwave-flash" data="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"><param name="movie" value="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"><param name="allowfullscreen" value="true"><param name="allowscriptaccess" value="always"><param name="wmode" value="opaque"><param name="flashvars" value="file=http://www.godtube.com/resource/mediaplayer/' . $id . '.file&image=http://www.godtube.com/resource/mediaplayer/' . $id . '.jpg&screencolor=000000&type=video&autostart=false&playonce=true&skin=http://www.godtube.com//resource/mediaplayer/skin/carbon/carbon.zip&logo.file=http://media.salemwebnetwork.com/godtube/theme/default/media/embed-logo.png&logo.link=http://www.godtube.com/watch/?v=' . $id . '&logo.position=top-left&logo.hide=false&controlbar.position=over"></object>';
			default:
				return '[video] Niet-ondersteunde video-website (' . mb_htmlentities($content) . ')';
				break;
		}
	}

	private $youtube = array();

	/**
	 * Geeft een miniatuurafbeelding weer van een youtube-video waarop geklikt kan worden om
	 * het filmpje af te spelen.
	 * 
	 * [youtube]youtubeid[/youtube]
	 */
	function ubb_youtube($arguments = array()) {
		$content = $this->parseArray(array('[/youtube]'), array());
//alleen de eerste 11 tekens zijn relevant...
		$content = substr($content, 0, 11);
		if (preg_match('/[0-9a-zA-Z\-_]{11}/', $content)) {
//als we in een quote-tag zijn, geen embed weergeven maar een link naar de embed,
//en het filmpje ook maar meteen starten.
			if ($this->quote_level > 0 OR isset($this->youtube[$content])) {
				$html = '<a href="#youtube' . $content . '" onclick="youtubeDisplay(\'' . $content . '\')" >&raquo; youtube-filmpje (ergens anders op deze pagina)</a>';
			} else {
				$html = '<div id="youtube' . $content . '" class="youtubeVideo">
					<a href="http://www.youtube.com/watch?v=' . $content . '" class="afspelen" onclick="return youtubeDisplay(\'' . $content . '\')"><img width="36" height="36" src="' . CSR_PICS . '/forum/afspelen.gif" alt="afspelen" /></a>
					<img src="http://img.youtube.com/vi/' . $content . '/default.jpg" style="width: 130px; height: 97px;"
						alt="klik op de afbeelding om de video te starten"/></div>';
//sla het youtube-id op in een array, dan plaatsen we de tweede keer dat
//het filmpje in een topic geplaatst wordt een linkje.
				$this->youtube[$content] = $content;
			}
		} else {
			$html = 'Ongeldig youtube-id: ' . mb_htmlentities($content) . '. Kies alleen de 11 tekens na v=';
		}
		return $html;
	}

	function ubb_googlevideo($arguments = array()) {
		$content = $this->parseArray(array('[/googlevideo]'), array());
		if (preg_match('/-?\d*/', $content)) {
			$html = '<embed style="width:400px; height:326px;" id="VideoPlayback" type="application/x-shockwave-flash"
src="http://video.google.com/googleplayer.swf?docId=' . $content . '"></embed>';
		} else {
			$html = '[googlevideo] Ongeldig googlevideo-id';
		}
		return $html;
	}

	function ubb_vimeo($arguments = array()) {
		$content = $this->parseArray(array('[/vimeo]'), array());
		if (preg_match('/^\d*$/', $content)) {
			$html = '<object width="549" height="309">
			<param name="allowfullscreen" value="true" />
			<param name="allowscriptaccess" value="always" />
			<param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=' . $content . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" />
			<embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $content . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="549" height="309">
			</embed>
			</object>';
		} else {
			$html = '[vimeo] Ongeldig vimeo-id';
		}
		return $html;
	}

	function ubb_twitter($arguments = array()) {
		$content = $this->parseArray(array('[/twitter]'), array());
		// widget size
		$lines = 4;
		$width = 355;
		$height = 300;
		if (isset($arguments['lines']) AND (int) $arguments['lines'] > 0) {
			$lines = (int) $arguments['lines'];
		}
		if (isset($arguments['width']) AND (int) $arguments['width'] > 100) {
			$width = (int) $arguments['width'];
		}
		if (isset($arguments['height']) AND (int) $arguments['height'] > 100) {
			$height = (int) $arguments['height'];
		}

		$html = <<<HTML
			<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
			<script>
			new TWTR.Widget({
			  version: 2,
			  type: 'profile',
HTML;
		$html.=" rpp: " . $lines . ",
			  interval: 30000,
			  width: " . $width . ",
			  height: " . $height . ",
			  theme: {
				shell: {
				  background: '#f5f5f5',
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
			}).render().setUser('" . mb_htmlentities($content) . "').start();
			</script>";
		return $html;
	}

	/**
	 * Geeft een groep met kortebeschrijving en een lijstje met leden weer.
	 * Als de groep aanmeldbaar is komt er ook een aanmeldknopje bij.
	 * 
	 * [groep]123[/groep]
	 * of
	 * [groep=123]
	 */
	protected function ubb_groep($arguments = array()) {
		if (isset($arguments['groep'])) {
			$groepid = $arguments['groep'];
		} else {
			$groepid = $this->parseArray(array('[/groep]'), array());
		}

		require_once 'groepen/groep.class.php';
		require_once 'groepen/groepcontent.class.php';
		try {
			$groep = new OldGroep($groepid);
			$groeptag = new GroepUbbContent($groep);
			return $groeptag->getHTML();
		} catch (Exception $e) {
			return '[groep] Geen geldig groep-id (' . mb_htmlentities($groepid) . ')';
		}
	}

	/**
	 * Geeft titel en auteur van een boek.
	 * Een kleine indicator geeft met kleuren beschikbaarheid aan
	 * 
	 * [boek]123[/boek]
	 * of
	 * [boek=123]
	 */
	protected function ubb_boek($arguments = array()) {
		if (isset($arguments['boek'])) {
			$boekid = $arguments['boek'];
		} else {
			$boekid = $this->parseArray(array('[/boek]'), array());
		}

		require_once 'bibliotheek/boek.class.php';
		require_once 'bibliotheek/bibliotheekcontent.class.php';
		try {
			$boek = new Boek((int) $boekid);
			$content = new BoekUbbContent($boek);
			return $content->view();
		} catch (Exception $e) {
			return '[boek] Boek [boekid:' . (int) $boekid . '] bestaat niet.';
		}
	}

	/**
	 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
	 * 
	 * [document]1234[/document]
	 * of
	 * [document=1234]
	 */
	protected function ubb_document($arguments = array()) {
		if (isset($arguments['document'])) {
			$id = $arguments['document'];
		} else {
			$id = $this->parseArray(array('[/document]'), array());
		}

		require_once 'documenten/documentcontent.class.php';
		try {
			$document = new Document((int) $id);
			$content = new DocumentUbbContent($document);
			return $content->getHTML();
		} catch (Exception $e) {
			return '<div class="ubb_document">[document] Ongeldig document (id:' . $id . ')</div>';
		}
	}

	/**
	 * Geeft een maaltijdketzer weer met maaltijdgegevens, aantal aanmeldingen en een aanmeldknopje.
	 * 
	 * [maaltijd=next], [maaltijd=1234]
	 * of
	 * [maaltijd]next[/maaldijd]
	 * of
	 * [maaltijd]123[/maaltijd]
	 */
	public function ubb_maaltijd($arguments = array()) {
		if (isset($arguments['maaltijd'])) {
			$mid = $arguments['maaltijd'];
		} else {
			$mid = $this->parseArray(array('[/maaltijd]'), array());
		}
		$mid = trim($mid);
		$maaltijd2 = null;

		require_once 'maalcie/model/MaaltijdenModel.class.php';
		require_once 'maalcie/model/MaaltijdAanmeldingenModel.class.php';
		require_once 'maalcie/view/MaaltijdKetzerView.class.php';
		try {
			if ($mid === 'next' || $mid === 'eerstvolgende' || $mid === 'next2' || $mid === 'eerstvolgende2') {
				$maaltijden = MaaltijdenModel::getKomendeMaaltijdenVoorLid(\LoginModel::getUid()); // met filter
				$aantal = sizeof($maaltijden);
				if ($aantal < 1) {
					return 'Geen aankomende maaltijd.';
				}
				$maaltijd = reset($maaltijden);
				if (endsWith($mid, '2') && $aantal >= 2) {
					unset($maaltijden[$maaltijd->getMaaltijdId()]);
					$maaltijd2 = reset($maaltijden);
				}
			} elseif (preg_match('/\d+/', $mid)) {
				$maaltijd = MaaltijdenModel::getMaaltijdVoorKetzer((int) $mid); // met filter
				if (!$maaltijd) {
					return '';
				}
			}
		} catch (Exception $e) {
			if (strpos($e->getMessage(), 'Not found') !== false) {
				return '<div class="ubb_block ubb_maaltijd">Maaltijd niet gevonden: ' . mb_htmlentities($mid) . '</div>';
			}
			return $e->getMessage();
		}
		if (!isset($maaltijd)) {
			return '<div class="ubb_block ubb_maaltijd">Maaltijd niet gevonden: ' . mb_htmlentities($mid) . '</div>';
		}
		$aanmeldingen = MaaltijdAanmeldingenModel::getAanmeldingenVoorLid(array($maaltijd->getMaaltijdId() => $maaltijd), \LoginModel::getUid());
		if (empty($aanmeldingen)) {
			$aanmelding = null;
		} else {
			$aanmelding = $aanmeldingen[$maaltijd->getMaaltijdId()];
		}
		$ketzer = new MaaltijdKetzerView($maaltijd, $aanmelding);
		$result = $ketzer->getKetzer();

		if ($maaltijd2 !== null) {
			$aanmeldingen2 = MaaltijdAanmeldingenModel::getAanmeldingenVoorLid(array($maaltijd2->getMaaltijdId() => $maaltijd2), \LoginModel::getUid());
			if (empty($aanmeldingen2)) {
				$aanmelding2 = null;
			} else {
				$aanmelding2 = $aanmeldingen2[$maaltijd2->getMaaltijdId()];
			}
			$ketzer2 = new MaaltijdKetzerView($maaltijd2, $aanmelding2);
			$result .= $ketzer2->getKetzer();
		}
		return $result;
	}

	/**
	 * Vanonderwerp = offtopic
	 */
	function ubb_vanonderwerp($arguments = array()) {
		return $this->ubb_offtopic($arguments);
	}

	public function ubb_offtopic() {
		$content = $this->parseArray(array('[/offtopic]', '[/vanonderwerp]'), array());
		return '<span class="offtopic">' . $content . '</span>';
	}

	/**
	 * Verklapper = spoiler
	 */
	function ubb_verklapper($arguments = array()) {
		return $this->ubb_spoiler($arguments);
	}

	public function ubb_spoiler() {
		$content = $this->parseArray(array('[/spoiler]', '[/verklapper]'), array());
		return '<button class="spoiler">Toon verklapper</button><div class="spoiler-content">' . $content . '</div>';
	}

	function ubb_1337() {
		$html = $this->parseArray(array('[/1337]'), array());
		$html = str_replace('er ', '0r ', $html);
		$html = str_replace('you', 'j00', $html);
		$html = str_replace('elite', '1337', $html);
		$html = strtr($html, "abelostABELOST", "48310574831057");
		return $html;
	}

	function ubb_b() {
		if ($this->nobold === true AND $this->quote_level == 0) {
			return $this->parseArray(array('[/b]'), array('b'));
		} else {
			return '<span class="dikgedrukt">' . $this->parseArray(array('[/b]'), array('b')) . '</span>';
		}
	}

	function ubb_i() {
		return '<span class="cursief">' . $this->parseArray(array('[/i]'), array('i')) . '</span>';
	}

	function ubb_s() {
		return '<span class="doorgestreept">' . $this->parseArray(array('[/s]'), array('s')) . '</span>';
	}

	function ubb_u() {
		return '<span class="onderstreept">' . $this->parseArray(array('[/u]'), array('u')) . '</span>';
	}

	function ubb_clear($arguments = array()) {
		$sClear = 'clear';
		if (isset($arguments['clear']) AND ( $arguments['clear'] === 'left' OR $arguments['clear'] === 'right' )) {
			$sClear .= '-' . $arguments['clear'];
		}
		return '<div class="' . $sClear . '"></div>';
	}

	/**
	 * Deze methode kan de belangrijkste mededelingen (doorgaans een top3) weergeven.
	 *
	 * [mededelingen=top3]
	 * of
	 * [mededeling]top3[/mededeling]
	 */
	public function ubb_mededelingen($arguments = array()) {
		if (isset($arguments['mededelingen'])) {
			$type = $arguments['mededelingen'];
		} else {
			$type = $this->parseArray(array('[/mededelingen]'), array());
		}
		if ($type == '') {
			return '[mededelingen] Geen geldig mededelingenblok.';
		}

		require_once 'mededelingen/mededeling.class.php';
		require_once 'mededelingen/mededelingencontent.class.php';

		$mededelingenContent = new MededelingenContent(0);
		switch ($type) {
			case 'top3nietleden': //lekker handig om dit intern dan weer anders te noemen...
				return $mededelingenContent->getTopBlock('nietleden');
			case 'top3leden':
				return $mededelingenContent->getTopBlock('leden');
			case 'top3oudleden':
				return $mededelingenContent->getTopBlock('oudleden');
		}
		return '[mededelingen] Geen geldig type (' . mb_htmlentities($type) . ').';
	}

	/**
	 * Commentaar niet weergeven
	 */
	function ubb_commentaar($arguments = array()) {
		$this->ubb_mode = false;
		$content = $this->parseArray(array('[/commentaar]'), array());
		$this->ubb_mode = true;
		return '';
	}

	/**
	 * Locatie = map in hoverIntentContent
	 */
	function ubb_locatie($arguments = array()) {
		$address = $this->parseArray(array('[/locatie]'), array());
		$map = $this->maps(htmlspecialchars($address), $arguments);
		return '<span class="hoverIntent"><a href="http://maps.google.nl/maps?q=' . htmlspecialchars($address) . '">' . $address . ' <img src="' . CSR_PICS . '/famfamfam/map.png" alt="map" title="Kaart" /></a><div class="hoverIntentContent">' . $map . '</div></span>';
	}

	/**
	 * Kaart = map
	 */
	function ubb_kaart($arguments = array()) {
		return $this->ubb_map($arguments);
	}

	/**
	 * Google-maps ubb-tag.
	 * 
	 * @author Piet-Jan Spaans
	 * 
	 * [map dynamic=false w=100 h=100]Oude Delft 9[/map]
	 */
	public function ubb_map($arguments = array()) {
		$address = $this->parseArray(array('[/map]', '[/kaart]'), array());
		return $this->maps(htmlspecialchars($address), $arguments);
	}

	public static function maps($address, array $arguments) {
		if (trim($address) == '') {
			return 'Geen adres opgegeven';
		}
		if (isset($arguments['w']) AND $arguments['w'] < 800) {
			$width = (int) $arguments['w'];
		} else {
			$width = 400;
		}
		if (isset($arguments['h']) AND $arguments['h'] < 600) {
			$height = (int) $arguments['h'];
		} else {
			$height = 300;
		}
		$html = '';
		if (!array_key_exists('mapJsLoaded', $GLOBALS)) {
			$html .= '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAATQu5ACWkfGjbh95oIqCLYxRY812Ew6qILNIUSbDumxwZYKk2hBShiPLD96Ep_T-MwdtX--5T5PYf1A" type="text/javascript"></script><script type="text/javascript" src="/layout/js/gmaps.js"></script>';
			$GLOBALS['mapJsLoaded'] = 1;
		} else {
			$GLOBALS['mapJsLoaded'] += 1;
		}
		$mapid = 'map' . $GLOBALS['mapJsLoaded'];
		$jscall = "writeStaticGmap('$mapid', '$address',$width,$height);";
		if (!isset($arguments['static'])) {
			$jscall = "$(document).ready(function() {loadGmaps('$mapid','$address');});";
		}
		$html .= '<div class="ubb_gmap" id="' . $mapid . '" style="width:' . $width . 'px;height:' . $height . 'px;"></div><script type="text/javascript">' . $jscall . '</script>';
		return $html;
	}

	/**
	 * Peiling ubb-tag.
	 * 
	 * @author Piet-Jan Spaans
	 * 
	 * [peiling=2]
	 * of
	 * [peiling]2[/peiling]
	 */
	public function ubb_peiling($arguments = array()) {
		if (isset($arguments['peiling'])) {
			$peilingid = $arguments['peiling'];
		} else {
			$peilingid = $this->parseArray(array('[/peiling]'), array());
		}

		require_once 'peilingcontent.class.php';
		try {
			$peiling = new Peiling((int) $peilingid);
			$peilingcontent = new PeilingContent($peiling);
			return $peilingcontent->getHTML();
		} catch (Exception $e) {
			return '[peiling] Er bestaat geen peiling met (id:' . (int) $peilingid . ')';
		}
	}

	private $slideshowJsIncluded = false;

	/**
	 * Slideshow-tag.
	 *
	 * example:
	 * [slideshow]http://example.com/image_1.jpg[/slideshow]
	 */
	public function ubb_slideshow($arguments = array()) {
		$content = $this->parseArray(array('[/slideshow]'), array());
		$slides_tainted = explode('[br]', $content);
		$slides = array();
		foreach ($slides_tainted as $slide) {
			$slide = trim($slide);
			if (url_like($slide) && $slide != '') {
				$slides[] = $slide;
			}
		}
		if (count($slides) == 0) {
			$content = '[slideshow]: geen geldige afbeeldingen gegeven';
		} else {
			$content = '
				<div class="image_reel">';
			foreach ($slides as $slide) {
				$content .= '<img src="' . $slide . '" alt="slide" />' . "\n";
			}
			$content .= '</div>'; //end image_reel
			$content .= '<div class="paging">';
			for ($i = 1; $i <= count($slides); $i++) {
				$content .= '<a href="#" rel="' . $i . '">&bull;</a>' . "\n";
			}
			$content .= '</div>' . "\n"; //end paging
			if ($this->slideshowJsIncluded === false) {
				$content .= '<script type="text/javascript" src="/layout/js/ubb_slideshow.js"></script>';
				$this->slideshowJsIncluded = true;
			}
		}
		return '<div class="ubb_slideshow">' . $content . '</div>';
	}

	/**
	 * Blokje met bijbelrooster voor opgegeven aantal dagen.
	 *
	 * [bijbelrooster=10]
	 * of
	 * [bijbelrooster]10[/bijbelrooster]
	 */
	public function ubb_bijbelrooster($arguments = array()) {
		if (isset($arguments['bijbelrooster'])) {
			$dagen = $arguments['bijbelrooster'];
		} else {
			$dagen = $this->parseArray(array('[/bijbelrooster]'), array());
		}
		require_once 'MVC/view/BijbelroosterView.class.php';
		$view = new BijbelroosterUbbView($dagen);
		return $view->getHtml();
	}

	function ubb_bijbel($arguments = array()) {
		$content = $this->parseArray(array('[/bijbel]'), array());
		if (isset($arguments['bijbel'])) { // [bijbel=
			$stukje = str_replace('_', ' ', $arguments['bijbel']);
		} else { // [bijbel][/bijbel]
			$stukje = $content;
		}
		$vertaling = null;
		if (isset($arguments['vertaling'])) {
			$vert = strtolower(str_replace('_', ' ', $arguments['vertaling']));
			foreach (self::$bijbelvertalingen as $v => $id) {
				if (startsWith(strtolower($v), $vert)) {
					$vertaling = $v;
				}
			}
		}
		return self::getBiblijaLink($stukje, $vertaling, true);
	}

	private static $bijbelvertalingen = array(
		'NBV'							 => 'id18=1',
		'NBG'							 => 'id16=1',
		'Herziene Statenvertaling'		 => 'id47=1',
		'Statenvertaling (Jongbloed)'	 => 'id37=1',
		'Groot Nieuws Bijbel'			 => 'id17=1',
		'Willibrordvertaling'			 => 'id35=1'
	);

	public static function getBiblijaLink($stukje, $vertaling = null, $tag = false) {
		if ($vertaling === null) {
			$vertaling = LidInstellingen::get('algemeen', 'bijbel');
		}
// fix http://stackoverflow.com/questions/10152894/php-replacing-special-characters-like-a-a-e-e
		$fix = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $stukje);
		$link = 'http://www.biblija.net/biblija.cgi?m=' . urlencode($fix) . '&' . self::$bijbelvertalingen[$vertaling] . '&l=nl&set=10';
		if ($tag) {
			return '<a href="' . $link . '" target="_blank">' . $stukje . '</a>';
		} else {
			return $link;
		}
	}

}

/**
 * We staan normaal geen HTML toe, maar met deze mag het wel.
 */
class CsrHtmlUbb extends CsrUbb {

	public function __construct() {
		parent::__construct();
		$this->allow_html = true;
	}

	public static function parse($ubb) {
		$parser = new CsrHtmlUbb();
		return $parser->getHTML($ubb);
	}

}
