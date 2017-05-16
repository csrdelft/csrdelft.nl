<?php
namespace CsrDelft\view;

use CsrDelft\bbparser\eamBBParser;
use CsrDelft\Icon;
use CsrDelft\model\bibliotheek\BiebBoek;
use CsrDelft\model\documenten\Document;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\KetzersModel;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\OnderverenigingenModel;
use CsrDelft\model\groepen\RechtenGroepenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\groepen\WerkgroepenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\PeilingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\SavedQuery;
use CsrDelft\SavedQueryContent;
use CsrDelft\view\bibliotheek\BoekBBView;
use CsrDelft\view\documenten\DocumentBBContent;
use CsrDelft\view\formulier\UrlDownloader;
use CsrDelft\view\fotoalbum\FotoAlbumBBView;
use CsrDelft\view\fotoalbum\FotoAlbumSliderView;
use CsrDelft\view\fotoalbum\FotoBBView;
use CsrDelft\view\groepen\GroepView;
use CsrDelft\view\ledenmemory\LedenMemoryScoreTable;
use CsrDelft\view\ledenmemory\LedenMemoryView;
use CsrDelft\view\maalcie\persoonlijk\MaaltijdKetzerView;
use CsrDelft\view\peilingen\PeilingView;
use Exception;
use function CsrDelft\endsWith;
use function CsrDelft\external_url;
use function CsrDelft\reldate;
use function CsrDelft\startsWith;
use function CsrDelft\url_like;


/**
 * CsrBB.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class CsrBB extends eamBBParser {

	/**
	 * BBcode within email is limited.
	 *
	 * @var boolean
	 */
	protected $email_mode = false;

	public function __construct() {
		parent::__construct();
		$this->paragraph_mode = false;
	}

	public static function parse($bbcode) {
		$parser = new CsrBB();
		return $parser->getHtml($bbcode);
	}

	public static function parseHtml($bbcode, $inline = false) {
		$parser = new CsrBB();
		$parser->allow_html = true;
		$parser->standard_html = $inline;
		return $parser->getHtml($bbcode);
	}

	public static function parseMail($bbcode) {
		$parser = new CsrBB();
		$parser->email_mode = true;
		return $parser->getHtml($bbcode);
	}

	/**
	 * Bij citeren mogen er geen ongesloten tags zijn om problemen te voorkomen.
	 * Werkt niet bij [ubboff] / [tekst].
	 *
	 * @param string $bbcode
	 * @return string
	 */
	public static function sluitTags($bbcode) {
		$verschil = self::aantalOngeslotenTags($bbcode);
		for ($i = 0; $i < $verschil; $i++) {
			$bbcode .= '[/]';
		}
		return $bbcode;
	}

	/**
	 * Aantal ongesloten tags.
	 *
	 * @param string $bbcode
	 * @return int
	 */
	public static function aantalOngeslotenTags($bbcode) {
		return substr_count($bbcode, '[') - substr_count($bbcode, '[*]') - 2 * substr_count($bbcode, '[/');
	}

	/**
	 * Soms willen we voorkomen dat de gebruiker een ubboff tag gebruikt, zoals in forum reden.
	 */
	public static function escapeUbbOff($bbcode) {
		return str_replace(array('[/ubboff]', '[/tekst]'), array('[/]', '[/]'), $bbcode);
	}

	/**
	 * Omdat we niet willen dat dingen die in privé staan alsnog gezien kunnen worden
	 * bij het citeren, slopen we hier alles wat in privé-tags staat weg.
	 */
	public static function filterPrive($bbcode) {
		// .* is greedy by default, dat wil zeggen, matched zoveel mogelijk.
		// door er .*? van te maken matched het zo weinig mogelijk, dat is precies
		// wat we hier willen, omdat anders [prive]foo[/prive]bar[prive]foo[/prive]
		// niets zou opleveren.
		// de /s modifier zorgt ervoor dat een . ook alle newlines matched.
		return preg_replace('/\[prive=?.*?\].*?\[\/prive\]/s', '', $bbcode);
	}

	/**
	 * Omdat we niet willen dat dingen die in commentaar staan alsnog gezien kunnen worden
	 * bij het citeren, slopen we hier alles wat in commentaar-tags staat weg.
	 */
	public static function filterCommentaar($bbcode) {
		// .* is greedy by default, dat wil zeggen, matched zoveel mogelijk.
		// door er .*? van te maken matched het zo weinig mogelijk, dat is precies
		// wat we hier willen, omdat anders [commentaar]foo[/commentaar]bar[commentaar]foo[/commentaar]
		// niets zou opleveren.
		// de /s modifier zorgt ervoor dat een . ook alle newlines matched.
		return preg_replace('/\[commentaar=?.*?\].*?\[\/commentaar\]/s', '', $bbcode);
	}

	/**
	 * Dit laad de twitter account van het hidden cash spel.
	 */
	function bb_hidden($arguments = array()) {
		$html = '<a class="twitter-timeline" href="https://twitter.com/HiddenCashCSR" data-widget-id="477465734352621568">Tweets by @HiddenCashCSR</a>
							<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http://.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
		return $html;
	}

	function bb_img($arguments = array()) {
		$style = '';
		$class = '';
		if (isset($arguments['class'])) {
			$class .= htmlspecialchars($arguments['class']);
		}
		if (isset($arguments['float'])) {
			switch ($arguments['float']) {
				case 'left':
					$style .= 'float:left;';
					break;
				case 'right':
					$style .= 'float:right;';
					break;
			}
		}
		if (isset($arguments['w']) AND $arguments['w'] > 10) {
			$style .= 'width: ' . ((int) $arguments['w']) . 'px; ';
		}
		if (isset($arguments['h']) AND $arguments['h'] > 10) {
			$style .= 'height: ' . ((int) $arguments['h']) . 'px;';
		}
		$url = $this->parseArray(array('[/img]', '[/IMG]'), array());
		$url = filter_var($url, FILTER_SANITIZE_URL);
		if (!$url OR ( !url_like($url) AND ! startsWith($url, '/plaetjes/') )) {
			return $url;
		}
		if ($this->email_mode) {
			return '<img class="bb-img ' . $class . '" src="' . $url . '" alt="' . htmlspecialchars($url) . '" style="' . $style . '" />';
		}
		return '<div class="bb-img-loading" src="' . $url . '" title="' . htmlspecialchars($url) . '" style="' . $style . '"></div>';
	}

	/**
	 * [foto]/pad/naar/foto[/foto]
	 *
	 * Toont de thumbnail met link naar fotoalbum.
	 */
	function bb_foto($arguments = array()) {
				$url = urldecode($this->parseArray(array('[/foto]'), array()));
		$parts = explode('/', $url);
		if (in_array('Posters', $parts)) {
			$groot = true;
		} else {
			$groot = false;
		}
		$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = PHOTOS_PATH . 'fotoalbum' . implode('/', $parts);
		$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		if (!$album) {
			return '<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>';
		}
		if (isset($arguments['responsive'])) {
			$responsive = true;
		} else {
			$responsive = false;
		}
		$foto = new Foto($filename, $album);
		if ($foto) {
			$fototag = new FotoBBView($foto, $groot, $responsive);
		} else {
			return '';
		}
		return $fototag->getHtml();
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
	protected function bb_fotoalbum($arguments = array()) {
				$url = urldecode($this->parseArray(array('[/fotoalbum]'), array()));
		if ($url === 'laatste') {
			$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
		} else {
			//vervang url met pad
			$url = str_ireplace(CSR_ROOT, '', $url);
			$path = PHOTOS_PATH;
			//check fotoalbum in url
			$url = str_ireplace('fotoalbum/', '', $url);
			$path .= 'fotoalbum/';
			//check slash voor pad
			if (startsWith($url, '/')) {
				$url = substr($url, 1);
			}
			$path .= $url;
			$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		}
		if (!$album) {
			return '<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>';
		}
		if (isset($arguments['slider'])) {
			$view = new FotoAlbumSliderView($album);
			if (isset($arguments['height'])) {
				$view->height = (int) $arguments['height'];
			}
			if (isset($arguments['interval'])) {
				$view->interval = (int) $arguments['interval'];
			}
			if (isset($arguments['random'])) {
				$view->random = $arguments['random'] !== 'false';
			}
		} else {
			$view = new FotoAlbumBBView($album);

			if ($this->quote_level > 0 || isset($arguments['compact'])) {
				$view->makeCompact();
			}
			if (isset($arguments['rows'])) {
				$view->setRows((int) $arguments['rows']);
			}
			if (isset($arguments['perrow'])) {
				$view->setPerRow((int) $arguments['perrow']);
			}
			if (isset($arguments['bigfirst'])) {
				$view->setBig(0);
			}
			if (isset($arguments['big'])) {
				if ($arguments['big'] == 'first') {
					$view->setBig(0);
				} else {
					$view->setBig($arguments['big']);
				}
			}
		}
		return $view->getHtml();
	}

	/**
	 * Rul = url
	 */
	function bb_rul($arguments = array()) {
		return $this->bb_url($arguments);
	}

	function bb_url($arguments = array()) {
		$content = $this->parseArray(array('[/url]', '[/rul]'), array());
		if (isset($arguments['url'])) { // [url=
			$url = $arguments['url'];
		} elseif (isset($arguments['rul'])) { // [rul=
			$url = $arguments['rul'];
		} else { // [url][/url]
			$url = $content;
		}
		return external_url($url, $content);
	}

	/* todo
	  function bb_mail($arguments = array()) {
	  return $this->bb-email($arguments);
	  }

	  function bb_email($arguments = array()){
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

	function bb_neuzen($arguments = array()) {
		if (is_array($arguments)) {
			$content = $this->parseArray(array('[/neuzen]'), array());
		} else {
			$content = $arguments;
		}
		if (LidInstellingenModel::get('layout', 'neuzen') != 'nee') {
			$neus = Icon::getTag('bullet_red', null, null, 'neus2013', 'o');
			$content = str_replace('o', $neus, $content);
		}
		return $content;
	}

	function bb_citaat($arguments = array()) {
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
		$text = '<div class="citaatContainer">Citaat';
		$van = '';
		if (isset($arguments['citaat'])) {
			$van = trim(str_replace('_', ' ', $arguments['citaat']));
		}
		$profiel = ProfielModel::getLink($van, 'user');
		if ($profiel) {
			$text .= ' van ' . $profiel;
		} elseif ($van != '') {
			if (isset($arguments['url']) AND url_like($arguments['url'])) {
				$text .= ' van ' . external_url($arguments['url'], $van);
			} else {
				$text .= ' van ' . $van;
			}
		}
		$text .= ':<div class="citaat">' . trim($content) . '</div></div>';
		return $text;
	}

	/**
	 * Geef de relatieve datum terug.
	 */
	function bb_reldate($arguments = array()) {
		$content = $this->parseArray(array('[/reldate]'), array());
		return '<span title="' . htmlspecialchars($content) . '">' . reldate($content) . '</span>';
	}

	/**
	 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
	 *
	 * Example:
	 * [lid=0436] => Am. Waagmeester
	 * of
	 * [lid]0436[/lid]
	 */
	function bb_lid($arguments = array()) {
		if (isset($arguments['lid'])) {
			$uid = $arguments['lid'];
		} else {
			$uid = $this->parseArray(array('[/lid]'), array());
		}
		$uid = trim($uid);
		$link = ProfielModel::getLink($uid, 'user');
		if (!$link) {
			return '[lid] ' . htmlspecialchars($uid) . '] &notin; db.';
		}
		return $link;
	}

	/**
	 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
	 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
	 */
	function bb_prive($arguments = array()) {
		if (isset($arguments['prive'])) {
			$permissie = $arguments['prive'];
		} else {
			$permissie = 'P_LOGGED_IN';
		}
		if (!LoginModel::mag($permissie)) {
			$this->bb_mode = false;
			$forbidden = array('prive');
		} else {
			$forbidden = array();
		}
		// content moet altijd geparsed worden, anders blijft de inhoud van de tag gewoon staan
		$content = '<span class="bb-prive">' . $this->parseArray(array('[/prive]'), $forbidden) . '</span>';
		if (!LoginModel::mag($permissie)) {
			$content = '';
			$this->bb_mode = true;
		}
		return $content;
	}

	/**
	 * Toont content als instelling een bepaalde waarde heeft,
	 * standaard 'ja';
	 *
	 * [instelling=maaltijdblokje module=voorpagina][maaltijd=next][/instelling]
	 */
	function bb_instelling($arguments = array()) {
		$content = $this->parseArray(array('[/instelling]'), array());
		if (!array_key_exists('instelling', $arguments) OR ! isset($arguments['instelling'])) {
			return 'Geen of een niet bestaande instelling opgegeven: ' . htmlspecialchars($arguments['instelling']);
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
			if (LidInstellingenModel::get($arguments['module'], $arguments['instelling']) == $testwaarde) {
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
	function bb_query($arguments = array()) {
		if (isset($arguments['query'])) {
			$queryID = $arguments['query'];
		} else {
			$queryID = $this->parseArray(array('[/query]'), array());
		}
		$queryID = (int) $queryID;

		if ($queryID != 0) {
						$sqc = new SavedQueryContent(new SavedQuery($queryID));

			return $sqc->render_queryResult();
		} else {
			return '[query] Geen geldig query-id opgegeven.<br />';
		}
	}

	/**
	 * Laat de embedded spotify player zien
	 */
	function bb_spotify($arguments = array()) {
		$id = $this->parseArray(array('[/spotify]'), array());
		if (isset($arguments['spotify'])) { // [spotify=
			$id = $arguments['spotify'];
		}

		if (startsWith($id, 'spotify')) { // Spotify uri
			$uri = $id;
		} elseif (startsWith($id, 'https')) { // Link naar afspeellijst
			$uri = preg_replace('/.+\/(\w+)\/(\w+)\/(\w+)\/(\w+)$/', 'spotify:$1:$2:$3:$4', $id);
		} else {
			return '[spotify] Geen geldige url (' . htmlspecialchars($id) . ')';
		}

		$uri = html_entity_decode($uri);

		# stiekem is het formaat altijd breed ?
		$width = 580;
		$height = 80;
		$class = '';

		if (isset($arguments['formaat'])) {
			$formaat = $arguments['formaat'];
			if ($formaat == "hoog") {
				$width = 300;
				$height = 380;
			} elseif ($formaat == "blok") {
				$width = 80;
				$height = 80;
				$class = "class='float-left'"; # Blokje float in tekst
			}
		}

		return "<iframe $class
					src=\"https://embed.spotify.com/?uri=$uri\"
					width=\"$width\" height=\"$height\"
					frameborder=\"0\" allowtransparency=\"true\"></iframe>";
	}

	function bb_youtube($arguments = array()) {
		$id = $this->parseArray(array('[/youtube]'), array());
		if (isset($arguments['youtube'])) { // [youtube=
			$id = $arguments['youtube'];
		}
		if (preg_match('/^[0-9a-zA-Z\-_]{11}$/', $id)) {

			$attributes['width'] = 570;
			$attributes['height'] = 360;
			$attributes['iframe'] = true;

			$attributes['src'] = '//www.youtube.com/embed/' . $id . '?autoplay=1';
			$previewthumb = 'https://img.youtube.com/vi/' . $id . '/0.jpg';

			return $this->video_preview($attributes, $previewthumb);
		} else {
			return '[youtube] Geen geldig youtube-id (' . htmlspecialchars($id) . ')';
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
	function bb_video($arguments = array()) {
		$content = $this->parseArray(array('[/video]'), array());

		$params['width'] = 570;
		$params['height'] = 360;
		$params['iframe'] = true;
		$previewthumb = '';

		$type = null;
		$id = null;
		$matches = array();

		//match type and id
		if (strstr($content, 'youtube.com') OR strstr($content, 'youtu.be')) {
			$type = 'youtube';
			if (preg_match('#(?:youtube\.com/watch\?v=|youtu.be/)([0-9a-zA-Z\-_]{11})#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//www.youtube.com/embed/' . $id . '?autoplay=1';
			$previewthumb = 'https://img.youtube.com/vi/' . $id . '/0.jpg';
		} elseif (strstr($content, 'vimeo')) {
			$type = 'vimeo';
			if (preg_match('#vimeo\.com/(?:clip\:)?(\d+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//player.vimeo.com/video/' . $id . '?autoplay=1';

			$videodataurl = 'http://vimeo.com/api/v2/video/' . $id . '.php';
			$data = '';
			$downloader = new UrlDownloader;
			if ($downloader->isAvailable()) {
				$data = $downloader->file_get_contents($videodataurl);
			}
			if ($data) {
				$data = unserialize($data);
				$previewthumb = $data[0]['thumbnail_medium'];
			}
		} elseif (strstr($content, 'dailymotion')) {
			$type = 'dailymotion';
			if (preg_match('#dailymotion\.com/video/([a-z0-9]+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//www.dailymotion.com/embed/video/' . $id . '?autoPlay=1';
			$previewthumb = 'http://www.dailymotion.com/thumbnail/video/' . $id;
		} elseif (strstr($content, 'godtube')) {
			$type = 'godtube';
			if (preg_match('#godtube\.com/watch/\?v=([a-zA-Z0-9]+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['id'] = $id;
			$params['iframe'] = false;

			$previewthumb = 'http://www.godtube.com/resource/mediaplayer/' . $id . '.jpg';
		}

		if (empty($type) OR empty($id)) {
			return '[video] Niet-ondersteunde video-website (' . htmlspecialchars($content) . ')';
		}
		return $this->video_preview($params, $previewthumb);
	}

	function video_preview(array $params, $previewthumb) {

		$params = json_encode($params);

		$html = <<<HTML
<div class="bb-video">
	<div class="bb-video-preview" onclick="event.preventDefault();bbvideoDisplay(this);" data-params='{$params}' title="Klik om de video af te spelen">
		<div class="play-button fa fa-play-circle-o fa-5x"></div>
		<div class="bb-img-loading" src="{$previewthumb}"></div>
	</div>
</div>
HTML;

		return $html;
	}

	function bb_twitter($arguments = array()) {
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
			}).render().setUser('" . htmlspecialchars($content) . "').start();
			</script>";
		return $html;
	}

	protected function groep(AbstractGroep $groep) {
				// Controleer rechten
		if (!$groep->mag(AccessAction::Bekijken)) {
			return '';
		}
		$view = new GroepView($groep, null, false, true);
		return $view->getHtml();
	}

	protected function bb_ketzer($arguments = array()) {
		if (isset($arguments['ketzer'])) {
			$id = $arguments['ketzer'];
		} else {
			$id = $this->parseArray(array('[/ketzer]'), array());
		}
		$groep = KetzersModel::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Ketzer met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/ketzers/beheren">Zoeken</a>';
		}
	}

	protected function bb_activiteit($arguments = array()) {
		if (isset($arguments['activiteit'])) {
			$id = $arguments['activiteit'];
		} else {
			$id = $this->parseArray(array('[/activiteit]'), array());
		}
		$groep = ActiviteitenModel::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Activiteit met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/activiteiten/beheren">Zoeken</a>';
		}
	}

	protected function bb_bestuur($arguments = array()) {
		if (isset($arguments['bestuur'])) {
			$id = $arguments['bestuur'];
		} else {
			$id = $this->parseArray(array('[/bestuur]'), array());
		}
		$groep = BesturenModel::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Bestuur met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/besturen/beheren">Zoeken</a>';
		}
	}

	protected function bb_commissie($arguments = array()) {
		if (isset($arguments['commissie'])) {
			$id = $arguments['commissie'];
		} else {
			$id = $this->parseArray(array('[/commissie]'), array());
		}
		$groep = CommissiesModel::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Commissie met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/commissies/beheren">Zoeken</a>';
		}
	}

	protected function bb_werkgroep($arguments = array()) {
		if (isset($arguments['werkgroep'])) {
			$id = $arguments['werkgroep'];
		} else {
			$id = $this->parseArray(array('[/werkgroep]'), array());
		}
		$groep = WerkgroepenModel::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Werkgroep met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/werkgroepen/beheren">Zoeken</a>';
		}
	}

	protected function bb_woonoord($arguments = array()) {
		if (isset($arguments['woonoord'])) {
			$id = $arguments['woonoord'];
		} else {
			$id = $this->parseArray(array('[/woonoord]'), array());
		}
		$groep = WoonoordenModel::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Woonoord met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/woonoorden/beheren">Zoeken</a>';
		}
	}

	protected function bb_ondervereniging($arguments = array()) {
		if (isset($arguments['ondervereniging'])) {
			$id = $arguments['ondervereniging'];
		} else {
			$id = $this->parseArray(array('[/ondervereniging]'), array());
		}
		$groep = OnderverenigingenModel::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Ondervereniging met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/onderverenigingen/beheren">Zoeken</a>';
		}
	}

	/**
	 * Geeft een groep met kortebeschrijving en een lijstje met leden weer.
	 * Als de groep aanmeldbaar is komt er ook een aanmeldknopje bij.
	 *
	 * [groep]123[/groep]
	 * of
	 * [groep=123]
	 */
	protected function bb_groep($arguments = array()) {
		if (isset($arguments['groep'])) {
			$id = $arguments['groep'];
		} else {
			$id = $this->parseArray(array('[/groep]'), array());
		}
		$groep = RechtenGroepenModel::get($id);
		if (!$groep) {
			$groep = RechtenGroepenModel::omnummeren($id);
		}
		if ($groep) {
			return $this->groep($groep);
		} else {
			return 'Groep met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/overig/beheren">Zoeken</a>';
		}
	}

	/**
	 * Geeft een link naar de verticale.
	 *
	 * [verticale]A[/verticale]
	 * of
	 * [verticale=A]
	 */
	protected function bb_verticale($arguments = array()) {
		if (isset($arguments['verticale'])) {
			$letter = $arguments['verticale'];
		} else {
			$letter = $this->parseArray(array('[/groep]'), array());
		}
		try {
			$verticale = VerticalenModel::get($letter);
			return '<a href="/verticalen#' . $verticale->letter . '">' . $verticale->naam . '</a>';
		} catch (Exception $e) {
			return 'Verticale met letter=' . htmlspecialchars($letter) . ' bestaat niet. <a href="/verticalen">Zoeken</a>';
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
	protected function bb_boek($arguments = array()) {
		if (isset($arguments['boek'])) {
			$boekid = $arguments['boek'];
		} else {
			$boekid = $this->parseArray(array('[/boek]'), array());
		}

						try {
			$boek = new BiebBoek((int) $boekid);
			$content = new BoekBBView($boek);
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
	protected function bb_document($arguments = array()) {
		if (isset($arguments['document'])) {
			$id = $arguments['document'];
		} else {
			$id = $this->parseArray(array('[/document]'), array());
		}
				try {
			$document = new Document((int) $id);
			$content = new DocumentBBContent($document);
			return $content->getHtml();
		} catch (Exception $e) {
			return '<div class="bb-document">[document] Ongeldig document (id:' . $id . ')</div>';
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
	public function bb_maaltijd($arguments = array()) {
		if (isset($arguments['maaltijd'])) {
			$mid = $arguments['maaltijd'];
		} else {
			$mid = $this->parseArray(array('[/maaltijd]'), array());
		}
		$mid = trim($mid);
		$maaltijd2 = null;

								try {
			if ($mid === 'next' || $mid === 'eerstvolgende' || $mid === 'next2' || $mid === 'eerstvolgende2') {
				$maaltijden = MaaltijdenModel::instance()->getKomendeMaaltijdenVoorLid(LoginModel::getUid()); // met filter
				$aantal = sizeof($maaltijden);
				if ($aantal < 1) {
					return 'Geen aankomende maaltijd.';
				}
				$maaltijd = reset($maaltijden);
				if (endsWith($mid, '2') && $aantal >= 2) {
					unset($maaltijden[$maaltijd->maaltijd_id]);
					$maaltijd2 = reset($maaltijden);
				}
			} elseif (preg_match('/\d+/', $mid)) {
				$maaltijd = MaaltijdenModel::instance()->getMaaltijdVoorKetzer((int) $mid); // met filter
				if (!$maaltijd) {
					return '';
				}
			}
		} catch (Exception $e) {
			if (strpos($e->getMessage(), 'Not found') !== false) {
				return '<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' . htmlspecialchars($mid) . '</div>';
			}
			return $e->getMessage();
		}
		if (!isset($maaltijd)) {
			return '<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' . htmlspecialchars($mid) . '</div>';
		}
		$aanmeldingen = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorLid(array($maaltijd->maaltijd_id => $maaltijd), LoginModel::getUid());
		if (empty($aanmeldingen)) {
			$aanmelding = null;
		} else {
			$aanmelding = $aanmeldingen[$maaltijd->maaltijd_id];
		}
		$ketzer = new MaaltijdKetzerView($maaltijd, $aanmelding);
		$result = $ketzer->getHtml();

		if ($maaltijd2 !== null) {
			$aanmeldingen2 = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorLid(array($maaltijd2->maaltijd_id => $maaltijd2), LoginModel::getUid());
			if (empty($aanmeldingen2)) {
				$aanmelding2 = null;
			} else {
				$aanmelding2 = $aanmeldingen2[$maaltijd2->maaltijd_id];
			}
			$ketzer2 = new MaaltijdKetzerView($maaltijd2, $aanmelding2);
			$result .= $ketzer2->getHtml();
		}
		return $result;
	}

	/**
	 * tekst = ubboff
	 */
	function bb_tekst($arguments = array()) {
		return $this->bb_ubboff($arguments);
	}

	/**
	 * Vanonderwerp = offtopic
	 */
	function bb_vanonderwerp($arguments = array()) {
		return $this->bb_offtopic($arguments);
	}

	/**
	 * OT = offtopic
	 */
	function bb_ot($arguments = array()) {
		return $this->bb_offtopic($arguments);
	}

	public function bb_offtopic($arguments = array()) {
		$content = $this->parseArray(array('[/ot]', '[/offtopic]', '[/vanonderwerp]'), array());
		return '<span class="offtopic">' . $content . '</span>';
	}

	/**
	 * Verklapper = spoiler
	 */
	function bb_verklapper($arguments = array()) {
		return $this->bb_spoiler($arguments);
	}

	public function bb_spoiler($arguments = array()) {
		$content = $this->parseArray(array('[/spoiler]', '[/verklapper]'), array());
		return '<button class="spoiler">Toon verklapper</button><div class="spoiler-content">' . $content . '</div>';
	}

	function bb_1337($arguments = array()) {
		$html = $this->parseArray(array('[/1337]'), array());
		$html = str_replace('er ', '0r ', $html);
		$html = str_replace('you', 'j00', $html);
		$html = str_replace('elite', '1337', $html);
		$html = strtr($html, "abelostABELOST", "48310574831057");
		return $html;
	}

	function bb_b() {
		if ($this->nobold === true AND $this->quote_level == 0) {
			return $this->parseArray(array('[/b]'), array('b'));
		} else {
			return '<span class="dikgedrukt"><strong>' . $this->parseArray(array('[/b]'), array('b')) . '</strong></span>';
		}
	}

	function bb_i() {
		return '<span class="cursief"><em>' . $this->parseArray(array('[/i]'), array('i')) . '</em></span>';
	}

	function bb_s() {
		return '<span class="doorgestreept"><del>' . $this->parseArray(array('[/s]'), array('s')) . '</del></span>';
	}

	function bb_u() {
		return '<span class="onderstreept"><ins>' . $this->parseArray(array('[/u]'), array('u')) . '</ins></span>';
	}

	function bb_rn() {
		return '<br />';
	}

	function bb_clear($arguments = array()) {
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
	public function bb_mededelingen($arguments = array()) {
		if (isset($arguments['mededelingen'])) {
			$type = $arguments['mededelingen'];
		} else {
			$type = $this->parseArray(array('[/mededelingen]'), array());
		}
		if ($type == '') {
			return '[mededelingen] Geen geldig mededelingenblok.';
		}


		$MededelingenView = new MededelingenView(0);
		switch ($type) {
			case 'top3nietleden': //lekker handig om dit intern dan weer anders te noemen...
				return $MededelingenView->getTopBlock('nietleden');
			case 'top3leden':
				return $MededelingenView->getTopBlock('leden');
			case 'top3oudleden':
				return $MededelingenView->getTopBlock('oudleden');
		}
		return '[mededelingen] Geen geldig type (' . htmlspecialchars($type) . ').';
	}

	/**
	 * Commentaar niet weergeven
	 */
	function bb_commentaar($arguments = array()) {
		$this->bb_mode = false;
		$content = $this->parseArray(array('[/commentaar]'), array());
		$this->bb_mode = true;
		return '';
	}

	/**
	 * Locatie = map in hoverIntentContent
	 */
	function bb_locatie($arguments = array()) {
		$address = $this->parseArray(array('[/locatie]'), array());
		$map = $this->maps(htmlspecialchars($address), $arguments);
		return '<span class="hoverIntent"><a href="https://maps.google.nl/maps?q=' . htmlspecialchars($address) . '">' . $address . Icon::getTag('map', null, 'Kaart', 'text') . '</a><div class="hoverIntentContent">' . $map . '</div></span>';
	}

	/**
	 * Kaart = map
	 */
	function bb_kaart($arguments = array()) {
		return $this->bb_map($arguments);
	}

	/**
	 * Google-maps
	 *
	 * @author Piet-Jan Spaans
	 *
	 * [map h=100]Oude Delft 9[/map]
	 */
	public function bb_map($arguments = array()) {
		$address = $this->parseArray(array('[/map]', '[/kaart]'), array());
		return $this->maps(htmlspecialchars($address), $arguments);
	}

	public static function maps($address, array $arguments) {
		if (trim($address) == '') {
			return 'Geen adres opgegeven';
		}
		// Hoogte maakt niet veel uit
		if (isset($arguments['h']) AND $arguments['h'] <= 900) {
			$height = (int) $arguments['h'];
		} else {
			$height = 450;
		}

		return '<iframe height="' . $height . '" frameborder="0" style="border:0;width:100%"
src="https://www.google.com/maps/embed/v1/search?q=' . $address . '&key=' . GOOGLE_EMBED_KEY . '"></iframe>';
	}

	/**
	 * Peiling
	 *
	 * @author Piet-Jan Spaans
	 *
	 * [peiling=2]
	 * of
	 * [peiling]2[/peiling]
	 */
	public function bb_peiling($arguments = array()) {
		if (isset($arguments['peiling'])) {
			$peiling_id = $arguments['peiling'];
		} else {
			$peiling_id = $this->parseArray(array('[/peiling]'), array());
		}
				try {
			$peiling = PeilingenModel::instance()->getPeilingById((int) $peiling_id);
			$peilingcontent = new PeilingView($peiling);
			return $peilingcontent->getHtml();
		} catch (Exception $e) {
			return '[peiling] Er bestaat geen peiling met (id:' . (int) $peiling_id . ')';
		}
	}

	private $slideshowJsIncluded = false;

	/**
	 * Slideshow-tag.
	 *
	 * example:
	 * [slideshow]http://example.com/image_1.jpg[/slideshow]
	 */
	public function bb_slideshow($arguments = array()) {
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
				$content .= '<script type="text/javascript" src="/layout/js/bb-slideshow.js"></script>';
				$this->slideshowJsIncluded = true;
			}
		}
		return '<div class="bb-slideshow">' . $content . '</div>';
	}

	/**
	 * Blokje met bijbelrooster voor opgegeven aantal dagen.
	 *
	 * [bijbelrooster=10]
	 * of
	 * [bijbelrooster]10[/bijbelrooster]
	 */
	public function bb_bijbelrooster($arguments = array()) {
		if (isset($arguments['bijbelrooster'])) {
			$dagen = $arguments['bijbelrooster'];
		} else {
			$dagen = $this->parseArray(array('[/bijbelrooster]'), array());
		}
				$view = new BijbelroosterBBView($dagen);
		return $view->getHtml();
	}

	function bb_bijbel($arguments = array()) {
		$content = $this->parseArray(array('[/bijbel]'), array());
		if (isset($arguments['bijbel'])) { // [bijbel=
			$stukje = str_replace('_', ' ', $arguments['bijbel']);
		} else { // [bijbel][/bijbel]
			$stukje = $content;
		}
		if (isset($arguments['vertaling'])) {
			$vertaling = $arguments['vertaling'];
		} else {
			$vertaling = null;
		}
		return self::getBijbelLink($stukje, $vertaling, true);
	}

	public static function getBijbelLink($stukje, $vertaling = null, $tag = false) {
		if (!LidInstellingenModel::instance()->isValidValue('algemeen', 'bijbel', $vertaling)) {
			$vertaling = null;
		}
		if ($vertaling === null) {
			$vertaling = LidInstellingenModel::get('algemeen', 'bijbel');
		}
		$link = 'https://www.debijbel.nl/bijbel/' . $vertaling . '/' . $stukje;
		if ($tag) {
			return '<a href="' . $link . '" target="_blank">' . $stukje . '</a>';
		} else {
			return $link;
		}
	}

	function bb_ledenmemoryscores($arguments = array()) {
						LedenMemoryScoresModel::instance();
		$groep = null;
		$titel = null;
		/**
		 * BEGIN COPY FROM @see LedenMemoryView.class.php
		 */
		if (isset($arguments['verticale'])) {
			$v = filter_var($arguments['verticale'], FILTER_SANITIZE_STRING);
			if (strlen($v) > 1) {
				$verticale = VerticalenModel::instance()->find('naam LIKE ?', array('%' . $v . '%'), null, null, 1)->fetch();
			} else {
				$verticale = VerticalenModel::get($v);
			}
			if ($verticale) {
				$titel = ' Verticale ' . $verticale->naam;
				$groep = $verticale;
			}
		} elseif (isset($arguments['lichting'])) {
			$l = (int) filter_var($arguments['lichting'], FILTER_SANITIZE_NUMBER_INT);
			if ($l < 1950) {
				$l = LichtingenModel::getJongsteLidjaar();
			}
			$lichting = LichtingenModel::get($l);
			if ($lichting) {
				$titel = ' Lichting ' . $lichting->lidjaar;
				$groep = $lichting;
			}
		}
		/**
		 * END COPY FROM @see LedenMemoryView.class.php
		 */
		$table = new LedenMemoryScoreTable($groep, $titel);
		return $table->view();
	}

}
