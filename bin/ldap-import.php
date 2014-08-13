#!/usr/bin/php
<?php
# Zorgt ervoor dat alle leden worden toegevoegd aan de LDAP

require_once('configuratie.include.php');

# databaseconnectie openen
$db = MijnSqli::instance();

# Alle leden ophalen en opslaan in de LDAP
$result = $db->select("SELECT uid FROM `lid`");
if ($result !== false and $db->numRows($result) > 0) {
	while ($uid = $db->next($result)) {
		$uid = $uid['uid'];
		echo $uid . ' toevoegen. ';
		$lid = new Lid($uid);
		$lid->save_ldap();
	}
}
