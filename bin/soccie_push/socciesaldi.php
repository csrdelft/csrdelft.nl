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
 * chmod +x socciesaldi.php
 * cp mysql.ini.sample mysql.ini
 * # pas het wachtwoord aan in mysql.ini
 *
 * test door ./socciesaldi.php te typen. Als het goed is komt er nu iets te staan als:
 *
 * jieter@anthrax.0 ~$ ./socciesaldi.php
 * SocCie-Saldi versturen naar csrdelft.nl op 2009-03-13 16:05:37
 * Statusmelding van csrdelft.nl:
 * [ 274 regels ontvangen.... OK ]
 * 16:08:37 ...klaar
 *
 * Nu is het nog handig om een cronjob te maken:
 * jieter@anthrax.0 ~$ crontab -e
 * en voeg deze regel toe om het scriptje elke dag om 6:30 te laten lopen
 * 30      6       *       *       * /home/jieter/socciesaldi.php >> soccie.log  2>&1
 */

//verbose inschakelen
$verbose = (isset($argv[1]) AND $argv[1] = '-v');

$config = parse_ini_file('mysql.ini');

$db = mysql_connect($config['host'], $config['user'], $config['pass']);
mysql_select_db($config['db']);

$sStreeplijstQuery = "
        SELECT
                Voornaam, Achternaam, Aliasnaam, Saldo, ID, createTerm
        FROM
                Leden
        WHERE
                achternaam != ''
        AND
                voornaam != ''
        ORDER BY
                Achternaam, Voornaam;";
$rStreeplijstResult = mysql_query($sStreeplijstQuery, $db);

$xml = "<saldi>\r\n";

while ($aLid = mysql_fetch_assoc($rStreeplijstResult)) {
	$xml.=' <lid>
  <id>' . $aLid['ID'] . '</id>
  <createTerm>' . $aLid['createTerm'] . '</createTerm>
  <voornaam>' . $aLid['Voornaam'] . '</voornaam>
  <achternaam>' . $aLid['Achternaam'] . '</achternaam>
  <saldo>' . $aLid['Saldo'] . '</saldo>
 </lid>' . "\r\n";
}
$xml.="</saldi>\r\n";
echo 'SocCie-Saldi versturen naar csrdelft.nl op ' . date('Y-m-d H:s:i') . "\r\n";

$verzendXml = base64_encode($xml);

if ($verbose) {
	echo '--  Verzenden...' . "\r\n";
}
//verzenden dan maar:
$url = CSR_ROOT . '/tools/soccie.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array('saldi' => $verzendXml));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$content = curl_exec($ch);
if ($verbose) {
	echo '--  Connectie sluiten...' . "\r\n";
}
curl_close($ch);
echo "Statusmelding van csrdelft.nl:\n " . $content . "\r\n";
echo date('H:s:i') . ' ...klaar' . "\r\n";
