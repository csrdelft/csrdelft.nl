<?php

namespace CsrDelft\view;

/**
 * Icon dingetje voor csrdelft.nl.
 *
 * Gaat samen met 'layout/css/icons.less' en 'layout/css/icons.png'
 *
 * Icon::getTag('bewerken'); geeft <span class="ico pencil"></span>
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class Icon {
	//handige dingen die we graag gebruiken in csrdelft.nl. Moeten geen namen zijn die al voorkomen
	//in de lijst met icons.
	public static $alias = array(
		// algemeen
		'toevoegen' => 'plus',
		'bewerken' => 'pencil',
		'verwijderen' => 'trash',
		'alert' => 'stop',
		'goedkeuren' => 'circle-check',
		'verjaardag' => 'cake-candles',
		'vraagteken' => 'circle-question',
		'fout' => 'error',
		'show' => 'eye',
		//documumenten
		'mime-onbekend' => 'page_white',
		'mime-audio' => 'sound',
		'mime-html' => 'page_white_world',
		'mime-word' => 'page_white_word',
		'mime-excel' => 'page_white_excel',
		'mime-powerpoint' => 'page_white_powerpoint',
		'mime-image' => 'page_white_picture',
		'mime-pdf' => 'page_white_acrobat',
		'mime-plain' => 'page_white_text',
		'mime-zip' => 'page_white_zip',
		// forum
		'citeren' => 'comments',
		'slotje' => 'lock',
		'plakkerig' => 'note',
		'belangrijk' => 'asterisk_orange',
		// corvee
		'taken_bewerken' => 'text_list_bullets',
		'punten_bewerken' => 'award_star_gold_1',
		'punten_bewerken_toegekend' => 'award_star_gold_2',
		'gemaild' => 'email_go',
		'gemaildoranje' => 'email_go_orange',
		'niet_gemaild' => 'email',
		// profiel
		'stats' => 'server_chart',
		'su' => 'user_go',
		'resetpassword' => 'user_gray',
		'instellingen' => 'cog',
		// mededelingen
		'legenda' => 'tag_yellow',
		// Melding
		'alert-danger' => 'exclamation',
		'alert-info' => 'information',
		'alert-success' => 'accept',
		'alert-warning' => 'bell',
		// Overig
		'table' => 'table_normal',
		'log' => 'report',

		'calendar' => 'fas fa-calendar',
		'forum' => 'fas fa-comments',
		'profiel' => 'fas fa-user',
		'fotoalbum' => 'fas fa-camera',
		'document' => 'fas fa-file',
		'Woonoord' => 'fas fa-home',
		'Commissie' => 'fas fa-users',
		'Ondervereniging' => 'fas fa-users',
		'Kring' => 'fas fa-circle-notch',
		'note' => 'fas fa-sticky-note',
		'boek' => 'fas fa-book',
	);

	public static function get($key) {
		if (array_key_exists($key, self::$alias)) {
			return self::$alias[$key];
		} else {
			return $key;
		}
	}

	/**
	 * @param string $key Naam van het icoon, mag een alias zijn
	 * @param null $hover string Naam van het icoon bij muis-over
	 * @param null $title string Titel van het icoon
	 * @param string $class
	 * @param null $content string Inhoud van dit icoon, is verborgen in de browser, maar wordt wel
	 * geselecteerd en door eventuele schermlezers opgevangen
	 * @return string
	 */
	public static function getTag($key, $hover = null, $title = null, $class = null) {
		$icon = self::get($key);

		if ($hover !== null) {
			$hover = 'hover-' . self::get($hover);
		}
		if ($title !== null) {
			$title = 'title="' . str_replace('&amp;', '&', htmlspecialchars($title)) . '" ';
		}

		return sprintf('<i class="fas fa-%s %s %s" title="%s"></i>', htmlspecialchars($icon), htmlspecialchars($hover), htmlspecialchars($class), htmlspecialchars($title));
	}
}
