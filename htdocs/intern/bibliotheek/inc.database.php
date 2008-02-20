<?
	/*
	 * Dit bestand bevat een verzameling functies voor het communiceren met een MySQL-database. Te weten de functies:
	 * db_connect();
	 * db_query($sql, $debug = false);
	 * db_firstCell($sql, $debug = false);
	 * db_escape($str);
	 */
	 
	//even een wrapper om de MySql-klasse van csrdelft.nl...
	require_once('include.config.php');
	$db=MySql::get_MySql();
	$dbYetConnected=true;
	
	//dummy-functie
	function db_connect(){}
	
	function db_query($sql, $debug = false) {
		// in debug-mode: print de query uit en stop ermee.
		if ($debug) {
			echo "SQL query: $sql";
			exit;
		}
		$db=MySql::get_MySql();
		// voer de query uit.
		$result = $db->query($sql);
		
		// als $result leeg is, is het mislukt. Print de error uit.
		if (!$result) {
			echo 'Query processing failed. Query:<br />';
			echo $sql.'<br />';
			echo 'MySQL error:<br />';
			echo mysql_error();
			echo "<hr size=1>";
		}
		
		// retourneer het resultaat.
		return $result;
	}
	function db_firstCell($sql, $debug = false) {
		// voer de query uit.
		$result = db_query($sql, $debug);
		
		$db=MySql::get_MySql();
		// retourneer de inhoud van de eerste cel.
		$row = $db->next($result);
		
		// vieze hack om te zorgen dat de 'eerste' key 0 wordt.
		sort($row);
		
		if(isset($row[0])){
			return $row[0];
		}else{
			return null;
		}
	}
	
	/*
	 * Deze functie escapet input voor een query
	 */
	function db_escape($str) {
		$db=MySql::get_MySql();
		return $db->escape($str);
	}
	
?>