<?php
/*
 * Icon dingetje voor csrdelft.nl.
 *
 *
 * Icon::get('bewerken'); geeft bijvoorbeeld http://plaetjes.csrdelft.nl/famfamfam/pencil.png
 */
define('ICON_PATH', PICS_PATH.'/famfamfam/');

class Icon{

	//array met alle icons
	public static $index=null;

	//handige dingen die we graag gebruiken in csrdelft.nl. Moeten geen namen zijn die al voorkomen
	//in de lijst met icons.
	public static $alias=array(
		// algemeen
		'toevoegen' => 'add',
		'bewerken' => 'pencil',
		'verwijderen' => 'cross',
		'alert' => 'stop',
		'goedkeuren' => 'tick',
		'verjaardag' => 'cake',

		//documumenten
		'mime-audio' => 'ipod',
		'mime-word' => 'page_white_word',
		'mime-excel' => 'page_white_excel',
		'mime-powerpoint' => 'page_white_powerpoint',
		'mime-image' => 'page_white_picture',
		'mime-onbekend' => 'page_white',
		'mime-pdf' => 'page_white_acrobat',
		'mime-plain' => 'page_white_text',
		'mime-zip' => 'page_white_zip',
		
		// forum
		'citeren' => 'comments',
		'slotje' => 'lock',
		'plakkerig' => 'note',
		
		// corvee
		'taken_bewerken' => 'text_list_bullets',
		'punten_bewerken' => 'award_star_gold_1',
		'punten_bewerken_toegekend' => 'award_star_gold_2',
		'gemaild' => 'email_open',
		'niet_gemaild' => 'email',

		//profiel
		'stats' => 'server_chart',
		'su' => 'user_go',
		'resetpassword' => 'user_gray',
		'instellingen' => 'cog'
	);

	
	private static function loadIndex(){
		if(!file_exists(ICON_PATH.'.index')){
			self::generateIndex();
		}
		self::$index=explode(',', file_get_contents( ICON_PATH.'.index'));
	}
	
	public static function has($key){
		if(self::$index===null){ self::loadIndex(); }
		//Bestaat er een $key in de index of een alias met met $key?
		return in_array($key, self::$index) OR (self::isAlias($key) AND self::has(self::getKeyForAlias($key)));
	}
	public static function isAlias($alias){
		return array_key_exists($alias, self::$alias);
	}
	public static function getKeyForAlias($alias){
		if(!self::isAlias($alias)){
			throw new Exception('Alias ('.$alias.') bestaat niet');
		}
		return self::$alias[$alias];
	}
	
	public static function get($key){
		if(!self::has($key)){
			throw new Exception('Icon ('.$key.') bestaat niet in images/famfamfam/');
		}
		if(in_array($key, self::$index)){
			return CSR_PICS.'famfamfam/'.$key.'.png';
		}else{
			return CSR_PICS.'famfamfam/'.self::$alias[$key].'.png';
		}
	}
	public static function getTag($key, $class='icon'){
		$icon=self::get($key);
		return '<img src="'.$icon.'" alt="'.$key.'" class="'.htmlspecialchars($class).'" />';
	}

	/*
	 * Bouw de index op in een bestand in de image-map.
	 */
	public static function generateIndex(){
		$handler = opendir(ICON_PATH);
		while($file = readdir($handler)) {
			//we willen geen directories en geen verborgen bestanden.
			if(!is_dir(ICON_PATH.$file) AND substr($file,0,1)!='.' AND substr($file, -3)=='png'){
				$icons[]=substr($file, 0, (strlen($file)-4));
			}
		}
		closedir($handler);
		file_put_contents(ICON_PATH.'.index', implode($icons, ','));
	}
}
?>
