<?php

/**
 * Icon dingetje voor csrdelft.nl.
 *
 * Icon::get('bewerken'); geeft bijvoorbeeld /plaetjes/famfamfam/pencil.png
 */
class Icon {

	//array met alle icons
	public static $index = null;
	//handige dingen die we graag gebruiken in csrdelft.nl. Moeten geen namen zijn die al voorkomen
	//in de lijst met icons.
	public static $alias = array(
		// algemeen
		'toevoegen'					 => 'add',
		'bewerken'					 => 'pencil',
		'verwijderen'				 => 'cross',
		'alert'						 => 'stop',
		'goedkeuren'				 => 'tick',
		'verjaardag'				 => 'cake',
		'vraagteken'				 => 'help',
		'fout'						 => 'error',
		//documumenten
		'mime-onbekend'				 => 'page_white',
		'mime-audio'				 => 'sound',
		'mime-html'					 => 'page_white_world',
		'mime-word'					 => 'page_white_word',
		'mime-excel'				 => 'page_white_excel',
		'mime-powerpoint'			 => 'page_white_powerpoint',
		'mime-image'				 => 'page_white_picture',
		'mime-pdf'					 => 'page_white_acrobat',
		'mime-plain'				 => 'page_white_text',
		'mime-zip'					 => 'page_white_zip',
		// forum
		'citeren'					 => 'comments',
		'slotje'					 => 'lock',
		'plakkerig'					 => 'note',
		'belangrijk'				 => 'asterisk_orange',
		// corvee
		'taken_bewerken'			 => 'text_list_bullets',
		'punten_bewerken'			 => 'award_star_gold_1',
		'punten_bewerken_toegekend'	 => 'award_star_gold_2',
		'gemaild'					 => 'email_go',
		'gemaildoranje'				 => 'email_go_orange',
		'niet_gemaild'				 => 'email',
		// profiel
		'stats'						 => 'server_chart',
		'su'						 => 'user_go',
		'resetpassword'				 => 'user_gray',
		'instellingen'				 => 'cog',
		// mededelingen
		'legenda'					 => 'tag_yellow'
	);

	private static function loadIndex() {
		if (!file_exists(ICON_PATH . '.index')) {
			self::generateIndex();
		}
		self::$index = explode(',', file_get_contents(ICON_PATH . '.index'));
	}

	public static function has($key) {
		if (self::$index === null) {
			self::loadIndex();
		}
		//Bestaat er een $key in de index of een alias met met $key?
		return in_array($key, self::$index) OR ( self::isAlias($key) AND self::has(self::getKeyForAlias($key)));
	}

	public static function isAlias($alias) {
		return array_key_exists($alias, self::$alias);
	}

	public static function getKeyForAlias($alias) {
		if (!self::isAlias($alias)) {
			throw new Exception('Alias (' . $alias . ') bestaat niet');
		}
		return self::$alias[$alias];
	}

	public static function get($key, $title = null) {
		if (!self::has($key)) {
			throw new Exception('Icon (' . $key . ') bestaat niet in ' . ICON_PATH);
		}
		if (in_array($key, self::$index)) {
			return '/plaetjes/famfamfam/' . $key . '.png';
		} else {
			return '/plaetjes/famfamfam/' . self::$alias[$key] . '.png';
		}
	}

	public static function getTag($key, $title = null, $class = 'icon') {
		$icon = self::get($key);
		if ($title !== null) {
			$title = 'title="' . str_replace('&amp;', '&', htmlspecialchars($title)) . '" ';
		}
		return '<img src="' . $icon . '" width="16" height="16" alt="' . $key . '" ' . $title . 'class="' . htmlspecialchars($class) . '" />';
	}

	/**
	 * Bouw de index op in een bestand in de image-map.
	 */
	public static function generateIndex() {
		$handle = opendir(ICON_PATH);
		if (!$handle) {
			return;
		}
		while ($file = readdir($handle)) {
			//we willen geen directories en geen verborgen bestanden.
			if (!is_dir(ICON_PATH . $file) AND substr($file, 0, 1) != '.' AND substr($file, -3) == 'png') {
				$icons[] = substr($file, 0, (strlen($file) - 4));
			}
		}
		closedir($handle);
		file_put_contents(ICON_PATH . '.index', implode($icons, ','));
	}

}
