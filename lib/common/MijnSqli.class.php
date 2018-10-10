<?php

namespace CsrDelft\common;

/**
 * MijnSqli.class.php
 *
 * @deprecated
 *
 * OogOpslag Internet / C.S.R. Delft | pubcie@csrdelft.nl
 *
 * MySQLi wrapper
 */
class MijnSqli {

	//cached instantie van de klasse
	static private $mySqli;
	//resource handle
	private $_db;
	private $queries = array();

	private function __construct() {
		$this->connect();
	}

	public static function instance() {
		//als er nog geen instantie gemaakt is, die nu maken
		if (!isset(self::$mySqli)) {
			self::$mySqli = new MijnSqli();
		}
		return self::$mySqli;
	}

	private function connect() {
		if (defined('ETC_PATH')) {
			$cred = parse_ini_file(ETC_PATH . 'mysql.ini');
		} else {
			$cred = array(
				'host' => 'localhost',
				'user' => 'foo',
				'pass' => 'bar',
				'db' => 'csrdelft'
			);
		}
		$this->_db = mysqli_connect($cred['host'], $cred['user'], $cred['pass'], $cred['db'])
		or die("Kan geen verbinding maken met de database server {$cred['host']}\n");
		//database verbinding vertellen dat hij utf-8 moet gebruiken.
		$this->query("SET NAMES 'utf8'");
	}

	public function error() {
		return mysqli_error($this->_db);
	}

	# het openen van de databaseconnectie gebeurt tijdens het eerste gebruik
	# ervan. er wordt een persistent connectie gebruikt
	# Retourneert het MySql resultaat bij de opgegeven Query

	public function query($query) {
		$return = mysqli_query($this->_db, $query);
		$this->debug($query);
		return $return;
	}

	# Retourneert het MySql resultaat bij de opgegeven Query

	public function select($query) {
		return $this->query($query);
	}

	# Een SELECT query kan ook gemaakt worden mbv een array
	# String $array['select'][] 	kolomnaam
	# String $array['from']       tabelnaam
	# String $array['where'][]		condities AND of:
	# String $array['orderby']		kolomnaam [DESC|ASC]
	# String $array['limit']			alleen de nummers

	public function select_a($array) {
		# SELECT
		$query = "SELECT";
		foreach ($array['select'] as $q)
			$query .= " {$q},";
		$query = substr($query, 0, -1); # laatste komma verwijderen
		# FROM
		$query .= " FROM {$array['from']}";

		# WHERE
		if (array_key_exists('where', $array)) {
			$query .= " WHERE";
			foreach ($array['where'] as $q)
				$query .= " {$q} AND";
			$query = substr($query, 0, -4); # laatste AND verwijderen
		}

		# ORDERBY
		if (array_key_exists('orderby', $array))
			$query .= " ORDER BY {$array['orderby']}";

		# LIMIT
		if (array_key_exists('limit', $array))
			$query .= " LIMIT {$array['limit']}";

		#echo $query;
		return $this->query($query);
	}

	public function next($result) {
		if (!$result) {
			die('Unable to run query: ' . $this->error());
		}
		return mysqli_fetch_assoc($result);
	}

	public function next_array($result) {
		if (!$result) {
			die('Unable to run query: ' . $this->error());
		}
		return mysqli_fetch_array($result);
	}

	public function numRows($result) {
		if (!$result) {
			die('Unable to run query: ' . $this->error());
		}
		return mysqli_num_rows($result);
	}

	public function insert_id() {
		return mysqli_insert_id($this->_db);
	}

	public function affected_rows() {
		return mysqli_affected_rows($this->_db);
	}

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

		$this->query("INSERT INTO `" . $table . "` (" . $q1 . ") VALUES (" . $q2 . ")");

		return mysqli_insert_id($this->_db);
	}

	public function update($query) {
		return $this->select($query);
	}

	# Bijwerken van een regel
	# String $table : de tabelnaam
	# int $id : regel-id
	# String $sqldata[$column] = $value
	# NB: API verandering!!! idkolom!!!

	public function update_a($table, $idkolom, $id, &$sqldata) {
		$q1 = "";
		foreach ($sqldata as $column => $value) {
			$q1 .= $column . "='" . $value . "', ";
		}
		$q1 = substr($q1, 0, -2); # laatste komma verwijderen

		if (!is_int($id)) {
			$id = "'" . $id . "'";
		}
		$this->query("UPDATE `" . $table . "` SET " . $q1 . " WHERE `" . $idkolom . "`=" . $id . ";");
	}

	// zet een resultaat ding om in een array
	public function result2array($rResult) {

		if ($this->numRows($rResult) >= 1) {
			$aArray = [];
			while ($aDataArray = $this->next($rResult)) {
				$aArray[] = $aDataArray;
			}
			return $aArray;
		} else {
			return false;
		}
	}

	//geef array terug met resultaten uit de aangeboden query
	public function query2array($query) {
		$result = $this->query($query);
		if (!$result) {
			return false;
		}
		return $this->result2array($result);
	}

	//selecteer één regel uit de db en geef die als array terug.
	public function getRow($query) {
		$result = $this->query($query);
		if (!$result) {
			return false;
		}
		return $this->next($result);
	}

	# Bijwerken van een regel
	# String $table : de tabelnaam
	# int $id : regel-id

	public function delete($table, $id) {
		$id = (int)$id;
		$this->query("DELETE FROM `" . $table . "` WHERE `id`=" . $id . ";");
	}

	public function escape($in) {
		if (is_array($in)) {
			$return = array();
			foreach ($in as $key => $item) {
				$return[$key] = $this->escape($item);
			}
			return $return;
		} else {
			return mysqli_real_escape_string($this->_db, $in);
		}
	}

	public function dbResource() {
		return $this->_db;
	}

	private function debug($string) {
		if (DEBUG) {
			$string = trim(str_replace(array("\r\n", "\n", "\t", '  ', '   '), ' ', $string));
			$error = $this->error();
			if (!empty($error)) {
				$string .= "\nmysqli_error: " . $error;
			}
			$this->queries[] = $string;
		}
	}

	public function getQueries() {
		return $this->queries;
	}

}
