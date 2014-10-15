<?php

require_once 'MVC/model/framework/Database.singleton.php';

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
	 * Get singleton DatabaseAdmin instance.
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
	 * @return PDOStatement
	 */
	public static function sqlShowTables() {
		$sql = 'SHOW TABLES';
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $query;
	}

	/**
	 * Get table attributes.
	 * 
	 * @param string $name
	 * @return PDOStatement
	 */
	public static function sqlDescribeTable($name) {
		$sql = 'DESCRIBE ' . $name;
		$query = self::instance()->prepare($sql);
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // lowercase attribute properties
		$query->execute();
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // reset
		$query->setFetchMode(PDO::FETCH_CLASS, 'PersistentAttribute');
		return $query;
	}

	/**
	 * Get query to (re-)create existing table.
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

	public static function sqlCreateTable($name, array $attributes, array $primary_key) {
		$sql = 'CREATE TABLE ' . $name . ' (';
		foreach ($attributes as $name => $attribute) {
			$sql .= $attribute->toSQL() . ', ';
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

	public static function sqlDropTable($name) {
		$sql = 'DROP TABLE ' . $name;
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY AND DB_DROP === true) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public static function sqlAddAttribute($table, PersistentAttribute $attribute, $after_attribute = null) {
		$sql = 'ALTER TABLE ' . $table . ' ADD ' . $attribute->toSQL();
		$sql .= ($after_attribute === null ? ' FIRST' : ' AFTER ' . $after_attribute);
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public static function sqlChangeAttribute($table, PersistentAttribute $attribute, $old_name = null) {
		$sql = 'ALTER TABLE ' . $table . ' CHANGE ' . ($old_name === null ? $attribute->field : $old_name) . ' ' . $attribute->toSQL();
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public static function sqlDeleteAttribute($table, PersistentAttribute $attribute) {
		$sql = 'ALTER TABLE ' . $table . ' DROP ' . $attribute->field;
		$query = self::instance()->prepare($sql);
		if (DB_MODIFY AND DB_DROP === true) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

}
