<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * @package  HTML_BBCodeParser2
 * @author   Gerrit Uitslag <klapinklapin@gmail.com>
 */


/**
 * Filter for tag to switch off bbcode
 */
class HTML_BBCodeParser2_Filter_Csrblocks extends HTML_BBCodeParser2_Filter {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @var      array
	 */
	protected $_definedTags = array(
		'groep' => array(
					'allowed'    => 'none',
					'attributes' => array('groep' => ''),
					'plugin'     => 'Csrblocks'),
		'lid' => array(
					'allowed'    => 'none',
					'attributes' => array('lid' => ''),
					'plugin'     => 'Csrblocks'),
		'boek' => array(
					'allowed'    => 'none',
					'attributes' => array('boek' => ''),
					'plugin'     => 'Csrblocks'),
		'document' => array(
					'allowed'    => 'none',
					'attributes' => array('document' => ''),
					'plugin'     => 'Csrblocks'),

		'locatie' => array(
					'allowed'    => 'none',
					'attributes' => array(
						'locatie' => '',
						'w'       => '',
						'h'       => '',
						'static'  => ''),
					'plugin'     => 'Csrblocks'),
		'map' => array(
					'allowed'    => 'none',
					'attributes' => array(
						'map'    => '',
						'w'      => '',
						'h'      => '',
						'static' => ''),
					'plugin'     => 'Csrblocks'),
		'kaart' => array(
					'allowed'    => 'none',
					'attributes' => array(
						'kaart'  => '',
						'w'      => '',
						'h'      => '',
						'static' => ''),
					'plugin'     => 'Csrblocks'),

		'peiling' => array(
					'allowed'    => 'none',
					'attributes' => array('peiling' => ''),
					'plugin'     => 'Csrblocks'),
		'slideshow' => array(
					'allowed'    => 'none',
					'attributes' => array('slideshow' => ''),
					'plugin'     => 'Csrblocks'),
		'bijbelrooster' => array(
					'allowed'    => 'none',
					'attributes' => array(
						'bijbelrooster' => '',
						'vertaling'     => ''),
					'plugin'     => 'Csrblocks'),
		'bijbel' => array(
					'allowed'    => 'none',
					'attributes' => array('bijbel' => ''),
					'plugin'     => 'Csrblocks'),
		'mededelingen' => array(
					'allowed'    => 'none',
					'attributes' => array('mededelingen' => ''),
					'plugin'     => 'Csrblocks'),
		'maaltijd' => array(
					'allowed'    => 'none',
					'attributes' => array('maaltijd' => ''),
					'plugin'     => 'Csrblocks'),
		'query' => array(
					'allowed'    => 'none',
					'attributes' => array('query' => ''),
					'plugin'     => 'Csrblocks'),
		'youtube' => array(
					'allowed'    => 'none',
					'attributes' => array('youtube' => ''),
					'plugin'     => 'Csrblocks'),
		'video' => array(
					'allowed'    => 'none',
					'attributes' => array('video' => ''),
					'plugin'     => 'Csrblocks'),
		'twitter' => array(
					'allowed'    => 'none',
					'attributes' => array(
						'twitter' => '',
						'lines'   => '',
						'width'   => '',
						'height'  => ''),
					'plugin'     => 'Csrblocks'),
		'foto' => array(
					'allowed'    => 'none',
					'attributes' => array('foto' => ''),
					'plugin'     => 'Csrblocks'),
		'fotoalbum' => array(
					'allowed'    => 'none',
					'attributes' => array(
						'fotoalbum' => '',
						'rows'      => '',
						'big'       => '',
						'bigfirst'  => '',
						'compact'   => ''),
					'plugin'     => 'Csrblocks'),
		'citaat' => array(
					'allowed'    => 'all',
					'attributes' => array('citaat' => ''),
					'plugin'     => 'Csrblocks'),
		'quote' => array(
					'allowed'    => 'all',
					'attributes' => array('quote' => ''),
					'plugin'     => 'Csrblocks'),
	);

	/**
	 * Number of nested quotes
	 *
	 * @var int
	 */
	protected $quote_level = 0;

	/**
	 * Executes statements before the actual array building starts
	 *
	 * This method should be overwritten in a filter if you want to do
	 * something before the parsing process starts. This can be useful to
	 * allow certain short alternative tags which then can be converted into
	 * proper tags with preg_replace() calls.
	 * The main class walks through all the filters and and calls this
	 * method if it exists. The filters should modify their private $_text
	 * variable.
	 *
	 * @see      $_text
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	protected function _preparse() {
		$options = $this->_options;
		$o = $options['open'];
		$c = $options['close'];
		$oe = $options['open_esc'];
		$ce = $options['close_esc'];

		// [tag=id]
		// [tag]id[/tag]
		// are converted to: [tag=id][/tag]
		$tags = array(
			'groep', 'lid', 'boek', 'document', 'kaart', 'locatie', 'map', 'peiling', 'bijbel', 'bijbelrooster',
			'slideshow'
		);
		$pattern = array();
		$replace = array();
		foreach ($tags as $tag) {
			$pattern[] = "#".$oe.$tag."=([^".$ce."]*?)".$ce."(?!".$oe."/".$tag.$ce.")#Ui"; // [groep=.*] zonder [/groep] erachter
			$pattern[] = "#".$oe.$tag."(?!=)(\s?.*)".$ce."(.+?)".$oe."/".$tag.$ce."#Ui";   // [groep .*].+[/groep]
			$replace[] = $o.$tag."=\$1".    $c.$o."/".$tag.$c;                             // [groep=.*][/groep]
			$replace[] = $o.$tag."=\$2 \$1".$c.$o."/".$tag.$c;                              // [groep=.+ .*][/groep]
		}

		$this->_preparsed = preg_replace($pattern, $replace, $this->_text);
	}

	/**
	 * Geeft een groep met kortebeschrijving en een lijstje met leden weer.
	 * Als de groep aanmeldbaar is komt er ook een aanmeldknopje bij.
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_groep(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['groep'])) {
					$groepid = trim($tag['attributes']['groep']);
				} else {
					$groepid = '';
				}

				require_once 'groepen/groep.class.php';
				require_once 'groepen/groepcontent.class.php';
				try {
					$groep = new OldGroep($groepid);
					$groeptag = new GroepBBContent($groep);
					return $groeptag->getHtml();
				} catch (Exception $e) {
					return '[groep] Geen geldig groep-id (' . htmlspecialchars($groepid) . ')';
				}
				break;
		}
		return false;
	}

	/**
	 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
	 *
	 * Example:
	 * [lid=0436] => Am. Waagmeester
	 * of
	 * [lid]0436[/lid]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	function html_lid(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['lid'])) {
					$uid = trim($tag['attributes']['lid']);
				} else {
					$uid = '';
				}

				$naam = Lid::naamLink($uid, 'user', 'visitekaartje');
				if ($naam !== false) {
					return $naam;
				} else {
					return '[lid] ' . htmlspecialchars($uid) . '] &notin; db.';
				}
				break;
		}
		return false;
	}

	/**
	 * Geeft titel en auteur van een boek.
	 * Een kleine indicator geeft met kleuren beschikbaarheid aan
	 *
	 * [boek]123[/boek]
	 * of
	 * [boek=123]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_boek(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['boek'])) {
					$boekid = trim($tag['attributes']['boek']);
				} else {
					$boekid = '';
				}

				require_once 'bibliotheek/boek.class.php';
				require_once 'bibliotheek/bibliotheekcontent.class.php';
				try {
					$boek = new Boek((int)$boekid);
					$content = new BoekBBContent($boek);
					return $content->view();
				} catch (Exception $e) {
					return '[boek] Boek [boekid:' . (int)$boekid . '] bestaat niet.';
				}
				break;
		}
		return false;
	}

	/**
	 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
	 *
	 * [document]1234[/document]
	 * of
	 * [document=1234]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_document(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['document'])) {
					$id = trim($tag['attributes']['document']);
				} else {
					$id = '';
				}

				require_once 'documenten/documentcontent.class.php';
				try {
					$document = new Document((int)$id);
					$content = new DocumentBBContent($document);
					return $content->getHtml();
				} catch (Exception $e) {
					return '<div class="bb-document">[document] Ongeldig document (id:' . $id . ')</div>';
				}
		}
		return false;
	}

	/**
	 * Google-maps
	 *
	 * [map dynamic=false w=100 h=100]Oude Delft 9[/map]
	 *
	 * @author Piet-Jan Spaans
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_locatie(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['locatie'])) {
					$address = trim($tag['attributes']['locatie']);
				} else {
					$address = '';
				}

				$map = $this->maps(htmlspecialchars($address), $tag['attributes']);

				return
					'<span class="hoverIntent">'
					. '<a href="http://maps.google.nl/maps?q=' . htmlspecialchars($address) . '">'
					. $address . ' <img src="' . CSR_PICS . '/famfamfam/map.png" alt="map" title="Kaart" />'
					. '</a>'
					. '<div class="hoverIntentContent">' . $map . '</div>'
					. '</span>';
		}
		return false;
	}

	/**
	 * Google-maps
	 *
	 * [map dynamic=false w=100 h=100]Oude Delft 9[/map]
	 *
	 * @author Piet-Jan Spaans
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_kaart(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes'][$tag['tag']])) {
					$address = trim($tag['attributes'][$tag['tag']]);
				} else {
					$address = '';
				}

				return $this->maps(htmlspecialchars($address), $tag['attributes']);
		}
		return false;
	}

	/**
	 * map = kaart
	 */
	protected function html_map(array $tag, &$enabled) {
		return $this->html_kaart($tag, $enabled);
	}

	/**
	 * @param string $address
	 * @param array  $arguments
	 * @return string
	 */
	private function maps($address, array $arguments) {
		if (trim($address) == '') {
			return 'Geen adres opgegeven';
		}
		if (isset($arguments['w']) AND $arguments['w'] < 800) {
			$width = (int)$arguments['w'];
		} else {
			$width = 400;
		}
		if (isset($arguments['h']) AND $arguments['h'] < 600) {
			$height = (int)$arguments['h'];
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
		$jscall = "writeStaticGmap('$mapid', '$address', $width, $height);";
		if (!isset($arguments['static'])) {
			$jscall = "$(document).ready(function() {loadGmaps('$mapid','$address');});";
		}
		$html .= '<div class="bb-gmap" id="' . $mapid . '" style="width:' . $width . 'px;height:' . $height . 'px;"></div><script type="text/javascript">' . $jscall . '</script>';
		return $html;
	}

	/**
	 * Peiling
	 *
	 * @author Piet-Jan Spaans
	 *
	 * [peiling=2]
	 * of
	 * [peiling]2[/peiling]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_peiling(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['peiling'])) {
					$peilingid = trim($tag['attributes']['peiling']);
				} else {
					$peilingid = '';
				}

				require_once 'peilingcontent.class.php';
				try {
					$peiling = new Peiling((int)$peilingid);
					$peilingcontent = new PeilingContent($peiling);
					return $peilingcontent->getHtml();
				} catch (Exception $e) {
					return '[peiling] Er bestaat geen peiling met (id:' . (int)$peilingid . ')';
				}
		}
		return false;
	}


	private $slideshowJsIncluded = false;

	/**
	 * Slideshow-tag.
	 *
	 * example:
	 * [slideshow]http://example.com/image_1.jpg[/slideshow]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_slideshow(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['slideshow'])) {
					$content = trim($tag['attributes']['slideshow']);
				} else {
					$content = '';
				}

				$slides_tainted = explode('[br]', $content); //todo BR??
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
					if ($this->slideshowJsIncluded === false) { //TODO check
						$content .= '<script type="text/javascript" src="/layout/js/bb-slideshow.js"></script>';
						$this->slideshowJsIncluded = true;
					}
				}
				return '<div class="bb-slideshow">' . $content . '</div>';
		}
		return false;
	}

	/**
	 * Blokje met bijbelrooster voor opgegeven aantal dagen.
	 *
	 * [bijbelrooster=10]
	 * of
	 * [bijbelrooster]10[/bijbelrooster]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_bijbelrooster(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['bijbelrooster'])) {
					$dagen = trim($tag['attributes']['bijbelrooster']);
				} else {
					$dagen = '';
				}

				require_once 'MVC/view/BijbelroosterView.class.php';
				$view = new BijbelroosterBBView($dagen);
				return $view->getHtml();
		}
		return false;
	}

	/**
	 * [bijbel=stukje vertaling=]
	 * [bijbel vertaling=]stukje[/bijbel]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_bijbel(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['bijbel'])) {
					$stukje = trim($tag['attributes']['bijbel']);
					$stukje = str_replace('_', ' ', $stukje); //oude bbcode ondersteunde geen spaties
				} else {
					$stukje = '';
				}

				if (isset($tag['attributes']['vertaling'])) {
					$vertaling = $tag['attributes']['vertaling'];
				} else {
					$vertaling = null;
				}
				return self::getBijbelLink($stukje, $vertaling, true);
		}
		return false;
	}

	/**
	 * @param string      $stukje
	 * @param string|null $vertaling
	 * @param bool        $tag
	 * @return string
	 */
	public static function getBijbelLink($stukje, $vertaling = null, $tag = false) {
		if (!LidInstellingen::instance()->isValidValue('algemeen', 'bijbel', $vertaling)) {
			$vertaling = null;
		}
		if ($vertaling === null) {
			$vertaling = LidInstellingen::get('algemeen', 'bijbel');
		}
		$link = 'https://www.debijbel.nl/bijbel/zoeken/' . $vertaling . '/' . $stukje;
		if ($tag) {
			return '<a href="' . $link . '" target="_blank">' . $stukje . '</a>';
		} else {
			return $link;
		}
	}

	/**
	 * Deze methode kan de belangrijkste mededelingen (doorgaans een top3) weergeven.
	 *
	 * [mededelingen=top3]
	 * of
	 * [mededeling]top3[/mededeling]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_mededelingen(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['mededelingen'])) {
					$type = trim($tag['attributes']['mededelingen']);
				} else {
					$type = '';
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
				return '[mededelingen] Geen geldig type (' . htmlspecialchars($type) . ').';
		}
		return false;
	}

	/**
	 * Geeft een maaltijdketzer weer met maaltijdgegevens, aantal aanmeldingen en een aanmeldknopje.
	 *
	 * [maaltijd=next], [maaltijd=1234]
	 * of
	 * [maaltijd]next[/maaldijd]
	 * of
	 * [maaltijd]123[/maaltijd]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_maaltijd(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['maaltijd'])) {
					$mid = trim($tag['attributes']['maaltijd']);
				} else {
					$mid = '';
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
						$maaltijd = MaaltijdenModel::getMaaltijdVoorKetzer((int)$mid); // met filter
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
				$aanmeldingen = MaaltijdAanmeldingenModel::getAanmeldingenVoorLid(array($maaltijd->getMaaltijdId() => $maaltijd), \LoginModel::getUid());
				if (empty($aanmeldingen)) {
					$aanmelding = null;
				} else {
					$aanmelding = $aanmeldingen[$maaltijd->getMaaltijdId()];
				}
				$ketzer = new MaaltijdKetzerView($maaltijd, $aanmelding);
				$result = $ketzer->getHtml();

				if ($maaltijd2 !== null) {
					$aanmeldingen2 = MaaltijdAanmeldingenModel::getAanmeldingenVoorLid(array($maaltijd2->getMaaltijdId() => $maaltijd2), \LoginModel::getUid());
					if (empty($aanmeldingen2)) {
						$aanmelding2 = null;
					} else {
						$aanmelding2 = $aanmeldingen2[$maaltijd2->getMaaltijdId()];
					}
					$ketzer2 = new MaaltijdKetzerView($maaltijd2, $aanmelding2);
					$result .= $ketzer2->getHtml();
				}
				return $result;
		}
		return false;
	}


	/**
	 * Deze methode kan resultaten van query's die in de database staan printen in een
	 * tabelletje.
	 *
	 * [query=1] of [query]1[/query]
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_query(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['query'])) {
					$queryID = trim($tag['attributes']['query']);
				} else {
					$queryID = 0;
				}

				$queryID = (int)$queryID;

				if ($queryID != 0) {
					require_once 'savedquery.class.php';
					$sqc = new SavedQueryContent(new SavedQuery($queryID));

					return $sqc->render_queryResult();
				} else {
					return '[query] Geen geldig query-id opgegeven.<br />';
				}
		}
		return false;
	}

	/**
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_youtube(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['youtube'])) {
					$id = trim($tag['attributes']['youtube']);
				} else {
					$id = '';
				}

				if (preg_match('/^[0-9a-zA-Z\-_]{11}$/', $id)) {

					$attr['width'] = 570;
					$attr['height'] = 360;
					$attr['iframe'] = true;

					$attr['src'] = '//www.youtube.com/embed/' . $id . '?autoplay=1';
					$previewthumb = 'http://img.youtube.com/vi/' . $id . '/0.jpg';

					return $this->video_preview($attr, $previewthumb);
				} else {
					return '[youtube] Geen geldig youtube-id (' . htmlspecialchars($id) . ')';
				}
		}
		return false;
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
	 *        force    Forceer weergave filmpje ook als het al een keer op de pagina voorkomt.
	 *        width    Breedte van het filmpje
	 *        height    Hoogte van het filmpje
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_video(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				if (isset($tag['attributes']['video'])) {
					$content = trim($tag['attributes']['video']);
				} else {
					$content = '';
				}

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
					$previewthumb = 'http://img.youtube.com/vi/' . $id . '/0.jpg';
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
		return false;
	}

	private function video_preview(array $params, $previewthumb) {

		$params = json_encode($params);

		$html = <<<HTML
<div class="bb-video">
	<div class="bb-video-preview" onclick="bbvideoDisplay(this)" data-params='$params' title="Klik om de video af te spelen">
		<div class="play-button"></div>
		<div class="bb-img-loading" src="$previewthumb"></div>
	</div>
</div>
HTML;

		return $html;
	}

	/**
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_twitter(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				$arguments = $tag['attributes'];
				if (isset($arguments['twitter'])) {
					$content = trim($arguments['twitter']);
				} else {
					$content = '';
				}

				// widget size
				$lines = 4;
				$width = 355;
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

				$html = <<<HTML
			<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
			<script>
			new TWTR.Widget({
			  version: 2,
			  type: 'profile',
HTML;
				$html .= " rpp: " . $lines . ",
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
		return false;
	}


	/**
	 * [foto]/pad/naar/foto[/foto]
	 *
	 * Toont de thumbnail met link naar fotoalbum.
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_foto(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				require_once 'MVC/controller/FotoAlbumController.class.php';

				if (isset($tag['attributes']['foto'])) {
					$url = urldecode(trim($tag['attributes']['foto']));
				} else {
					$url = '';
				}

				$parts = explode('/', $url);
				if (in_array('Posters', $parts)) {
					$groot = true;
				} else {
					$groot = false;
				}
				$filename = str_replace('#', '', array_pop($parts)); // replace # (foolproof)
				$path = PICS_PATH . 'fotoalbum' . implode('/', $parts);
				$album = FotoAlbumModel::instance()->getFotoAlbum($path);
				if (!$album) {
					return '<div class="bb-block">Fotoalbum niet gevonden: ' . $url . '</div>';
				}
				$foto = new Foto($album, $filename);
				$fototag = new FotoBBView($foto, $groot);
				return $fototag->getHtml();
		}
		return false;
	}

	/**
	 * [fotoalbum]/pad/naar/album[/fotoalbum]
	 *
	 * Parameters:
	 *    rows    Aantal regels weergeven
	 *            rows=4
	 *
	 *    big        Lijstje met indexen van afbeeldingen die groot moeten
	 *            worden.
	 *            big=0,5,14 | big=a | big=b |
	 *
	 *    compact    Compacte versie van de tag weergeven
	 *            compact=true
	 *
	 *  bigfirst
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_fotoalbum(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				require_once 'MVC/controller/FotoAlbumController.class.php';

				$arguments = $tag['attributes'];
				if (isset($arguments['fotoalbum'])) {
					$url = urldecode(trim($arguments['fotoalbum']));
				} else {
					$url = '';
				}

				if ($url === 'laatste') {
					$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
				} else {
					//vervang url met pad
					$url = str_ireplace(CSR_ROOT, '', $url);
					$path = PICS_PATH;
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
					return '<div class="bb-block">Fotoalbum niet gevonden: /' . $url . '</div>';
				}
				if (count($album->getFotos()) < 1) {
					return '<div class="bb-block">Fotoalbum bevat geen foto\'s: /' . $url . '</div>';
				}
				$fotoalbumtag = new FotoAlbumBBView($album);
				if ($this->quote_level > 0 || isset($arguments['compact'])) {
					$fotoalbumtag->makeCompact();
				}
				if (isset($arguments['rows'])) {
					$fotoalbumtag->setRows((int)$arguments['rows']);
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
				return $fotoalbumtag->getHtml();
		}
		return false;
	}

	/**
	 * Citaat, geneste citaten worden compact weergegeven
	 *
	 * @param array  $tag
	 * @param string $enabled (reference) modify unmatched text or use htmlspecialchar()
	 * @return false|string html or false for using default
	 */
	protected function html_citaat(array $tag, &$enabled) {
		switch ($tag['type']) {
			case 1:
				$arguments = $tag['attributes'];
				if (isset($arguments[$tag['tag']])) {
					$van = trim($arguments[$tag['tag']]);
					$van = trim(str_replace('_', ' ', $van)); //backward compatible
				} else {
					$van = '';
				}

				$spreker = '';
				$lid = Lid::naamLink($van, 'user', 'visitekaartje');
				if ($lid !== false) {
					$spreker = ' van ' . $lid;
				} elseif ($van !== '') {
					if (isset($arguments['url']) AND url_like($arguments['url'])) {
						$spreker = ' van ' . internal_url($arguments['url'], $van);
					} else {
						$spreker = ' van ' . $van;
					}
				}

				$this->quote_level++;

				$html = '<div class="citaatContainer">Citaat' . $spreker . ':';
				$html .= '<div class="citaat">';
				if ($this->quote_level > 1) {
					$html .= '<div onclick="$(this).children(\'.citaatpuntjes\').slideUp();$(this).children(\'.meercitaat\').slideDown();">'
						   . '<div class="meercitaat verborgen">';
				}
				return $html;
			case 2:
				$html = '';
				if ($this->quote_level > 1) {
					$html .= '</div>'
						   . '<div class="citaatpuntjes" title="Toon citaat">...</div>'
						   . '</div>';
				}
				$html .= '</div></div>';

				$this->quote_level--;
				return $html;
		}
		return false;
	}

	/**
	 * quote = citaat
	 */
	protected function html_quote(array $tag, &$enabled) {
		$this->html_citaat($tag, $enabled);
	}
}
