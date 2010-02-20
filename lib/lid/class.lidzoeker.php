<?php
/*
 * LidZoeker
 * 
 * de array's die in deze class staan bepalen wat er in het formulier te zien is.
 */

class LidZoeker{
	
	private $allowVelden=array(
		'pasfoto', 'uid', 'naam', 'voornaam', 'tussenvoegsel', 'achternaam', 'nickname', 
		'email', 'adres', 'telefoon', 'mobiel', 'skype', 'studie', 'status',
		'gebdatum', 'beroep', 'verticale', 'lidjaar', 'kring', 'patroon');
	
	//deze velden kunnen we niet selecteren voor de ledenlijst, ze zijn wel te 
	//filteren en te sorteren.
	private $veldenNotSelectable=array('voornaam', 'achternaam', 'tussenvoegsel');
	
	//nette aliassen voor kolommen, als ze niet beschikbaar zijn wordt gewoon 
	//de naam uit $this->allowVelden gebruikt
	private $veldNamen=array(
		'telefoon' => 'Nummer',
		'mobiel' => 'Pauper',
		'studie' => 'Studie',
		'gebdatum' => 'Geb.datum',
		'studienr' => 'StudieNr.',
		'ontvangtcontactueel' => 'Contactueel?');
	
	//toegestane opties voor het statusfilter.
	private $allowStatus=array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_NOBODY', 'S_OUDLID', 'S_KRINGEL', 'S_OVERLEDEN');
	
	//toegestane opties voor de weergave.
	private $allowWeergave=array('lijst', 'kaartje', 'CSV');
	private $sortable=array(
		'achternaam' => 'Achternaam', 'email' => 'Email', 'gebdatum' => 'Geboortedatum',
		'lidjaar' => 'lichting', 'studie' => 'Studie');
	
	private $rawQuery=array('status'=>'LEDEN', 'sort'=>'achternaam');
	
	private $query='';
	private $zoekveld=array('default');
	private $filters=array();
	private $sort=array('achternaam');
	private $velden=array('naam', 'email', 'telefoon', 'mobiel');
	private $weergave='lijst';
	
	private $result=null;
	
	public function __construct(){
		if(Loginlid::instance()->hasPermission('P_LEDEN_MOD')){
			$this->allowVelden=array_merge(
				$this->allowVelden, 
				array('studienr', 'bankrekening', 'muziek', 'ontvangtcontactueel', 'kerk', 'lidafdatum'));
		}
		
		//parse default values.
		$this->parseQuery($this->rawQuery);
	}
	public function parseQuery($query){
		if(!is_array($query)){
			$parts=explode('&', $query);
		}
		$this->rawQuery=$query;
		
		foreach($query as $key => $value){
			switch($key){
				case 'q':
					$this->query=$value;
				break;
				case 'weergave':
					if(in_array($value, $this->allowWeergave)){
						$this->weergave=$value;
					}
				break;
				case 'velden':
					$this->velden=array();
					foreach($value as $veld){
						if(array_key_exists($veld, $this->getSelectableVelden())){
							$this->velden[]=$veld;
						}
					}
					if(count($this->velden)==0){
						$this->velden=array('naam', 'adres', 'email', 'mobiel');
					}
				break;
				case 'status':
					$value=strtoupper($value);
					
					if($value=='*' OR $value=='ALL'){
						break;
					}
					$filters=explode('|', $value);
					
					$add=array();
					foreach($filters as $filter){
						if($filter=='LEDEN'){
							$add=array_merge($add, array('S_LID', 'S_NOVIET', 'S_GASTLID'));
						}
						$filter='S_'.$filter;
						if(in_array($filter, $this->allowStatus)){
							$add[]=$filter;
						}
					}
					$this->addFilter('status', $add);
				break;
			}
		}
	}
	private function defaultSearch($zoekterm){
		$query='';
		$defaults=array();
		
		$zoekterm=MySql::instance()->escape($zoekterm);
		
		if(preg_match('/^\d{2}$/', $zoekterm)){ //lichting bij een sting van 2 cijfers
			$query="RIGHT(lidjaar,2)=".(int)$zoekterm." ";
		}elseif(Lid::isValidUid($zoekterm)){ //uid's is ook niet zo moeilijk.
			$query="uid='".$zoekterm."' ";
		}elseif(preg_match('/^[\-0-9]+$/', $zoekterm)){
			$defaults[]="telefoon LIKE '%".$zoekterm."%' ";
			$defaults[]="mobiel LIKE '%".$zoekterm."%' ";
			
			$query.='( '.implode(' OR ', $defaults).' )';
		}else{
			$defaults[]="voornaam LIKE '%".$zoekterm."%' ";
			$defaults[]="achternaam LIKE '%".$zoekterm."%' ";
			$defaults[]="CONCAT_WS(' ', voornaam, tussenvoegsel, achternaam) LIKE '%".$zoekterm."%' ";
			$defaults[]="CONCAT_WS(' ', tussenvoegsel, achternaam) LIKE '%".$zoekterm."%' ";
			$defaults[]="CONCAT_WS(', ', achternaam, tussenvoegsel) LIKE '%".$zoekterm."%' ";
			$defaults[]="nickname LIKE '%".$zoekterm."%' ";
			
			$defaults[]="CONCAT_WS(' ', adres, postcode, woonplaats) LIKE '%".$zoekterm."%' ";
			$defaults[]="adres LIKE '%".$zoekterm."%' ";
			$defaults[]="postcode LIKE '%".$zoekterm."%' ";
			$defaults[]="woonplaats LIKE '%".$zoekterm."%' ";
			
			$defaults[]="studie LIKE '%".$zoekterm."%' ";
			$defaults[]="email LIKE '%".$zoekterm."%' ";
			
			$query.='( '.implode(' OR ', $defaults).' )';
		}
		
		return $query.' AND ';
	}
	/*
	 * Doe de zoektocht.
	 */
	public function search(){
		$db=MySql::instance();

		$query="SELECT uid FROM lid WHERE ";
		
		if($this->query!=''){
			$query.=$this->defaultSearch($this->query);
		}		
		$query.=$this->getFilterSQL();
		$query.=' ORDER BY '.implode($this->sort).';';
		
		$this->sqlquery=$query;
		$result=$db->query2array($query);
		$this->result=array();
		if(is_array($result)){
			foreach($result as $uid){
				$lid=LidCache::getLid($uid['uid']);
				if($lid instanceof Lid){
					$this->result[]=$lid;
				}
			}
		}
	}
	public function count(){
		if($this->result===null){
			$this->search();
		}
		return count($this->result);
	}
	public function searched(){
		return $this->result!==null;
	}
	public function getLeden(){
		if($this->result===null){
			$this->search();
		}
		return $this->result;
	}
	public function getQuery(){		return $this->query; }
	public function getVelden(){ 	return $this->velden; } 
	public function getWeergave(){ 	return 'LL'.ucfirst($this->weergave); }
	
	public function getRawQuery($key){
		if(!isset($this->rawQuery[$key])){
			return false;
		}
		return $this->rawQuery[$key];
	}
	public function getFilterSQL(){
		$filters=array();
		foreach($this->filters as $key => $value){
			if(is_array($value)){
				$filters[]=$key." IN ('".implode("', '", $value)."')";
			}else{
				$filters[]=$key."='".$value."'";
			}
		}
		$return=implode(' AND ', $filters);
		if(strlen(trim($return))==0){
			return '1';
		}else{
			return $return;
		}
	}
	public function getSelectedVelden(){
		return $this->velden;
	}
	public function getSelectableVelden(){
		$return=array();
		foreach($this->allowVelden as $veld){
			if(in_array($veld, $this->veldenNotSelectable)){
				continue;
			}
			if(isset($this->veldNamen[$veld])){
				$return[$veld]=$this->veldNamen[$veld];
			}else{
				$return[$veld]=$veld;
			}
		}
		return $return;
	}
	public function getSortableVelden(){
		return $this->sortable;
	}
	public function addFilter($field, $value){
		if(is_array($value)){
			$this->filters[$field]=$value;
		}else{
			$this->filters[$field]=array($value);
		}
	}
	public function __toString(){
		$return='Zoeker:';
		$return.=print_r($this->rawQuery, true);
		$return.=print_r($this->filters, true);
		return $return;
	}
}
?>
