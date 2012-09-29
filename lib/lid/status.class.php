<?php
/**
 * Status van leden, met wat eigenschappen zoals:
 * standaard-permissies, omschrijvingen, het status-karakter, etc. 
 */

class Status{
	private $status;

	private static $all=array(
		'S_NOVIET', 'S_LID', 'S_GASTLID',
		'S_OUDLID','S_ERELID',
		'S_KRINGEL',
		'S_OVERLEDEN',
		'S_EXLID',
		'S_NOBODY',
		'S_CIE'
	);

	private $lidLike=array('S_NOVIET', 'S_LID', 'S_GASTLID');
	private $oudlidLike=array('S_OUDLID', 'S_ERELID');
	
	public function __construct($status){
		if(!self::exists($status)){
			throw new Exception('Status bestaat niet');
		}
		$this->status=$status;
	}

	public function __toString(){
		return $this->get();
	}
	
	public function get(){	return $this->status;	}
	
	public function isLid(){ return in_array($this->get(), $this->lidLike); }
	public function isOudlid(){ return in_array($this->get(), $this->oudlidLike); }

	/**
	 * Geef een karakter terug om de status van het huidige lid aan te
	 * duiden. In de loop der tijd zijn ~ voor kringel en • voor oudlid
	 * ingeburgerd. Handig om in leden snel te zien om wat voor soort
	 * lid het gaat.
	 */
	public function getChar($status=null){
		if($status===null){ $status=$this->get(); }
		switch($status){
			case 'S_OUDLID':	return '•';
			case 'S_KRINGEL':	return '~';
			case 'S_EXLID':
			case 'S_NOBODY':	return '∉';
			case 'S_NOVIET':
			case 'S_GASTLID':
			case 'S_LID': 		return '∈';
			case 'S_ERELID': 	return '☀';
			case 'S_OVERLEDEN': return '✝';
		}
	}

	/**
	 * Geef een omschrijving van de status terug.
	 */
	public function getDescription($status=null){
		if($status===null){ $status=$this->get(); }
		switch($status){
			case 'S_OUDLID': 	return 'Oudlid';
			case 'S_KRINGEL': 	return 'Kringel';
			case 'S_EXLID':		return 'Ex-lid';
			case 'S_NOBODY':	return 'Nobody';
			case 'S_NOVIET':	return 'Noviet';
			case 'S_GASTLID':	return 'Gastlid';
			case 'S_LID':		return 'Lid';
			case 'S_ERELID':	return 'Erelid';
			case 'S_OVERLEDEN': return 'Overleden';
			case 'S_CIE':		return 'Commissie & in LDAP adresboek';
		}
	}

	/**
	 * Geef standaard permissie die bij de status hoort
	 */
	 static public function getDefaultPermission($status){
		switch($status){
			case 'S_KRINGEL':
			case 'S_NOVIET':
			case 'S_GASTLID':
			case 'S_LID': 		return 'P_LID';
			case 'S_OUDLID':
			case 'S_ERELID': 	return 'P_OUDLID';
			case 'S_NOBODY':
			case 'S_EXLID':
			case 'S_OVERLEDEN': 
			case 'S_CIE':		return 'P_NOBODY';
		}
	}

	/**
	 * bestaat deze status uberhaupt?
	 */
	public static function exists($status){
		return in_array($status, self::getAll());
	}
	
	/**
	 * Geef array met alle mogelijke statussen
	 */
	static public function getAll(){
		return self::$all;
	}
	
	/**
	 * Geef array met statusbeschrijvingen met statussen als key
	 * @return array(
	 * 		[S_NOVIET] => Noviet
	 * 		..
	 * )
	 */
	static public function getAllDescriptions(){
		$return = array();
		foreach(self::getAll() as $status){
			$return[$status] = Status::getDescription($status);
		}
		return $return;
	}

}

?>
