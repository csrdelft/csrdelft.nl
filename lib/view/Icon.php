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
		'alert' => 'stop',
		'goedkeuren' => 'circle-check',
		'verjaardag' => 'cake-candles',
		'vraagteken' => 'circle-question',
		'show' => 'eye',
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
		'plakkerig' => 'note',
		'belangrijk' => 'star',
		// profiel
		'stats' => 'chart-line',
		'resetpassword' => 'user-lock',
		'instellingen' => 'gear',
		// melding
		'alert-danger' => 'bell-exclamation',
		'alert-info' => 'bell-on',
		'alert-success' => 'circle-check',
		'alert-warning' => 'bell',
		// Overig
		'table' => 'table_normal',
		'log' => 'report',
	];

	public static $fa = [
		'calendar' => 'fa fa-calendar',
		'forum' => 'fa fa-comments',
		'profiel' => 'fa fa-user',
		'fotoalbum' => 'fa fa-camera',
		'document' => 'fa fa-file',
		'Woonoord' => 'fa fa-home',
		'Commissie' => 'fa fa-users',
		'Ondervereniging' => 'fa fa-users',
		'Kring' => 'fa fa-circle-notch',
		'note' => 'fa fa-sticky-note',
		'boek' => 'fa fa-book',
		'wiki' => 'fa fa-atlas',
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
		$class = null,
		$content = null
	) {
		$icon = self::get($key);

		if (isset(static::$fa[$icon])) {
			return sprintf(
				'<span class="%s %s" title="%s">%s</span>',
				static::$fa[$icon],
				htmlspecialchars($class),
				htmlspecialchars($title),
				htmlspecialchars($content)
			);
		}

		if ($hover !== null) {
			$hover = 'hover-' . self::get($hover);
		}
		if ($title !== null) {
			$title =
				'title="' . str_replace('&amp;', '&', htmlspecialchars($title)) . '" ';
		}

		return sprintf(
			'<span class="ico %s %s %s" %s>%s</span>',
			htmlspecialchars($icon),
			htmlspecialchars($hover),
			htmlspecialchars($class),
			$title,
			htmlspecialchars($content)
		);
	}
}
