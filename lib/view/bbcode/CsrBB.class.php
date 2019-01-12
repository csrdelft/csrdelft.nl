<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\common\CsrException;
use CsrDelft\common\Ini;
use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\documenten\DocumentModel;
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
use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\SavedQuery;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bibliotheek\BoekBBView;
use CsrDelft\view\formulier\UrlDownloader;
use CsrDelft\view\fotoalbum\FotoAlbumBBView;
use CsrDelft\view\fotoalbum\FotoBBView;
use CsrDelft\view\groepen\GroepView;
use CsrDelft\view\Icon;
use CsrDelft\view\ledenmemory\LedenMemoryScoreTable;
use CsrDelft\view\ledenmemory\LedenMemoryView;
use CsrDelft\view\maalcie\persoonlijk\MaaltijdKetzerView;
use CsrDelft\view\mededelingen\MededelingenView;
use CsrDelft\view\SavedQueryContent;

/**
 * CsrBB.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class CsrBB extends Parser {

	/**
	 * BBcode within email is limited.
	 *
	 * @var boolean
	 */
	protected $email_mode = false;

	/**
	 * Light-weight front-end agnostic mode.
	 *
	 * @var boolean
	 */
	protected $light_mode = false;

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

	public static function parseMail($bbcode, $light = false) {
		$parser = new CsrBB();
		$parser->email_mode = true;
		$parser->light_mode = $light;
		return $parser->getHtml($bbcode);
	}

	public static function parseLight($bbcode) {
		$parser = new CsrBB();
		$parser->light_mode = true;
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
	 * Templates for light mode
	 */
	private function lightLinkInline($tag, $url, $content) {
	    if ($this->email_mode && isset($url[0]) && $url[0] === '/') {
	        // Zorg voor werkende link in e-mail
	        $url = CSR_ROOT . $url;
        }

		return <<<HTML
			<a class="bb-link-inline bb-tag-{$tag}" href="{$url}">{$content}</a>
HTML;
	}

	private function lightLinkBlock($tag, $url, $titel, $beschrijving, $thumbnail = '') {
		$titel = htmlspecialchars($titel);
		$beschrijving = htmlspecialchars($beschrijving);
		if ($thumbnail !== '') {
			$thumbnail = '<img src="' . $thumbnail . '" />';
		}
		return <<<HTML
			<a class="bb-link-block bb-tag-{$tag}" href="{$url}">
				{$thumbnail}
				<h2>{$titel}</h2>
				<p>{$beschrijving}</p>
			</a>
HTML;
	}

	private function lightLinkImage($tag, $url) {
		return <<<HTML
			<a class="bb-link-image bb-tag-{$tag}" href="{$url}"></a>
HTML;
	}

	private function lightLinkThumbnail($tag, $url, $thumbnail) {
		return <<<HTML
			<a class="bb-link-thumbnail bb-tag-{$tag}" href="{$url}">
				<img src="{$thumbnail}" />
			</a>
HTML;
	}

	/**
	 * Image
	 *
	 * @param optional String $arguments['class'] Class attribute
	 * @param optional String $arguments['float'] CSS float left or right
	 * @param optional Integer $arguments['w'] CSS width in pixels
	 * @param optional Integer $arguments['h'] CSS height in pixels
	 *
	 * @example [img class=special float=left w=20 h=50]URL[/img]
	 */
	function bb_img($arguments = array()) {
		$url = $this->parseArray(array('[/img]', '[/IMG]'), array());
		$url = filter_var($url, FILTER_SANITIZE_URL);
		if (!$url OR (!url_like($url) AND !startsWith($url, '/plaetjes/'))) {
			return $url;
		}
		if ($this->light_mode) {
			return $this->lightLinkImage('img', $url);
		}
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
			$style .= 'width: ' . ((int)$arguments['w']) . 'px; ';
		}
		if (isset($arguments['h']) AND $arguments['h'] > 10) {
			$style .= 'height: ' . ((int)$arguments['h']) . 'px;';
		}
		if ($this->email_mode) {
			return '<img class="bb-img ' . $class . '" src="' . $url . '" alt="' . htmlspecialchars($url) . '" style="' . $style . '" />';
		}
		return '<div class="bb-img-loading" src="' . $url . '" title="' . htmlspecialchars($url) . '" style="' . $style . '"></div>';
	}

	/**
	 * Toont de thumbnail van een foto met link naar fotoalbum.
	 *
	 * @param optional Boolean $arguments['responsive'] Responsive sizing
	 *
	 * @example [foto responsive]/pad/naar/foto[/foto]
	 */
	function bb_foto($arguments = array()) {
		$url = urldecode($this->parseArray(array('[/foto]'), array()));
		$parts = explode('/', $url);
		$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
		$path = PHOTOALBUM_PATH . 'fotoalbum' . implode('/', $parts);
		$album = FotoAlbumModel::instance()->getFotoAlbum($path);
		if (!$album) {
			return '<div class="bb-block">Fotoalbum niet gevonden: ' . htmlspecialchars($url) . '</div>';
		}
		$foto = new Foto($filename, $album);
		if (!$foto) {
			return '';
		}
		if ($this->light_mode) {
			$link = $foto->getAlbumUrl() . '#' . $foto->getResizedUrl();
			$thumb = CSR_ROOT . $foto->getThumbUrl();
			return $this->lightLinkThumbnail('foto', $link, $thumb);
		}
		$groot = in_array('Posters', $parts);
		$responsive = isset($arguments['responsive']);
		$fototag = new FotoBBView($foto, $groot, $responsive);
		return $fototag->getHtml();
	}

	/**
	 * Fotoalbum
	 *
	 * Albumweergave (default):
	 * @param optional Boolean $arguments['compact'] Compacte weergave
	 * @param optional Integer $arguments['rows'] Aantal rijen
	 * @param optional Integer $arguments['perrow'] Aantal kolommen
	 * @param optional Boolean $arguments['bigfirst'] Eerste foto groot
	 * @param optional String $arguments['big'] Indexen van foto's die groot moeten, of patroon 'a', 'b' of 'c'
	 *
	 * @example [fotoalbum compact bigfirst]/pad/naar/album[/fotoalbum]
	 * @example [fotoalbum rows=2 perrow=5 big=a]/pad/naar/album[/fotoalbum]
	 * @example [fotoalbum big=0,5,14]/pad/naar/album[/fotoalbum]
	 *
	 * Sliderweergave:
	 * @param optional Boolean $arguments['slider'] Slider weergave
	 * @param optional Integer $arguments['interval'] Slider interval in seconden
	 * @param optional Boolean $arguments['random'] Slider met random volgorde
	 * @param optional Boolean $arguments['height'] Slider hoogte in pixels
	 *
	 * @example [fotoalbum slider interval=10 random height=200]/pad/naar/album[/fotoalbum]
	 * @example [fotoalbum]laatste[/fotoalbum]
	 * @return string
	 */
	protected function bb_fotoalbum($arguments = array()) {
		$url = urldecode($this->parseArray(array('[/fotoalbum]'), array()));
		if ($url === 'laatste') {
			$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
		} else {
			//vervang url met pad
			$url = str_ireplace(CSR_ROOT, '', $url);
			$path = PHOTOALBUM_PATH;
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
		if ($this->light_mode) {
			$beschrijving = count($album->getFotos()) . ' foto\'s';
			$cover = CSR_ROOT . $album->getCoverUrl();
			return $this->lightLinkBlock('fotoalbum', $album->getUrl(), $album->dirname, $beschrijving, $cover);
		}
		if (isset($arguments['slider'])) {
			$view = view('fotoalbum.slider', [
				'fotos' => array_shuffle($album->getFotos())
			]);
			if (isset($arguments['height'])) {
				$view->height = (int)$arguments['height'];
			}
			if (isset($arguments['interval'])) {
				$view->interval = (int)$arguments['interval'];
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
				$view->setRows((int)$arguments['rows']);
			}
			if (isset($arguments['perrow'])) {
				$view->setPerRow((int)$arguments['perrow']);
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

	/**
	 * URL
	 *
	 * @param String $arguments ['url'] URL waarnaar gelinkt wordt
	 *
	 * @example [url]https://csrdelft.nl[/url]
	 * @example [url=https://csrdelft.nl]Stek[/url]
	 */
	function bb_url($arguments = array()) {
		$content = $this->parseArray(array('[/url]', '[/rul]'), array());
		if (isset($arguments['url'])) { // [url=
			$url = $arguments['url'];
		} elseif (isset($arguments['rul'])) { // [rul=
			$url = $arguments['rul'];
		} else { // [url][/url]
			$url = $content;
		}
		if ($this->light_mode) {
			return $this->lightLinkInline('url', $url, $content);
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

	/**
	 * 2013 Neuzen
	 *
	 * @example [neuzen]2o13[/neuzen]
	 */
	function bb_neuzen($arguments = array()) {
		$content = $this->parseArray(array('[/neuzen]'), array());
		if ($this->light_mode) {
			return $content;
		}
		if (LidInstellingenModel::get('layout', 'neuzen') != 'nee') {
			$neus = Icon::getTag('bullet_red', null, null, 'neus2013', 'o');
			$content = str_replace('o', $neus, $content);
		}
		return $content;
	}

	/**
	 * Citaat
	 *
	 * @param optional String $arguments['citaat'] Naam of lidnummer van wie geciteerd wordt
	 * @param optional String $arguments['url'] Link naar bron van het citaat
	 *
	 * @example [citaat=1234]Citaat[/citaat]
	 * @example [citaat=Jan_Lid url=https://csrdelft.nl]Citaat[/citaat]
	 * @example [citaat]Citaat[/citaat]
	 */
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
			if ($this->light_mode) {
				$content = '...';
			}
		}
		$text = '<div class="citaatContainer bb-tag-citaat">Citaat';
		$van = '';
		if (isset($arguments['citaat'])) {
			$van = trim(str_replace('_', ' ', $arguments['citaat']));
		}
		$profiel = ProfielModel::get($van);
		if ($profiel) {
			if ($this->light_mode) {
				$text .= ' van ' . $this->lightLinkInline('lid', '/profiel/' . $profiel->uid, $profiel->getNaam('user'));
			} else {
				$text .= ' van ' . $profiel->getLink('user');
			}
		} elseif ($van != '') {
			if (isset($arguments['url']) AND url_like($arguments['url'])) {
				if ($this->light_mode) {
					$text .= ' van ' . $this->lightLinkInline('url', $arguments['url'], $van);
				} else {
					$text .= ' van ' . external_url($arguments['url'], $van);
				}
			} else {
				$text .= ' van ' . $van;
			}
		}
		$text .= ':<div class="citaat">' . trim($content) . '</div></div>';
		return $text;
	}

	/**
	 * Relatieve datum zoals geparsed door php's strtotime
	 *
	 * @example [reldate]1 day ago[/reldate]
	 * @example [reldate]20-01-2012[/reldate]
	 * @example [reldate]20-01-2012 18:00[/reldate]
	 */
	function bb_reldate($arguments = array()) {
		$content = $this->parseArray(array('[/reldate]'), array());
		return '<span class="bb-tag-reldate" title="' . htmlspecialchars($content) . '">' . reldate($content) . '</span>';
	}

	/**
	 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
	 *
	 * @example [lid=0436]
	 * @example [lid]0436[/lid]
	 */
	function bb_lid($arguments = array()) {
		if (isset($arguments['lid'])) {
			$uid = $arguments['lid'];
		} else {
			$uid = $this->parseArray(array('[/lid]'), array());
		}
		$uid = trim($uid);
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			return '[lid] ' . htmlspecialchars($uid) . '] &notin; db.';
		}
		if ($this->light_mode) {
			return $this->lightLinkInline('lid', '/profiel/' . $uid, $profiel->getNaam('user'));
		}
		return $profiel->getLink('user');
	}

	/**
	 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
	 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
	 *
	 * @param optional String $arguments['prive'] Permissie nodig om de tekst te lezen
	 *
	 * @example [prive]Persoonsgegevens[/prive]
	 * @example [prive=commissie:PubCie]Tekst[/prive]
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
		$content = '<span class="bb-prive bb-tag-prive">' . $this->parseArray(array('[/prive]'), $forbidden) . '</span>';
		if (!LoginModel::mag($permissie)) {
			$content = '';
			$this->bb_mode = true;
		}
		return $content;
	}

	/**
	 * Toont content als instelling een bepaalde waarde heeft, standaard 'ja';
	 *
	 * @param String $arguments ['instelling'] Naam instelling
	 * @param String $arguments ['module'] Naam module
	 * @param optional String $arguments['waarde'] Waarde waarbij content wordt weergegeven
	 *
	 * @example [instelling=maaltijdblokje module=voorpagina][maaltijd=next][/instelling]
	 */
	function bb_instelling($arguments = array()) {
		$content = $this->parseArray(array('[/instelling]'), array());
		if (!array_key_exists('instelling', $arguments) OR !isset($arguments['instelling'])) {
			return 'Geen of een niet bestaande instelling opgegeven: ' . htmlspecialchars($arguments['instelling']);
		}
		if (!array_key_exists('module', $arguments) OR !isset($arguments['module'])) { // backwards compatibility
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
		} catch (CsrException $e) {
			return '[instelling]: ' . $e->getMessage();
		}
	}

	/**
	 * Deze methode kan resultaten van query's die in de database staan printen in een
	 * tabelletje.
	 *
	 * @example [query=1]
	 * @example [query]1[/query]
	 */
	function bb_query($arguments = array()) {
		if (isset($arguments['query'])) {
			$queryID = $arguments['query'];
		} else {
			$queryID = $this->parseArray(array('[/query]'), array());
		}
		$queryID = (int)$queryID;

		if ($queryID != 0) {
			$sqc = new SavedQueryContent(new SavedQuery($queryID));
			if ($this->light_mode) {
				$url = '/tools/query.php?id=' . urlencode($queryID);
				return $this->lightLinkBlock('query', $url, $sqc->getModel()->getBeschrijving(), $sqc->getModel()->count() . ' regels');
			}
			return $sqc->render_queryResult();
		} else {
			return '[query] Geen geldig query-id opgegeven.<br />';
		}
	}

	/**
	 * Laat de embedded spotify player zien
	 *
	 * @param optional String $arguments['formaat'] Ander formaar: 'hoog' of 'blok'
	 *
	 * @example [spotify]https://open.spotify.com/user/.../playlist/...[/spotify]
	 * @example [spotify]spotify:user:...:playlist:...[/spotify]
	 */
	function bb_spotify($arguments = array()) {
		$uri = $this->parseArray(array('[/spotify]'), array());
		if (isset($arguments['spotify'])) { // [spotify=
			$uri = $arguments['spotify'];
		}

		if (!startsWith($uri, 'spotify') && !filter_var($uri, FILTER_VALIDATE_URL)) {
			return '[spotify] Geen geldige url (' . $uri . ')';
		}

		if ($this->light_mode) {
			$url = 'https://open.spotify.com/' . str_replace(':', '/', str_replace('spotify:', '', $uri));
			if (strstr($uri, 'playlist')) {
				$beschrijving = 'Afspeellijst';
			} elseif (strstr($uri, 'album')) {
				$beschrijving = 'Album';
			} elseif (strstr($uri, 'track')) {
				$beschrijving = 'Nummer';
			} else {
				$beschrijving = '';
			}
			return $this->lightLinkBlock('spotify', $url, 'Spotify', $beschrijving);
		}

		$commonAttributen = "src=\"https://embed.spotify.com/?uri=$uri\" frameborder=\"0\" allowtransparency=\"true\"";

		if (isset($arguments['formaat'])) {
			$formaat = $arguments['formaat'];
			if ($formaat == "hoog") {
				return "<iframe width=\"300\" height=\"380\" $commonAttributen></iframe>";
			} elseif ($formaat == "blok") {
				return "<iframe width=\"80\" height=\"80\" class=\"float-left\" $commonAttributen></iframe>";
			}
		}

		return "<iframe class=\"w-100\" height=\"80\" $commonAttributen></iframe>";
	}

	/**
	 * YouTube speler
	 *
	 * @param String $arguments ['youtube'] YouTube id van 11 tekens
	 *
	 * @example [youtube]dQw4w9WgXcQ[/youtube]
	 * @example [youtube=dQw4w9WgXcQ]
	 */
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

			if ($this->light_mode) {
				return $this->lightLinkBlock('youtube', 'https://youtu.be/' . $id, 'YouTube video', '', $previewthumb);
			}

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
	 * @example [video]https://www.youtube.com/watch?v=Zo0LJrw5nCs[/video]
	 * @example [video]Zo0LJrw5nCs[/video]
	 * @example [video]https://vimeo.com/1582112[/video]
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
			$type = 'YouTube';
			if (preg_match('#(?:youtube\.com/watch\?v=|youtu.be/)([0-9a-zA-Z\-_]{11})#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//www.youtube.com/embed/' . $id . '?autoplay=1';
			$previewthumb = 'https://img.youtube.com/vi/' . $id . '/0.jpg';
		} elseif (strstr($content, 'vimeo')) {
			$type = 'Vimeo';
			if (preg_match('#vimeo\.com/(?:clip\:)?(\d+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//player.vimeo.com/video/' . $id . '?autoplay=1';

			$videodataurl = 'https://vimeo.com/api/v2/video/' . $id . '.php';
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
			$type = 'DailyMotion';
			if (preg_match('#dailymotion\.com/video/([a-z0-9]+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//www.dailymotion.com/embed/video/' . $id . '?autoPlay=1';
			$previewthumb = 'https://www.dailymotion.com/thumbnail/video/' . $id;
		} elseif (strstr($content, 'godtube')) {
			$type = 'GodTube';
			if (preg_match('#godtube\.com/watch/\?v=([a-zA-Z0-9]+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['id'] = $id;
			$params['iframe'] = false;

			$previewthumb = 'https://www.godtube.com/resource/mediaplayer/' . $id . '.jpg';
		}

		if (empty($type) OR empty($id)) {
			return '[video] Niet-ondersteunde video-website (' . htmlspecialchars($content) . ')';
		}
		if ($this->light_mode) {
			return $this->lightLinkBlock('video', $content, $type . ' video', '', $previewthumb);
		}
		return $this->video_preview($params, $previewthumb);
	}

	function video_preview(array $params, $previewthumb) {
		$params = json_encode($params);

		$html = <<<HTML
<div class="bb-video">
	<div class="bb-video-preview" onclick="event.preventDefault();window.bbcode.bbvideoDisplay(this);" data-params='{$params}' title="Klik om de video af te spelen">
		<div class="play-button fa fa-play-circle-o fa-5x"></div>
		<div class="bb-img-loading" src="{$previewthumb}"></div>
	</div>
</div>
HTML;

		return $html;
	}

	/**
	 * Twitter widget
	 *
	 * @param optional Integer $arguments['lines']
	 * @param optional Integer $arguments['width'] Breedte
	 * @param optional Integer $arguments['height'] Hoogte
	 *
	 * @example [twitter][/twitter]
	 */
	function bb_twitter($arguments = array()) {
		$content = $this->parseArray(array('[/twitter]'), array());

		if ($this->light_mode) {
			$url = 'https://twitter.com/' . $content;
			return $this->lightLinkBlock('twitter', $url, 'Twitter', 'Tweets van @' . $content);
		}

		// widget size
		$width = 580;
		$height = 300;
		if (isset($arguments['lines']) AND (int)$arguments['lines'] > 0) {
			$lines = (int)$arguments['lines'];
		}
		if (isset($arguments['width']) AND (int)$arguments['width'] > 100) {
			$width = (int)$arguments['width'];
		}
		if (isset($arguments['height']) AND (int)$arguments['height'] > 100) {
			$height = (int)$arguments['height'];
		}

		$script = <<<HTML
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
HTML;

		if (preg_match('/status/', $content)) {
			return <<<HTML
<blockquote class="twitter-tweet" data-lang="nl" data-dnt="true" data-link-color="#0a338d">
	<a href="{$content}">Tweet op Twitter</a>
</blockquote>
{$script}
HTML;
		}

		if (startsWith($content, '@')) {
			$content = 'https://twitter.com/' . $content;
		}

		return <<<HTML
<a class="twitter-timeline" 
	 data-lang="nl" data-width="{$width}" data-height="{$height}" data-dnt="true" data-theme="light"data-link-color="#0a338d" 
	 href="https://twitter.com/{$content}">
	 	Tweets van {$content}
</a>
{$script}
HTML;

	}

	protected function groep(AbstractGroep $groep, $tag, $leden) {
		// Controleer rechten
		if (!$groep->mag(AccessAction::Bekijken)) {
			return '';
		}
		if ($this->light_mode) {
			return $this->lightLinkBlock($tag, $groep->getUrl(), $groep->naam, $groep->aantalLeden() . ' ' . $leden);
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
			return $this->groep($groep, 'ketzer', 'aanmeldingen');
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
			return $this->groep($groep, 'activiteit', 'aanmeldingen');
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
			return $this->groep($groep, 'bestuur', 'personen');
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
			return $this->groep($groep, 'commissie', 'leden');
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
			return $this->groep($groep, 'werkgroep', 'aanmeldingen');
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
			return $this->groep($groep, 'woonoord', 'bewoners');
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
			return $this->groep($groep, 'ondervereniging', 'leden');
		} else {
			return 'Ondervereniging met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/onderverenigingen/beheren">Zoeken</a>';
		}
	}

	/**
	 * Geeft een groep met kortebeschrijving en een lijstje met leden weer.
	 * Als de groep aanmeldbaar is komt er ook een aanmeldknopje bij.
	 *
	 * @example [groep]123[/groep]
	 * @example [groep=123]
	 */
	protected function bb_groep($arguments = array()) {
		if (isset($arguments['groep'])) {
			$id = $arguments['groep'];
		} else {
			$id = $this->parseArray(array('[/groep]'), array());
		}
		$groep = RechtenGroepenModel::get($id);
		if ($groep) {
			return $this->groep($groep, 'groep', 'personen');
		} else {
			return 'Groep met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="/groepen/overig/beheren">Zoeken</a>';
		}
	}

	/**
	 * Geeft een link naar de verticale.
	 *
	 * @example [verticale]A[/verticale]
	 * @example [verticale=A]
	 */
	protected function bb_verticale($arguments = array()) {
		if (isset($arguments['verticale'])) {
			$letter = $arguments['verticale'];
		} else {
			$letter = $this->parseArray(array('[/verticale]'), array());
		}
		try {
			$verticale = VerticalenModel::get($letter);
			return '<a href="/verticalen#' . $verticale->letter . '">' . $verticale->naam . '</a>';
		} catch (CsrException $e) {
			return 'Verticale met letter=' . htmlspecialchars($letter) . ' bestaat niet. <a href="/verticalen">Zoeken</a>';
		}
	}

	/**
	 * Geeft titel en auteur van een boek.
	 * Een kleine indicator geeft met kleuren beschikbaarheid aan
	 *
	 * @example [boek]123[/boek]
	 * @example [boek=123]
	 */
	protected function bb_boek($arguments = array()) {
		if (isset($arguments['boek'])) {
			$boekid = $arguments['boek'];
		} else {
			$boekid = $this->parseArray(array('[/boek]'), array());
		}

		try {
			$boek = BoekModel::instance()->get((int)$boekid);
			if ($this->light_mode) {
				return $this->lightLinkBlock('boek', $boek->getUrl(), $boek->getTitel(), 'Auteur: ' . $boek->getAuteur());
			}
			$content = new BoekBBView($boek);
			return $content->view();
		} catch (CsrException $e) {
			return '[boek] Boek [boekid:' . (int)$boekid . '] bestaat niet.';
		}
	}

	/**
	 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
	 *
	 * @example [document]1234[/document]
	 * @example [document=1234]
	 */
	protected function bb_document($arguments = array()) {
		if (isset($arguments['document'])) {
			$id = $arguments['document'];
		} else {
			$id = $this->parseArray(array('[/document]'), array());
		}

		$document = DocumentModel::instance()->get($id);

		if ($document) {
			if ($this->light_mode) {
				$beschrijving = $document->getFriendlyMimetype() . ' (' . format_filesize((int)$document->filesize) . ')';
				return $this->lightLinkBlock('document', $document->getDownloadUrl(), $document->naam, $beschrijving);
			}
			return view('documenten.document_bb', ['document' => $document])->getHtml();
		} else {
			return '<div class="bb-document">[document] Ongeldig document (id:' . $id . ')</div>';
		}
	}

	/**
	 * Geeft een maaltijdketzer weer met maaltijdgegevens, aantal aanmeldingen en een aanmeldknopje.
	 *
	 * @example [maaltijd=next]
	 * @example [maaltijd=1234]
	 * @example [maaltijd]next[/maaldijd]
	 * @example [maaltijd]123[/maaltijd]
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
					return '<div class="bb-block bb-maaltijd">Geen aankomende maaltijd.</div>';
				}
				$maaltijd = reset($maaltijden);
				if (endsWith($mid, '2') && $aantal >= 2) {
					unset($maaltijden[$maaltijd->maaltijd_id]);
					$maaltijd2 = reset($maaltijden);
				}
			} elseif (preg_match('/\d+/', $mid)) {
				$maaltijd = MaaltijdenModel::instance()->getMaaltijdVoorKetzer((int)$mid); // met filter
				if (!$maaltijd) {
					return '';
				}
			}
		} catch (CsrException $e) {
			if (strpos($e->getMessage(), 'Not found') !== false) {
				return '<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' . htmlspecialchars($mid) . '</div>';
			}
			return $e->getMessage();
		}
		if (!isset($maaltijd)) {
			return '<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' . htmlspecialchars($mid) . '</div>';
		}
		if ($this->light_mode) {
			$url = $maaltijd->getUrl() . '#' . $maaltijd->maaltijd_id;
			return $this->lightLinkBlock('maaltijd', $url, $maaltijd->titel, $maaltijd->datum . ' ' . $maaltijd->tijd);
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
		return '<span class="offtopic bb-tag-offtopic">' . $content . '</span>';
	}

	/**
	 * Verklapper = spoiler
	 */
	function bb_verklapper($arguments = array()) {
		return $this->bb_spoiler($arguments);
	}

	public function bb_spoiler($arguments = array()) {
		$content = $this->parseArray(array('[/spoiler]', '[/verklapper]'), array());
		if ($this->light_mode) {
			$content = str_replace('[br]', '<br />', $content);
			return '<a class="bb-tag-spoiler" href="#/verklapper/' . urlencode($content) . '">Toon verklapper</a>';
		}
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
			return '<strong class="dikgedrukt bb-tag-b">' . $this->parseArray(array('[/b]'), array('b')) . '</strong>';
		}
	}

	function bb_i() {
		return '<em class="cursief bb-tag-i">' . $this->parseArray(array('[/i]'), array('i')) . '</em>';
	}

	function bb_s() {
		return '<del class="doorgestreept bb-tag-s">' . $this->parseArray(array('[/s]'), array('s')) . '</del>';
	}

	function bb_u() {
		return '<ins class="onderstreept bb-tag-u">' . $this->parseArray(array('[/u]'), array('u')) . '</ins>';
	}

	function bb_rn() {
		return '<br />';
	}

	function bb_clear($arguments = array()) {
		$sClear = 'clear';
		if (isset($arguments['clear']) AND ($arguments['clear'] === 'left' OR $arguments['clear'] === 'right')) {
			$sClear .= '-' . $arguments['clear'];
		}
		return '<div class="' . $sClear . '"></div>';
	}

	/**
	 * Deze methode kan de belangrijkste mededelingen (doorgaans een top3) weergeven.
	 *
	 * @example [mededelingen=top3]
	 * @example [mededelingen]top3[/mededelingen]
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
		if ($this->light_mode) {
			return $this->lightLinkBlock('mededelingen', '/mededelingen', 'Mededelingen', 'Bekijk de laatste mededelingen');
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
		$url = 'https://maps.google.nl/maps?q=' . urlencode($address);
		if ($this->light_mode) {
			return $this->lightLinkInline('locatie', $url, $address);
		}
		$map = $this->maps(htmlspecialchars($address), $arguments);
		return '<span class="hoverIntent"><a href="' . $url . '">' . $address . Icon::getTag('map', null, 'Kaart', 'text') . '</a><div class="hoverIntentContent">' . $map . '</div></span>';
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
	 * @example [map h=100]Oude Delft 9[/map]
	 */
	public function bb_map($arguments = array()) {
		$address = $this->parseArray(array('[/map]', '[/kaart]'), array());
		if ($this->light_mode) {
			$url = 'https://maps.google.nl/maps?q=' . urlencode($address);
			return $this->lightLinkBlock('map', $url, $address, 'Google Maps');
		}
		return $this->maps(htmlspecialchars($address), $arguments);
	}

	public static function maps($address, array $arguments) {
		if (trim($address) == '') {
			return 'Geen adres opgegeven';
		}
		// Hoogte maakt niet veel uit
		if (isset($arguments['h']) AND $arguments['h'] <= 900) {
			$height = (int)$arguments['h'];
		} else {
			$height = 450;
		}

		return '<iframe height="' . $height . '" frameborder="0" style="border:0;width:100%"
src="https://www.google.com/maps/embed/v1/place?q=' . urlencode($address) . '&key=' . Ini::lees(Ini::GOOGLE, 'embed_key') . '"></iframe>';
	}

	/**
	 * Peiling
	 *
	 * @author Piet-Jan Spaans
	 *
	 * @example [peiling=2]
	 * @example [peiling]2[/peiling]
	 */
	public function bb_peiling($arguments = array()) {
		if (isset($arguments['peiling'])) {
			$peiling_id = $arguments['peiling'];
		} else {
			$peiling_id = $this->parseArray(array('[/peiling]'), array());
		}
		try {
			$peiling = PeilingenModel::instance()->getPeilingById((int)$peiling_id);
			if ($peiling === false) {
				throw new CsrException("Peiling bestaat niet");
			}
			if ($this->light_mode) {
				$url = '#/peiling/' . urlencode($peiling_id);
				return $this->lightLinkBlock('peiling', $url, $peiling->titel, $peiling->beschrijving);
			}
			$peilingcontent = view('peilingen.peiling', [
				'peiling' => $peiling,
				'opties' => PeilingenLogic::instance()->getOptionsAsJson($peiling_id, LoginModel::getUid()),
			]);
			return $peilingcontent->getHtml();
		} catch (CsrException $e) {
			return '[peiling] Er bestaat geen peiling met (id:' . (int)$peiling_id . ')';
		}
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
		$link = self::getBijbelLink($stukje, $vertaling, !$this->light_mode);
		if ($this->light_mode) {
			return $this->lightLinkInline('bijbel', $link, $stukje);
		} else {
			return $link;
		}
	}

	public static function getBijbelLink($stukje, $vertaling = null, $tag = false) {
		if (!LidInstellingenModel::instance()->isValidValue('algemeen', 'bijbel', $vertaling)) {
			$vertaling = null;
		}
		if ($vertaling === null) {
			$vertaling = LidInstellingenModel::get('algemeen', 'bijbel');
		}
		$link = 'https://www.debijbel.nl/bijbel/' . urlencode($vertaling) . '/' . urlencode($stukje);
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
			$l = (int)filter_var($arguments['lichting'], FILTER_SANITIZE_NUMBER_INT);
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
		if ($this->light_mode) {
			return $this->lightLinkBlock('ledenmemoryscores', '/forum/onderwerp/8017', 'Ledenmemory Scores', $titel);
		}
		$table = new LedenMemoryScoreTable($groep, $titel);
		return $table->view();
	}

	/**
	 * Heading
	 *
	 * @param Integer $arguments ['h'] Heading level (1-6)
	 * @param optional String $arguments['id'] ID attribute
	 *
	 * @example [h=1 id=special]Heading[/h]
	 */
	function bb_h($arguments) {
		$id = '';
		if (isset($arguments['id'])) {
			$id = ' id="' . htmlspecialchars($arguments['id']) . '"';
		}
		$h = 1;
		if (isset($arguments['h'])) {
			$h = (int)$arguments['h'];
		}
		$text = '<h' . $h . $id . ' class="bb-tag-h">';
		$text .= $this->parseArray(array('[/h]'), array('h'));
		$text .= '</h' . $h . '>' . "\n\n";

		// remove trailing br (or even two)
		$next_tag = array_shift($this->parseArray);

		if ($next_tag != '[br]') {
			array_unshift($this->parseArray, $next_tag);
		} else {
			$next_tag = array_shift($this->parseArray);
			if ($next_tag != '[br]') {
				array_unshift($this->parseArray, $next_tag);
			}
		}
		return $text;
	}

	protected $nobold = false;

	function bb_nobold($arguments = array()) {
		$this->nobold = true;
		$return = $this->parseArray(array('[/nobold]'), array());
		$this->nobold = false;

		return $return;
	}

	/**
	 * Subscript
	 *
	 * @example [sub]Subscript[/sub]
	 */
	function bb_sub() {
		return '<sub class="bb-tag-sub">' . $this->parseArray(array('[/sub]'), array('sub', 'sup')) . '</sub>';
	}

	/**
	 * Superscript
	 *
	 * @example [sup]Superscript[/sup]
	 */
	function bb_sup() {
		return '<sup class="bb-tag-sup">' . $this->parseArray(array('[/sup]'), array('sub', 'sup')) . '</sup>';
	}

	/**
	 * Code
	 *
	 * @param optional String $arguments['code'] Description of code type
	 *
	 * @example [code=PHP]phpinfo();[/code]
	 */
	function bb_code($arguments = array()) {
		$content = $this->parseArray(array('[/code]'), array('code', 'br', 'all' => 'all'));

		$code = '';
		if (isset($arguments['code'])) {
			$code = $arguments['code'] + ' ';
		}

		return '<div class="bb-tag-code"><sub>' . $code . 'code:</sub><pre class="bbcode">' . $content . '</pre></div>';;
	}

	/**
	 * Quote
	 *
	 * @example [quote]Citaat[/quote]
	 */
	function bb_quote() {
		if ($this->quote_level == 0) {
			$this->quote_level = 1;
			$content = $this->parseArray(array('[/quote]'), array());
			$this->quote_level = 0;
		} else {
			$this->quote_level++;
			$delcontent = $this->parseArray(array('[/quote]'), array());
			$this->quote_level--;
			unset($delcontent);
			$content = '...';
		}
		$text = '<div class="citaatContainer bb-tag-quote"><strong>Citaat</strong>' .
			'<div class="citaat">' . $content . '</div></div>';
		return $text;
	}

	/**
	 * List
	 *
	 * @param optional String $arguments['list'] Type of ordered list
	 *
	 * @example [list]Unordered list[/list]
	 * @example [ulist]Unordered list[/ulist]
	 * @example [list=a]Ordered list numbered with lowercase letters[/list]
	 */
	function bb_list($arguments) {
		$content = $this->parseArray(array('[/list]', '[/ulist]'), array('br'));
		if (!isset($arguments['list'])) {
			$text = '<ul class="bb-tag-list">' . $content . '</ul>';
		} else {
			$text = '<ol class="bb-tag-list" type="' . $arguments['list'] . '">' . $content . '</ol>';
		}
		return $text;
	}

	/**
	 * Horizontal line
	 *
	 * @example [hr]
	 */
	function bb_hr($arguments) {
		return '<hr class="bb-tag-hr" />';
	}

	/**
	 * List item (short)
	 *
	 * @example [lishort]First item
	 * @example [*]Next item
	 */
	function bb_lishort($arguments) {
		return '<li class="bb-tag-li">' . $this->parseArray(array('[br]')) . '</li>';
	}

	/**
	 * List item
	 *
	 * @example [li]Item[/li]
	 */
	function bb_li($arguments) {
		return '<li class="bb-tag-li">' . $this->parseArray(array('[/li]')) . '</li>';
	}

	/**
	 * Slash me
	 *
	 * @param optional String $arguments['me'] Name of who is me
	 *
	 * @example [me] waves
	 * @example [me=Name] waves
	 */
	function bb_me($arguments) {
		$content = $this->parseArray(array('[br]'), array());
		array_unshift($this->parseArray, '[br]');
		if (isset($arguments['me'])) {
			$html = '<span style="color:red;">* ' . $arguments['me'] . $content . '</span>';
		} else {
			$html = '<span style="color:red;">/me' . $content . '</span>';
		}
		return $html;
	}

	/**
	 * UBB off
	 *
	 * @example [ubboff]Not parsed[/ubboff]
	 * @example [tekst]Not parsed[/tekst]
	 */
	function bb_ubboff() {
		$this->bb_mode = false;
		$content = $this->parseArray(array('[/ubboff]', '[/tekst]'), array());
		$this->bb_mode = true;
		return $content;
	}

	/**
	 * Email anchor
	 *
	 * @param String $arguments ['email'] Email address to link to
	 * @param optional Boolean $arguments['spamsafe'] Uses spam safe javascript obfuscator
	 *
	 * @example [email]noreply@csrdelft.nl[/email]
	 * @example [email=noreply@csrdelft.nl spamsafe]text[/email]
	 */
	function bb_email($arguments) {
		$html = '';

		$mailto = array_shift($this->parseArray);
		$endtag = array_shift($this->parseArray);

		$email = '';
		$text = '';

		// only valid patterns
		if ($endtag == '[/email]') {
			if (isset($arguments['email'])) {
				if (email_like($arguments['email'])) {
					$email = $arguments['email'];
					$text = $mailto;
				}
			} else {
				if (email_like($mailto)) {
					$email = $text = $mailto;
				}
			}
		} else {
			if (isset($arguments['email'])) {
				if (email_like($arguments['email'])) {
					$email = $text = $arguments['email'];
				}
			}
			array_unshift($this->parseArray, $endtag);
			array_unshift($this->parseArray, $mailto);
		}
		if (!empty($email)) {
			$html = '<a class="bb-tag-email" href="mailto:' . $email . '">' . $text . '</a>';

			//spamprotectie: rot13 de email-tags, en voeg javascript toe om dat weer terug te rot13-en.
			if (isset($arguments['spamsafe'])) {
				$html = '<script>document.write("' . str_rot13(addslashes($html)) . '".replace(/[a-zA-Z]/g, function(c){ return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));</script>';
			}
		} else {
			$html = '[email] Ongeldig e-mailadres (' . htmlspecialchars($mailto) . ')';
		}
		return $html;
	}

	/**
	 * Table
	 *
	 * @param optional String $arguments['border'] CSS border style
	 * @param optional String $arguments['color'] CSS color style
	 * @param optional String $arguments['background-color'] CSS background-color style
	 * @param optional String $arguments['border-collapse'] CSS border-collapse style
	 *
	 * @example [table border=1px_solid_blue]...[/table]
	 */
	function bb_table($arguments) {
		$tableProperties = array('border', 'color', 'background-color', 'border-collapse');
		$style = '';
		foreach ($arguments as $name => $value) {
			if (in_array($name, $tableProperties)) {
				$style .= $name . ': ' . str_replace('_', ' ', htmlspecialchars($value)) . '; ';
			}
		}

		$content = $this->parseArray(array('[/table]'), array('br'));
		$html = '<table class="bb-table bb-tag-table" style="' . $style . '">' . $content . '</table>';
		return $html;
	}

	/**
	 * Table row
	 *
	 * @example [tr]...
	 * @example [tr]...[/tr]
	 */
	function bb_tr() {
		$content = $this->parseArray(array('[/tr]'), array('br'));
		$html = '<tr class="bb-tag-tr">' . $content . '</tr>';
		return $html;
	}

	/**
	 * Table cell
	 *
	 * @param optional Integer $arguments['w'] CSS width in pixels
	 *
	 * @example [td w=50]...[/td]
	 */
	function bb_td($arguments = array()) {
		$content = $this->parseArray(array('[/td]'), array());

		$style = '';
		if (isset($arguments['w'])) {
			$style .= 'width: ' . (int)$arguments['w'] . 'px; ';
		}

		$html = '<td class="bb-tag-td" style="' . $style . '">' . $content . '</td>';
		return $html;
	}

	/**
	 * Table header cell
	 *
	 * @example [th]...[/th]
	 */
	function bb_th() {
		$content = $this->parseArray(array('[/th]'), array());
		$html = '<th class="bb-tag-th">' . $content . '</th>';
		return $html;
	}

	/**
	 * Div
	 *
	 * @param optional String $arguments['class'] Class attribute
	 * @param optional Boolean $arguments['clear'] CSS clear: both
	 * @param optional String $arguments['float'] CSS float left or right
	 * @param optional Integer $arguments['w'] CSS width in pixels
	 * @param optional Integer $arguments['h'] CSS height in pixels
	 *
	 * @example [div class=special clear float=left w=20 h=50]...[/div]
	 */
	function bb_div($arguments = array()) {
		$content = $this->parseArray(array('[/div]'), array());
		$class = '';
		if (isset($arguments['class'])) {
			$class .= htmlspecialchars($arguments['class']);
		}
		if (isset($arguments['clear'])) {
			$class .= ' clear';
		} elseif (isset($arguments['float']) AND $arguments['float'] == 'left') {
			$class .= ' float-left';
		} elseif (isset($arguments['float']) AND $arguments['float'] == 'right') {
			$class .= ' float-right';
		}
		if ($class != '') {
			$class = ' class="bb-tag-div ' . $class . '"';
		}
		$style = '';
		if (isset($arguments['w'])) {
			$style .= 'width: ' . ((int)$arguments['w']) . 'px; ';
		}
		if (isset($arguments['h'])) {
			$style .= 'height: ' . ((int)$arguments['h']) . 'px; ';
		}
		if ($style != '') {
			$style = ' style="' . $style . '" ';
		}
		$title = '';
		if (isset($arguments['title'])) {
			$title = ' title="' . htmlspecialchars(trim(str_replace('_', ' ', $arguments['title']))) . '" ';
		}
		return '<div' . $class . $style . $title . '>' . $content . '</div>';
	}

}
