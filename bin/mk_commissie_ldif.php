#!/usr/bin/php5
<?php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/configuratie.include.php');

while ($xaccount = fgetcsv(STDIN, 1024, ";")) {
	if (substr(trim($xaccount[0]),0,1) == '#' or trim($xaccount[0]) == '') continue;
	if ($xaccount[3] == '') $xaccount[3] = $xaccount[2];
	if (substr($xaccount[4], -1, 1) == '@') $xaccount[4] .= 'csrdelft.nl';
		
		printf(<<<EOT
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
homePhone: 015-2135681
ou: Sociëteit Confide
homePostalAddress: Oude Delft 9$2611 BA\$Delft
o: C.S.R. Delft
xmozillausehtmlmail: FALSE
mozillaHomeStreet: Oude Delft 9
mozillaHomeLocalityName: Delft
mozillaHomePostalCode: 2611 BA
mozillaHomeCountryName: Nederland
homeurl: http://csrdelft.nl
description: Adreslijst C.S.R. Delft


EOT
			,$xaccount[0]
			,$xaccount[0]
			,$xaccount[1]
			,$xaccount[2]
			,$xaccount[3]
			,$xaccount[4]
		);

}

?>
