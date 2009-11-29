<?php
/*
 * C.S.R. Delft pubcie@csrdelft.nl
 *
 * Instelling houdt instellingen bij voor gebruikers. In de sessie en in
 * het profiel van leden.
 * De array met instellingen wordt geserialiseerd opgeslagen, als iemand zin
 * heeft kan het op termijn netjes in een losse tabel in de database.
 */

class Instelling{

	/*
	 * Instellingarray, een naampje, met een default-value en een type.
	 */
	private static $instellingen=array(
			'algemeen_sneltoetsen' => array('nee', 'Sneltoetsen op de webstek', 'enum', array('ja', 'nee')),
			'layout_rozeWebstek' => array('nee', 'Webstek roze maken', 'enum', array('ja', 'nee')),
			'forum_onderwerpenPerPagina' => array(15, 'Onderwerpen per pagina', 'int', 5), //deze hebben een minimum, anders gaat het forum stuk.
			'forum_postsPerPagina' => array(25, 'Berichten per pagina', 'int', 10),
			'forum_naamWeergave' => array('civitas', 'Naamweergave', 'enum', array('civitas', 'volledig', 'bijnaam', 'aaidrom')),
			'forum_zoekresultaten' => array(40, 'Zoekresultaten', 'int'),
			'forum_toonpasfotos' => array('nee', 'Pasfoto\'s standaard weergeven', 'enum', array('ja', 'nee')),
			'zijbalk_gasnelnaar' => array('ja', 'Ga snel naar weergeven', 'enum', array('ja', 'nee')),
			'zijbalk_agendaweken' => array(2, 'Aantal weken vooruit agenda weergeven', 'int'),
			'zijbalk_mededelingen' => array(8, 'Aantal mededelingen in zijbalk', 'int'),
			'zijbalk_forum' => array(10, 'Aantal forumberichten in zijbalk', 'int'),
			'zijbalk_forum_zelf' => array(0, 'Aantal zelf geposte forumberichten zijbalk', 'int'),
			'zijbalk_verjaardagen_pasfotos' => array('ja', 'Toon pasfoto\'s bij verjaardagen', 'enum', array('ja', 'nee')),
			'zijbalk_verjaardagen' => array(10, 'Aantal verjaardagen in zijbalk', 'int'),
			'voorpagina_maaltijdblokje' => array('ja', 'Eerstvolgende maaltijd weergeven', 'enum', array('ja', 'nee')),
			'groepen_toonPasfotos' => array('ja', 'Standaard pasfotos tonen', 'enum', array('ja', 'nee'))
	);

	//hebben we een instelling die $key heet?
	public static function has($key){			return array_key_exists($key, self::$instellingen); }
	public static function getDefault($key){	return self::$instellingen[$key][0]; }
	public static function getDescription($key){return self::$instellingen[$key][1]; }
	public static function getType($key){		return self::$instellingen[$key][2]; }
	public static function getEnumOptions($key){
		if(self::getType($key)=='enum'){
			return self::$instellingen[$key][3];
		}
		return false;
	}
	
	
	public static function get($key){
		//als er nog niets in SESSION staat, herladen.
		if(!isset($_SESSION['instellingen'])){
			self::reload();
		}
		if(!self::has($key)){
			throw new Exception('Deze instelling  bestaat niet');
		}
		//als deze instelling nog niet in SESSION staat, maar we em wel kennen, die er instoppen.
		if(!isset($_SESSION['instellingen'][$key])){
			$_SESSION['instellingen'][$key]=self::getDefault($key);
		}
		return $_SESSION['instellingen'][$key];
	}
	
	public static function set($key, $value){
		if(!isset($_SESSION['instellingen'])){
			self::reload();
		}
		if(!self::has($key)){
			throw new Exception('Deze instelling  bestaat niet');
		}
		switch(self::getType($key)){
			case 'int':
				$value=(int)$value;
				//check op minimum
				if(isset(self::$instellingen[$key][3]) AND $value<self::$instellingen[$key][3]){
					$value=self::$instellingen[$key][3];
				}
			break;
			case 'enum':
				//als $value niet een van de toegestane waarden is
				//de standaardwaarde teruggeven.
				if(!in_array($value, self::getEnumOptions($key))){
					$value=self::getDefault($key);
				}
			break;
		}
		$_SESSION['instellingen'][$key]=$value;
	}
	public static function clear(){
		unset($_SESSION['instellingen']);		
	}
	public static function reload(){
		if(is_array(LoginLid::instance()->getLid()->getInstellingen())){
			$_SESSION['instellingen']=LoginLid::instance()->getLid()->getInstellingen();
		}else{
			$_SESSION['instellingen']=Instelling::getDefaults();
		}
	}
	public static function save(){
		$lid=LoginLid::instance()->getLid();
		$lid->setProperty('instellingen', $_SESSION['instellingen']);
		return $lid->save();
	}	

	//standaardwaarden teruggeven.
	public static function getDefaults(){
		$return=array();
		foreach(self::$instellingen as $key => $instelling){
			$return[$key]=$instelling[0];
		}
		return $return;
	}

}
