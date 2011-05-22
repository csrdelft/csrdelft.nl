#!/usr/bin/php
<?php
# Zorgt ervoor dat alle leden worden toegevoegd aan de LDAP

require_once('configuratie.include.php');

# databaseconnectie openen
$db=MySql::instance();

# Profiel-object maken
require_once('class.lid.php');

# Alle leden ophalen en opslaan in de LDAP
$result = $db->select("SELECT uid FROM `lid` WHERE status = 'S_LID' OR status = 'S_GASTLID' OR status = 'S_NOVIET' OR status = 'S_KRINGEL' OR status = 'S_CIE'");
if ($result !== false and $db->numRows($result) > 0) {
	while ($uid = $db->next($result)){
		$uid = $uid['uid'];
		echo $uid.' toevoegen';
		$lid=new Lid($uid);
		$lid->save_ldap();
	}
}

?>
