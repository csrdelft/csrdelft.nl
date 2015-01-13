<?php

# draaien met:
#!/usr/bin/php5 -c /etc/php5/vhosts/csrdelft/
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/configuratie.include.php');
require_once('configuratie.include.php');

# open database
require_once('class.mysql.php');
$db = new MijnSqli();

# haal alles van lid op
$leden = array();
$result = $db->select("
	SELECT
		uid,
		voornaam,
		tussenvoegsel,
		achternaam,
		adres,
		postcode,
		woonplaats,
		telefoon,
		mobiel,
		email,
		password,
		website,
		nickname,
		land
	FROM
		lid
	WHERE
		(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET')
	ORDER BY
		uid
");
if ($result !== false and $db->numRows($result) > 0) {
	while ($lid = $db->next($result)) {

		# right.. nu nog het woonoord opzoeken
		$wores = $db->select("
			SELECT 
				woonoord.naam AS naam
			FROM 
				woonoord, bewoner
			WHERE
				woonoord.id=bewoner.woonoordid
			AND
				bewoner.uid='" . $lid['uid'] . "'
			LIMIT 1;
		");
		if ($wores !== false and $db->numRows($wores) == 1) {
			$record = $db->next($wores);
			$lid['woonoord'] = $record['naam'];
		} else
			$lid['woonoord'] = '';

		$ldif = sprintf(<<<EOT
dn: uid=%s,ou=leden,dc=csrdelft,dc=nl
objectClass: top
objectClass: person
objectClass: organizationalPerson
objectClass: inetOrgPerson
objectClass: mozillaAbPersonObsolete
uid: %s
givenName: %s
sn: %s
cn: %s
mail: %s
homePhone: %s
mobile: %s
ou: %s
homePostalAddress: %s
o: C.S.R. Delft
mozillaNickname: %s
mozillaUseHtmlMail: FALSE
mozillaHomeStreet: %s
mozillaHomeLocalityName: %s
mozillaHomePostalCode: %s
mozillaHomeCountryName: %s
mozillaHomeUrl: %s
description: Ledenlijst C.S.R. Delft
userPassword: %s


EOT
				, $lid['uid'], $lid['uid'], str_replace('  ', ' ', implode(' ', array($lid['voornaam'], $lid['tussenvoegsel']))), $lid['achternaam'], str_replace('  ', ' ', implode(' ', array($lid['voornaam'], $lid['tussenvoegsel'], $lid['achternaam']))), $lid['email'], $lid['telefoon'], $lid['mobiel'], ($lid['woonoord'] !== false) ? $lid['woonoord'] : '', implode('$', array($lid['adres'], $lid['postcode'], $lid['woonplaats'])), $lid['nickname'], $lid['adres'], $lid['woonplaats'], $lid['postcode'], $lid['land'], $lid['website'], $lid['password']
		);

		$ldif = (preg_replace('/\w+: \n/', '', $ldif));
		# geen idee waarom zonder volgende regel borkt met ou: lege regels
		#$ldif=(preg_replace('/ou: \n/','',$ldif));
		print($ldif);
	}
}