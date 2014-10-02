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
			$cred = parse_ini_file(ETC_PATH . 'mysql.ini');
			if ($cred === false) {
				$cred = array(
					'host'	 => 'localhost',
					'user'	 => 'admin',
					'pass'	 => 'password',
					'db'	 => 'csrdelft'
				);
			}
			$dsn = 'mysql:host=' . $cred['host'] . ';dbname=' . $cred['db'];
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
				PDO::ATTR_ERRMODE			 => PDO::ERRMODE_EXCEPTION
			);
			self::$instance = new DatabaseAdmin($dsn, $cred['user'], $cred['pass'], $options);
		}
		return self::$instance;
	}

	/**
	 * Array of SQL statements for file.sql
	 * @var array
	 */
	private static $queries = array();

	/**
	 * Get array of SQL statements for file.sql
	 * @return array
	 */
	public static function getQueries() {
		return self::$queries;
	}

	/**
	 * Get table fields.
	 * 
	 * @param string $name
	 * @return PDOStatement
	 */
	public static function sqlDescribeTable($name) {
		$sql = 'DESCRIBE ' . $name;
		$query = self::instance()->prepare($sql);
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // lowercase field properties
		$query->execute();
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // reset
		$query->setFetchMode(PDO::FETCH_CLASS, 'PersistentField');
		return $query;
	}

	/**
	 * Backup table structure and data.
	 * 
	 * @param string $name
	 */
	public static function sqlBackupTable($name) {
		$filename = 'backup-' . $name . '_' . date('d-m-Y_H-i-s') . '.sql.gz';
		header('Content-Type: application/x-gzip');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$cred = parse_ini_file(ETC_PATH . 'mysql.ini');
		$cmd = 'mysqldump --user=' . $cred['user'] . ' --password=' . $cred['pass'] . ' --host=' . $cred['host'] . ' ' . $cred['db'] . ' ' . $name . ' | gzip --best';
		passthru($cmd);
	}

	/**
	 * Get all tables.
	 * 
	 * @return string SQL query
	 */
	public static function sqlShowTables() {
		$sql = 'SHOW TABLES';
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $query;
	}

	/**
	 * Get create table query.
	 * 
	 * @param string $name
	 * @return string SQL query
	 */
	public static function sqlShowCreateTable($name) {
		$sql = 'SHOW CREATE TABLE ' . $name;
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $query->fetchColumn(1);
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
		foreach ($fields as $name => $field) {
			$sql .= $field->toSQL() . ', ';
		}
		if (empty($primary_key)) {
			$sql = substr($sql, 0, -2); // remove last ,
		} else {
			$sql .= 'PRIMARY KEY (' . implode(', ', $primary_key) . ')';
		}
		$sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1';
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public static function sqlAddField($table, PersistentField $field, $after_field = null) {
		$sql = 'ALTER TABLE ' . $table . ' ADD ' . $field->toSQL();
		$sql .= ($after_field === null ? ' FIRST' : ' AFTER ' . $after_field);
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public static function sqlChangeField($table, PersistentField $field, $old_name = null) {
		$sql = 'ALTER TABLE ' . $table . ' CHANGE ' . ($old_name === null ? $field->field : $old_name) . ' ' . $field->toSQL();
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public static function sqlDeleteField($table, PersistentField $field) {
		$sql = 'ALTER TABLE ' . $table . ' DROP ' . $field->field;
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY AND DB_DROP === true) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

}
