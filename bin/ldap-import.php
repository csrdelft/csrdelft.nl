#!/usr/bin/php
<?php
# Zorgt ervoor dat alle leden worden toegevoegd aan de LDAP

require_once('include.config.php');

# databaseconnectie openen
$db=MySql::get_MySql();

# Profiel-object maken
require_once('class.profiel.php');
$lid=new Profiel();

# Alle leden ophalen en opslaan in de LDAP
$result = $db->select("SELECT uid FROM `lid` WHERE status = 'S_LID' OR status = 'S_GASTLID' OR status = 'S_NOVIET' OR status = 'S_KRINGEL'");
if ($result !== false and $db->numRows($result) > 0) {
	while ($uid = $db->next($result)){
		$uid = $uid['uid'];
		$lid->loadSqlTmpProfile($uid);
		$lid->save_ldap();
	}
}

?>