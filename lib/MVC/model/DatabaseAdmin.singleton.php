<?php

require_once 'MVC/model/Database.singleton.php';

/**
 * DatabaseAdmin.singleton.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Separate login credentials in the future perhaps.
 */
class DatabaseAdmin extends Database {

	/**
	 * Singleton instance
	 * @var DatabaseAdmin
	 */
	private static $instance;

	/**
	 * Get singleton Database instance.
	 * 
	 * @return DatabaseAdmin
	 */
	public static function instance() {
		if (!isset(self::$instance)) {

			if (defined('ETC_PATH')) {
				$cred = parse_ini_file(ETC_PATH . '/mysql.ini');
			} else {
				$cred = array(
					'host' => 'localhost',
					'user' => 'admin',
					'pass' => 'password',
					'db' => 'csrdelft'
				);
			}
			$dsn = 'mysql:host=' . $cred['host'] . ';dbname=' . $cred['db'];
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);
			self::$instance = new DatabaseAdmin($dsn, $cred['user'], $cred['pass'], $options);
		}
		return self::$instance;
	}

	/**
	 * Create table and return SQL.
	 * 
	 * @param string $name
	 * @param array $fields
	 * @param array $primary_key
	 * @return string SQL query
	 */
	public static function sqlCreateTable($name, array $fields, array $primary_key) {
		$sql = 'CREATE TABLE ' . $name . ' (';
		foreach ($fields as $key => $value) {
			$sql .= $key . ' ' . $value . ', ';
		}
		$sql .= 'PRIMARY KEY (' . implode(', ', $primary_key) . ')) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1';
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $sql;
	}

	/**
	 * Get table fields.
	 * 
	 * @param string $name
	 * @return PersistentField[]
	 */
	public static function sqlDescribeTable($name) {
		$sql = 'DESCRIBE ' . $name;
		$query = self::instance()->prepare($sql);
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // lowercase field properties
		$query->execute();
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // reset
		return $query->fetchAll(PDO::FETCH_CLASS, 'PersistentField');
	}

	public static function sqlAddField($table, PersistentField $field, $after_field = null) {
		$sql = 'ALTER TABLE ' . $table . ' ADD ' . $field->field . ' ' . $field->type;
		$sql .= ($field->null === 'YES' ? '' : ' NOT NULL');
		$sql .= (empty($field->default) ? '' : ' DEFAULT "' . $field->default . '"');
		$sql .= (empty($field->extra) ? '' : ' ' . $field->extra);
		$sql .= ($after_field === null ? ' FIRST' : ' AFTER ' . $after_field);
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $sql;
	}

	public static function sqlDeleteField($table, $field_name) {
		$sql = 'ALTER TABLE ' . $table . ' DROP ' . $field_name;
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $sql;
	}

}
