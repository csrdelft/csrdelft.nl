<?php

class Status{
	private $status;

	public function __construct($status){
		if(!in_array($status, Status::getStatussen())){
			throw new Exception('Status bestaat niet');
		}
		$this->status=$status;
	}

	public function __toString(){
		return $this->get();
	}

	public function get(){	return $this->status;	}

	/**
	 * Geef array met alle mogelijke statussen
	 */
	static public function getStatussen(){
		return array(
			'S_NOVIET', 'S_LID', 'S_GASTLID',
			'S_OUDLID','S_ERELID',
			'S_KRINGEL',
			'S_OVERLEDEN',
			'S_NOBODY',
			'S_CIE'
		);
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
		foreach(Status::getStatussen() as $status){
			$return[$status] = Status::getDescription($status);
		}
		return $return;
	}

	/**
	 * Geef een karakter terug om de status van het huidige lid aan te
	 * duiden. In de loop der tijd zijn ~ voor kringel en • voor oudlid
	 * ingeburgerd. Handig om in leden snel te zien om wat voor soort
	 * lid het gaat.
	 */
	public function getChar($sStatus=null){
		if($sStatus===null){ $sStatus=$this->get(); }
		switch($sStatus){
			case 'S_OUDLID':	return '•';
			case 'S_KRINGEL':	return '~';
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
	public function getDescription($sStatus=null){
		if($sStatus===null){ $sStatus=$this->get(); }
		switch($sStatus){
			case 'S_OUDLID': 	return 'Oudlid';
			case 'S_KRINGEL': 	return 'Kringel';
			case 'S_NOBODY':	return 'Nobody';
			case 'S_NOVIET':	return 'Noviet';
			case 'S_GASTLID':	return 'Gastlid';
			case 'S_LID':		return 'Lid';
			case 'S_ERELID':	return 'Erelid';
			case 'S_OVERLEDEN': return 'Overleden';
			case 'S_CIE':		return 'Commissie & in LDAP adresboek';
		}
	}

}

?>
