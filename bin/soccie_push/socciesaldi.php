#!/usr/bin/php
<?php
/*
 * socciesaldi.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * dit script pushed saldi in een xml-bestand richting de webserver van
 * csrdelft.nl.
 *
 * 'installeer' met:
 * svn co http://svn.knorrie.org/csrdelft.nl/trunk/bin/soccie_push .
 */

//verbose inschakelen
$verbose=(isset($argv[1]) AND $argv[1]='-v');

$config=parse_ini_file('mysql.ini');

$db=mysql_connect($config['host'], $config['user'], $config['pass']);
mysql_select_db($config['db']);

$sStreeplijstQuery="
        SELECT
                Voornaam, Achternaam, Aliasnaam, Saldo, ID, createTerm
        FROM
                Leden
        WHERE
                achternaam <> ''
        AND
                voornaam <> ''
        ORDER BY
                Achternaam, Voornaam;";
$rStreeplijstResult=mysql_query($sStreeplijstQuery, $db);

$xml="<saldi>\r\n";

while($aLid=mysql_fetch_assoc($rStreeplijstResult)){
        $xml.=' <lid>
  <id>'.$aLid['ID'].'</id>
  <createTerm>'.$aLid['createTerm'].'</createTerm>
  <voornaam>'.$aLid['Voornaam'].'</voornaam>
  <achternaam>'.$aLid['Achternaam'].'</achternaam>
  <saldo>'.$aLid['Saldo'].'</saldo>
 </lid>'."\r\n";
}
$xml.="</saldi>\r\n";
echo 'SocCie-Saldi versturen naar csrdelft.nl op '.date('Y-m-d H:s:i')."\r\n";

$verzendXml=base64_encode($xml);

if($verbose) echo '--  Verzenden...'."\r\n";
//verzenden dan maar:
$url='http://csrdelft.nl/tools/soccie.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array('saldi'=>$verzendXml));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$content = curl_exec($ch);
if($verbose) echo '--  Connectie sluiten...'."\r\n";
curl_close ($ch);
echo "Statusmelding van csrdelft.nl:\n ".$content."\r\n";
echo date('H:s:i').' ...klaar'."\r\n";

?>
