<?php

namespace CsrDelft\view;

/**
 * Icon dingetje voor csrdelft.nl.
 *
 * Icon::getTag('bewerken'); geeft <i class="fas fa-pencil"></i>
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class Icon
{
	//handige dingen die we graag gebruiken in csrdelft.nl. Moeten geen namen zijn die al voorkomen
	//in de lijst met icons.
	public static $alias = [
		// algemeen
		'toevoegen' => 'plus',
		'bewerken' => 'pencil',
		'verwijderen' => 'trash',
		'goedkeuren' => 'circle-check',
		'verjaardag' => 'cake-candles',
		'vraagteken' => 'circle-question',
		'tonen' => 'eye',
		'verbergen' => 'eye-slash',
		'sluiten' => 'xmark',
		'kopieren' => 'clipboard',
		'undo' => 'arrow-rotate-left',
		'redo' => 'arrow-rotate-right',
		'alert' => 'stop',
		'stop' => 'ban',
		//documumenten
		'mime-onbekend' => 'file-circle-exclamation',
		'mime-audio' => 'file-audio',
		'mime-html' => 'file-code',
		'mime-word' => 'file-word',
		'mime-excel' => 'file-excel',
		'mime-powerpoint' => 'file-powerpoint',
		'mime-image' => 'file-image',
		'mime-pdf' => 'file-pdf',
		'mime-plain' => 'file-lines',
		'mime-zip' => 'file-zipper',
		// forum
		'citeren' => 'quote-left',
		'slotje' => 'lock',
		'plakkerig' => 'note-sticky',
		'belangrijk' => 'star',
		'ruby' => 'gem',
		'asterisk_orange' => 'star',
		'flag_red' => 'flag', // Backwards compatibility
		'flag_orange' => 'flag',
		'flag_green' => 'flag',
		'flag_blue' => 'flag',
		// profiel
		'stats' => 'chart-line',
		'resetpassword' => 'user-lock',
		'instellingen' => 'gear',
		// melding
		'alert-danger' => 'bell-exclamation',
		'alert-info' => 'bell-on',
		'alert-success' => 'circle-check',
		'alert-warning' => 'bell',
		// overig
		'tabel' => 'table',
		'log' => 'rectangle-terminal',
		'prullenbak' => 'dumpster',

		'calendar' => 'calendar',
		'forum' => 'comments',
		'profiel' => 'user',
		'adres' => 'location-dot',
		'fotoalbum' => 'images',
		'document' => 'file',
		'woonoord' => 'home',
		'commissie' => 'users',
		'ondervereniging' => 'users',
		'kring' => 'circle-notch',
		'boek' => 'book',
		'wiki' => 'book-atlas',
		'rechten' => 'key',
	];

	public static function get($key)
	{
		if (array_key_exists($key, self::$alias)) {
			return self::$alias[$key];
		} else {
			return $key;
		}
	}

	/**
	 * @param string $key Naam van het icoon, mag een alias zijn
	 * @param null $hover string Naam van het icoon bij muis-over
	 * @param string $title string Titel van het icoon
	 * @param string $class
	 * @return string
	 */
	public static function getTag(
		$key,
		$hover = null,
		$title = null,
		$class = null
	) {
		$icon = self::get($key);

		if ($hover !== null) {
			$hover = 'hover-' . self::get($hover);
		}
		if ($title !== null) {
			$title = str_replace('&amp;', '&', htmlspecialchars($title));
		}

		// Test if string contains the a fa brands tag.
		if (strpos($icon, 'fab fa-') !== false) {
			return sprintf(
				'<i class="%s %s %s" title="%s" aria-hidden="true"></i>',
				htmlspecialchars($icon),
				htmlspecialchars($hover),
				htmlspecialchars($class),
				$title
			);
		} else {
			return sprintf(
				'<i class="fas fa-%s %s %s" title="%s" aria-hidden="true"></i>',
				htmlspecialchars($icon),
				htmlspecialchars($hover),
				htmlspecialchars($class),
				$title
			);
		}
	}
}
