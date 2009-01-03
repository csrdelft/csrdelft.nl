<?php
# OogOpslag Internet / C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.mysql.php
# -------------------------------------------------------------------
# MySQL wrapper
# -------------------------------------------------------------------


class MySql{

	//cached instantie van de klasse
	static private $Mysql;

	//resource handle
	private $_db;
	private $querys=array();

	private function __construct(){ $this->connect(); }

	public static function instance(){
    	//als er nog geen instantie gemaakt is, die nu maken
    	if(!isset(self::$Mysql)){
			self::$Mysql = new MySql();
		}
    	return self::$Mysql;
	}
	private function connect() {
		$cred = parse_ini_file(ETC_PATH.'/mysql.ini');
		$this->_db = mysql_connect($cred['host'], $cred['user'], $cred['pass'])
		or die ("Kan geen verbinding maken met host {$cred['host']}\n");
		mysql_select_db($cred['db'], $this->_db)
		or die ("Kan niet inloggen bij de database\n");
		//database verbinding vertellen dat hij utf-8 moet gebruiken.
		$this->query("SET NAMES 'utf8'");
	}

	### public ###

	# het openen van de databaseconnectie gebeurt tijdens het eerste gebruik
	# ervan. er wordt een persistent connectie gebruikt

 	# Retourneert het MySql resultaat bij de opgegeven Query
	public function query($query) {
		$this->debug($query);
		return mysql_query($query, $this->_db);
	}

	# Retourneert het MySql resultaat bij de opgegeven Query
	public function select($query) { return $this->query($query); }

	# Een SELECT query kan ook gemaakt worden mbv een array
	# String $array['select'][] 	kolomnaam
	# String $array['from']       tabelnaam
	# String $array['where'][]		condities AND of:
	# String $array['orderby']		kolomnaam [DESC|ASC]
	# String $array['limit']			alleen de nummers
	public function select_a($array) {
    # SELECT
		$query = "SELECT";
		foreach ($array['select'] as $q) $query .= " {$q},";
		$query = substr($query, 0, -1); # laatste komma verwijderen

		# FROM
		$query .= " FROM {$array['from']}";

		# WHERE
		if (array_key_exists('where', $array)) {
			$query .= " WHERE";
			foreach ($array['where'] as $q) $query .= " {$q} AND";
			$query = substr($query, 0, -4); # laatste AND verwijderen
		}

		# ORDERBY
		if (array_key_exists('orderby', $array)) $query .= " ORDER BY {$array['orderby']}";

		# LIMIT
		if (array_key_exists('limit', $array)) $query .= " LIMIT {$array['limit']}";

		#echo $query;
	  return $this->query($query);
	}

	public function next($result)		{ return mysql_fetch_assoc($result); }
	public function numRows($result)	{ return mysql_num_rows($result); }
	public function insert_id()			{ return mysql_insert_id($this->_db); }
	public function affected_rows() 	{ return mysql_affected_rows($this->_db); }

	# Toevoegen van een regel
	# String $table : de tabelnaam
	# String $sqldata[$column] = $value
	public function insert_a($table, &$sqldata) {
		$q1 = "";
		$q2 = "";
		foreach ($sqldata as $column => $value) {
			$q1 .= "`$column`, ";
			$q2 .= "'$value', ";
		}
		$q1 = substr($q1, 0, -2); # laatste komma verwijderen
		$q2 = substr($q2, 0, -2);

		$this->query("INSERT INTO `".$table."` (".$q1.") VALUES (".$q2.")");

		return mysql_insert_id();
	}


	public function update($query){ return $this->select($query); }

	# Bijwerken van een regel
	# String $table : de tabelnaam
	# int $id : regel-id
	# String $sqldata[$column] = $value
	# NB: API verandering!!! idkolom!!!
	public function update_a($table, $idkolom, $id, &$sqldata) {
		$q1 = "";
		foreach ($sqldata as $column => $value) {
			$q1 .= $column."='".$value."', ";
		}
		$q1 = substr($q1, 0, -2); # laatste komma verwijderen

		if (!is_int($id)) {
			$id = "'".$id."'";
		}
		$this->query("UPDATE `".$table."` SET ".$q1." WHERE `".$idkolom."`=".$id.";");
	}

	// zet een resultaat ding om in een array
	public function result2array($rResult){
		if($this->numRows($rResult)>=1){
			while($aDataArray=$this->next($rResult)){
				$aArray[]=$aDataArray;
			}
		}else{
			$aArray=false;
		}
		return $aArray;
	}
	//geef array terug met resultaten uit de aangeboden query
	public function query2array($query){
		$result=$this->query($query);
		if(!result){
			return false;
		}
		return $this->result2array($query);
	}
	//selecteer één regel uit de db en geef die als array terug.
	public function getRow($query){
		$result=$this->query($query);
		if(!$result){
			return false;
		}
		return $this->next($result);
	}
	# Bijwerken van een regel
	# String $table : de tabelnaam
	# int $id : regel-id
	public function delete($table, $id) {
		$id=(int)$id;
		$this->query("DELETE FROM `".$table."` WHERE `id`=".$id.";");
	}

	public function escape($str) {
		return mysql_real_escape_string($str, $this->_db);
	}
	public function dbResource() {
		return $this->_db;
	}
	function debug($string){
		if(defined('DEBUG')){
			$string=trim(str_replace(array("\r\n", "\n", "\t", '  ', '   '), ' ', $string));
			if(mysql_error()!=''){
				$string.="\nmysql_error(): ".mysql_error();
			}
			$this->querys[]=$string;
		}
	}
	public function getDebug(){
		return 'MySql query '.print_r($this->querys, true);
	}
}

?>
