<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------
# Historie:
# 02-01-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.mysql.php');

class Lid {

	### private ###
	# het profiel van een gebruiker, i.e. zijn regel uit de database die we inladen
	# komt in de sessie...

	# permissies die we gebruiken om te vergelijken met de permissies van
	# een gebruiker. zie functie _loadPermissions()
	var $_permissions = array();
	var $_perm_user   = array();

	var $_db;
	
	# Het profiel van de gebruiker... niet meer in de sessie maar bij elke pagina
	# opgehaald, om wijzigingen meteen actief te krijgen.
	var $_profile;
	
	#
	# data voor de profiel-pagina functionaliteit
	#
	
	# profiel dat we tijdelijk openen om te wijzigen of af te beelden
	var $_tmpprofile;
	# in delta worden veranderingen die gemaakt worden in profiel opgeslagen
	# de veranderingen worden in loadPostTmpProfile ontdekt door POST-invoer
	# met het huidige profiel te vergelijken, en de verschillen worden dan in
	# delta gezet. vervolgens kan de functie delta_to_xml er een xml bestandje van
	# maken, delta_to_sql kan de verandering in sql doorvoeren, en delta_to_ldap
	# kan de veranderingen naar ldap wegschrijren
	var $_delta;
	# Hierin worden tijdens controleren van invoer foutmeldingen gezet die
	# dan weer worden afgebeeld door ProfielContent
	var $_formerror = array();

	function Lid(&$db) {
		# we starten op aan het begin van een pagina
		$this->_loadPermissions();
		$this->_db =& $db;
		#print_r($_SESSION);

		# kijken in de sessie of er een gebruiker in staat,
		# en of dit een gebruiker is die een profiel in de database heeft.
		if (!isset($_SESSION['_uid']) or !$this->reloadProfile()) {
			# zo nee, dan nobody user er in gooien...
			# in dit geval is het de eerste keer dat we een pagina opvragen
			# of er is net uitgelogd waardoor de gegevens zijn leeggegooid
			$this->login('x999','x999');
		}
		# experimentele logfunctie
		$this->logBezoek();
	}

	### public ###

	function login($user,$pass) {
		#
		$user = $this->_db->escape($user);

		# eerst proberen we via de user-id de gebruiker te vinden
		$result = $this->_db->select("SELECT * FROM `lid` WHERE `uid` = '{$user}' LIMIT 1");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$profile = $this->_db->next($result);
			if ($this->_checkpw($profile['password'], $pass)) {
				$this->_profile = $profile;
				$_SESSION['_uid'] = $profile['uid'];
				return true;
			}
		}
		# anders via de nickname N.B. deze nickname search is *case-insensitive*
		$result = $this->_db->select("SELECT * FROM `lid` WHERE `nickname` = '{$user}' LIMIT 1");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$profile = $this->_db->next($result);
			if ($this->_checkpw($profile['password'], $pass)) {
				$this->_profile = $profile;
				$_SESSION['_uid'] = $profile['uid'];
				return true;
			}
		}
		# helaasch
		return false;
	}

	
	function reloadProfile() {
		$result = $this->_db->select("SELECT * FROM `lid` WHERE `uid` = '{$_SESSION['_uid']}' LIMIT 1");
        if (($result !== false) and $this->_db->numRows($result) > 0) {
			$this->_profile = $this->_db->next($result);
			return true;
		}
		return false;
	}
	

	function logout() {
		session_unset();
		setcookie (session_name(), '', (time () - 2592000), '/', '', 0);
		session_destroy();
	}

	### public ###

	function hasPermission($descr) {
		# ga alleen verder als er een geldige permissie wordt gevraagd
		if (!array_key_exists($descr, $this->_permissions)) return false;
		# zoek de code op
		$gevraagd = (int) $this->_permissions[$descr];

		# zoek de rechten van de gebruiker op
		$liddescr = $this->_profile['permissies'];
		# ga alleen verder als er een geldige permissie wordt teruggegeven
		if (!array_key_exists($liddescr, $this->_perm_user)) return false;
		# zoek de code op
		$lidheeft = $this->_perm_user[$liddescr];

		# $p is de gevraagde permissie als octaal getal
		# de permissies van de gebruiker kunnen we bij $this->_lid opvragen
		# als we die 2 met elkaar AND-en, dan moet het resultaat hetzelfde
		# zijn aan de gevraagde permissie. In dat geval bestaat de permissie
		# van het lid dus minimaal uit de gevraagde permissie
		#
		# voorbeeld:
		#  gevraagd:   P_FORUM_MOD: 0000000700
		#  lid heeft:  P_LID      : 0005544500
		#  AND resultaat          : 0000000500 -> is niet wat gevraagd is -> weiger
		#
		#  gevraagd:  P_DOCS_READ : 0000004000
		#  gebr heeft: P_LID      : 0005544500
		#  AND resultaat          : 0000004000 -> ja!

		$resultaat = $gevraagd & $lidheeft;
		if (!($resultaat == $gevraagd)) return false;

		return true;
	}

	function getUid() { return $this->_profile['uid']; }
	function getNickName() { return $this->_profile['nickname']; }

	# <DEPRECATED> een keertje search-replace doen op deze functies ofzo...
	function isLoggedIn() { return $this->hasPermission('P_LOGGED_IN'); }
	function getLoginName() { return $this->getUid(); }
	# </DEPRECATED>

	function getProfile() { return $this->_profile; }
	
	# naast het profiel van de huidige gebruiker is er een variabele _tmpprofile,
	# die gebruikt wordt voor het inladen van een profiel wat op de profiel-pagina
	# getoond wordt, en waar wijzigingen in gemaakt worden.

	# profiel inladen
	function loadSqlTmpProfile($uid) {
		# kijken of uid een goed formaat heeft
		if (!preg_match('/^[a-z\d]{1}\d{3}$/', $uid)) return false;
		
		# anders databaseconnectie openen
		if (!isset($this->_db)) $this->_db = new MySQL ();
		# en gebruiker opzoeken
		$uid = $this->_db->escape($uid);
		$result = $this->_db->select("SELECT * FROM `lid` WHERE `uid` = '{$uid}' LIMIT 1");
        if (($result !== false) and $this->_db->numRows($result) > 0) {
			$this->_tmpprofile =  $this->_db->next($result);
			return true;
		}
		return false;
	}
	
	# inladen van de gegevens van het formulier. we geven een errorcode
	# terug. als die 0 is dan klopt alles, bij 1 dan gaat het grondig mis,
	# en bij 2 zitten er nog fouten in de invoer
	
	# N.B. in $this->_tmpprofile wordt vlak voor het aanroepen van deze functie het huidige
	# profiel ingeladen, zodat we snel kunnen vergelijken.
	
	# Als een ingevulde waarde verschilt van de oude, dan controleren we de nieuwe waarde, en
	# als het klopt dan zetten we de nieuwe waarde klaar in $this->_delta om de oude te gaan vervangen.
	function loadPostTmpProfile() {
		# foutmeldingen leeggooien
		$this->_formerror = array();
		# uid waarvoor we wijzingen in delta zetten
		$this->_delta['uid'] = $this->_tmpprofile['uid'];
		
		# 1. eerst de tekstvelden die het lid zelf mag wijzigen
		# NB: beroep en eetwens wordt niet getoond in het profiel bij S_LID, adres ouders niet bij S_OUDLID
		$velden = array('adres' => 100, 'postcode' => 20, 'woonplaats' => 50, 'land' => 50, 'o_adres' => 100,
		  'o_postcode' => 20, 'o_woonplaats' => 50, 'o_land' => 50, 'skype' => 50, 'eetwens' => 50, 'beroep' => 750 );
		# voor al deze veldnamen...
		foreach($velden as $veld => $max_lengte) {
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if ($invoer != "" and !is_utf8($invoer)) {
						$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
					} elseif (mb_strlen($invoer) > $max_lengte) {
						$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => $veld,
							'oud'  => $this->_tmpprofile[$veld],
							'nieuw'  => $invoer
						);
					}
				}
			}
		}
		
		# 2. Nickname -> nickname mag nog niet voorkomen N.B. deze nickname search is *case-insensitive*
		$veld = 'nickname';
		$max_lengte = 20;

		if (isset($_POST['frmdata'][$veld])) {
			$invoer = trim(strval($_POST['frmdata'][$veld]));
			# is het wel een wijziging?
			if ($invoer != $this->_tmpprofile[$veld]) {
				# controleren op juiste inhoud...
				if ($invoer != "" and !is_utf8($invoer)) {
					$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
				} elseif (mb_strlen($invoer) > $max_lengte) {
					$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
				} elseif ($invoer != "" and $this->nickExists($invoer)) {
					$this->_formerror[$veld] = "Deze bijnaam is al in gebruik.";
				}
				
				# als er geen fout is opgetreden veranderde waarde bewaren
				if (!isset($this->_formerror[$veld])) {
					# bewaar oude en nieuwe waarde in delta
					$this->_delta['diff'][] = array (
						'veld' => $veld,
						'oud'  => $this->_tmpprofile[$veld],
						'nieuw'  => $invoer
					);
				# anders ingevulde waarde terugzetten in het invoervak
				} else {
					$this->_tmpprofile[$veld] = $invoer;
				}
			}
		}

		# 7. Website
		$veld = 'website';
		$max_lengte = 80;

		if (isset($_POST['frmdata'][$veld])) {
			$invoer = trim(strval($_POST['frmdata'][$veld]));
			# is het wel een wijziging?
			if ($invoer != $this->_tmpprofile[$veld]) {
				# controleren op juiste inhoud...
				# Als er geen protocol aangegeven is, dan gooien we er http:// voor.
				if ($invoer != "" and is_utf8($invoer) and !preg_match("#^[\w]+?://#is",$invoer) )
					$invoer = 'http://'.$invoer;
				# controleren of het een geldige url is...
				if ($invoer != "" and (!is_utf8($invoer) or !preg_match("#([\w]+?://[^ \"\n\r\t<]*?)#is",$invoer) ) ) {
					$this->_formerror[$veld] = "Ongeldige karakters:";
				} elseif (mb_strlen($invoer) > $max_lengte) {
					$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
				}
				
				# als er geen fout is opgetreden veranderde waarde bewaren
				if (!isset($this->_formerror[$veld])) {
					# bewaar oude en nieuwe waarde in delta
					$this->_delta['diff'][] = array (
						'veld' => $veld,
						'oud'  => $this->_tmpprofile[$veld],
						'nieuw'  => $invoer
					);
				# anders ingevulde waarde terugzetten in het invoervak
				} else {
					$this->_tmpprofile[$veld] = $invoer;
				}
			}
		}
			
		# 3. forum-instellingen
		$veld = 'forum_name';
		if (isset($_POST['frmdata'][$veld])) {
			$invoer = trim(strval($_POST['frmdata'][$veld]));
			if ($invoer != 'civitas' and $invoer != 'nick') $invoer = 'civitas';
			# is het wel een wijziging?
			if ($invoer != $this->_tmpprofile[$veld]) {
				# bewaar oude en nieuwe waarde in delta
				$this->_delta['diff'][] = array (
					'veld' => $veld,
					'oud'  => $this->_tmpprofile[$veld],
					'nieuw'  => $invoer
				);
			}
		}
		
		
		# 4. telefoonvelden
		$velden = array('telefoon', 'mobiel', 'o_telefoon');
		foreach ($velden as $veld) {
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# geldige telefoonnummers...
					if (!preg_match('/^(\d{4}-\d{6}|\d{3}-\d{7}|\d{2}-\d{8}|\+\d{10-20})$/', $invoer) and $invoer != "") {
						$this->_formerror[$veld] = "Geldig formaat: 0187-123456; 015-2135681; 06-12345678; +31152135681";
					}
					
					# als er geen fout is opgetreden veranderde waarde bewaren
					if (!isset($this->_formerror[$veld])) {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => $veld,
							'oud'  => $this->_tmpprofile[$veld],
							'nieuw'  => $invoer
						);
					# anders ingevulde waarde terugzetten in het invoervak
					} else {
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}
		}
		
		# 5. ICQ nummer
		$veld = 'icq';
		if (isset($_POST['frmdata'][$veld])) {
			$invoer = trim(strval($_POST['frmdata'][$veld]));
			# is het wel een wijziging?
			if ($invoer != $this->_tmpprofile[$veld]) {
				if (!preg_match('/^\d{5,10}$/', $invoer) and $invoer != "") {
					$this->_formerror[$veld] = "Gebruik 5 tot 10 getallen:";
				}

				# als er geen fout is opgetreden veranderde waarde bewaren
				if (!isset($this->_formerror[$veld])) {
					# bewaar oude en nieuwe waarde in delta
					$this->_delta['diff'][] = array (
						'veld' => $veld,
						'oud'  => $this->_tmpprofile[$veld],
						'nieuw'  => $invoer
					);
				# anders ingevulde waarde terugzetten in het invoervak
				} else {
					$this->_tmpprofile[$veld] = $invoer;
				}
			}
		}

		# 6. Mailadressen
		$velden = array('email', 'msn');
		foreach ($velden as $veld) {
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# we gaan dus iets veranderen.
					# eerst kijken of de nieuwe invoer niet een leeg vak is
					if ($invoer != "") {
						# staat er wel een @ in?
						if (strpos($invoer,'@') === false) {
							# zo nee, dan is het zowieso ongeldig
							$this->_formerror[$veld] = "Ongeldig formaat email-adres:";
						} else {
							# anders gaan we m ontleden en controleren
							list ($usr,$dom) = split ('@', $invoer);
							if (mb_strlen($usr) > 50) {
								$this->_formerror[$veld] = "Gebruik max. 50 karakters voor de @:";
							} elseif (mb_strlen($dom) > 50) {
								$this->_formerror[$veld] = "Gebruik max. 50 karakters na de @:";
							# RFC 821 <- voorlopig voor JabberID even zelfde regels aanhouden
							# http://www.lookuptables.com/
							# Hmmmz, \x2E er uit gehaald ( . )
							} elseif (preg_match('/[^\x21-\x7E]/', $usr) or
					                  preg_match('/[\x3C\x3E\x28\x29\x5B\x5D\x5C\x2C\x3B\x40\x22]/', $usr)) {
								$this->_formerror[$veld] = "Het adres bevat ongeldige karakters voor de @:";
							} elseif (!preg_match("/^[a-z0-9]+([-.][a-z0-9]+)*\\.[a-z]{2,4}$/i", $dom)) {
								$this->_formerror[$veld] = "Het domein is ongeldig:";
							} elseif (!checkdnsrr($dom, 'A') and !checkdnsrr($dom, 'MX')) {
								$this->_formerror[$veld] = "Het domein bestaat niet (IPv4):";
							} elseif (!checkdnsrr($dom, 'MX')) {
								$this->_formerror[$veld] = "Het domein is niet geconfigureerd om email te ontvangen:";
							}
						}
					}
					
					# als er geen fout is opgetreden veranderde waarde bewaren
					if (!isset($this->_formerror[$veld])) {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => $veld,
							'oud'  => $this->_tmpprofile[$veld],
							'nieuw'  => $invoer
						);
					# anders ingevulde waarde terugzetten in het invoervak
					} else {
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}
		}

		# 9. Jabber ID
		$velden = array('jid');
		foreach ($velden as $veld) {
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# we gaan dus iets veranderen.
					# eerst kijken of de nieuwe invoer niet een leeg vak is
					if ($invoer != "") {
						# staat er wel een @ in?
						if (strpos($invoer,'@') === false) {
							# zo nee, dan is het zowieso ongeldig
							$this->_formerror[$veld] = "Ongeldig formaat Jabber ID:";
						} else {
							# anders gaan we m ontleden en controleren
							if (mb_strpos($invoer,'@') === false) {
								$this->_formerror[$veld] = "Dit lijkt niet op een Jabber ID...";
							} else {
								list ($usr,$dom) = split ('@', $invoer);
								if (mb_strlen($usr) > 50) {
									$this->_formerror[$veld] = "Gebruik max. 50 karakters voor de @:";
								} elseif (mb_strlen($dom) > 50) {
									$this->_formerror[$veld] = "Gebruik max. 50 karakters voor de @:";
								}
								# RFC 821 <- voorlopig voor JabberID even zelfde regels aanhouden
								# http://www.lookuptables.com/
								# Hmmmz, \x2E er uit gehaald ( . )
								elseif (preg_match('/[^\x21-\x7E]/', $usr) or
								        preg_match('/[\x3C\x3E\x28\x29\x5B\x5D\x5C\x2C\x3B\x40\x22]/', $usr)) {
									$this->_formerror[$veld] = "Het adres bevat ongeldige karakters voor de @:";
								} elseif (!preg_match("/^[a-z0-9]+([-.][a-z0-9]+)*\\.[a-z]{2,4}$/i", $dom)) {
									$this->_formerror[$veld] = "Het domein is ongeldig:";
								}
							}
						}
					}
					
					# als er geen fout is opgetreden veranderde waarde bewaren
					if (!isset($this->_formerror[$veld])) {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => $veld,
							'oud'  => $this->_tmpprofile[$veld],
							'nieuw'  => $invoer
						);
					# anders ingevulde waarde terugzetten in het invoervak
					} else {
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}
		}

		# 8. password veranderen
		$velden = array('oldpass', 'nwpass', 'nwpass2');
		$pwveldenset = true;
		# controleren of velden in de invoer zitten
		foreach ($velden as $veld) if (!isset($_POST['frmdata'][$veld])) $pwveldenset = false;
		# alleen doorgaan als ze er alledrie zijn
		if ($pwveldenset === true) {
			$oldpass = strval($_POST['frmdata']['oldpass']);
			$nwpass = strval($_POST['frmdata']['nwpass']);
			$nwpass2 = strval($_POST['frmdata']['nwpass2']);
		
			# alleen actie ondernemen als er een oud password is ingevuld
			if ($oldpass != "" or $nwpass != "" or $nwpass2 != "") {
				if ($oldpass == "" and ($nwpass != "" or $nwpass2 != "")) {
					$this->_formerror['oldpass'] = "Vul ook uw huidige wachtwoord in:";
				# we kijken of het oude wachtwoord klopt
				} elseif (!$this->_checkpw($this->_profile['password'], $oldpass)) {
					$this->_formerror['oldpass'] = "Het huidige wachtwoord is onjuist:";
				# of er wat nieuws is ingevuld...
				} elseif ($nwpass == "" and $nwpass2 == "") {
					$this->_formerror['nwpass'] = "Vul ook het nieuwe wachtwoord 2x in:";
				# daarna of de twee nieuwe overeenkomen
				} elseif($nwpass != $nwpass2) {
					$this->_formerror['nwpass'] = "De nieuwe wachtwoorden komen niet overeen:";
				# daarna of het nieuwe wel aan de veiligheidscriteria voldoet
				} elseif(!$this->_isSecure($this->_profile['uid'], $this->_profile['nickname'], $nwpass, $tmperror)) {
					$this->_formerror['nwpass'] = $tmperror;
				# anders is het wel ok...
				} else {
					# bewaar oude en nieuwe waarde in delta
					$this->_delta['diff'][] = array (
						'veld' => 'password',
						'oud'  => $this->_tmpprofile['password'],
						'nieuw'  => $this->_makepasswd($nwpass)
					);
				}
			}
		}

		# Extra velden die gewijzigd kunnen worden... Oudleden kunnen meer elementaire velden wijzigen
		# als hun naam, hun studiejaar etc, om de oudledenlijst compleet te krijgen.
		# De Vice-Abactis kan de info van iedereen wijzigen.
		
		if ($this->_profile['status'] == 'S_OUDLID' or $this->hasPermission('P_LEDEN_MOD')) {
			
			# Info over naam, studieomschrijving
			$velden = array('voornaam' => 50, 'tussenvoegsel' => 15, 'achternaam' => 50, 'studie' => 100);
			# voor al deze veldnamen...
			foreach($velden as $veld => $max_lengte) {
				# kijken of ze in POST voorkomen...
				if (isset($_POST['frmdata'][$veld])) {
					$invoer = trim(strval($_POST['frmdata'][$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and !is_utf8($invoer)) {
							$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
						} elseif (mb_strlen($invoer) > $max_lengte) {
							$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->_delta['diff'][] = array (
								'veld' => $veld,
								'oud'  => $this->_tmpprofile[$veld],
								'nieuw'  => $invoer
							);
						}
					}
				}
			}
		
			# jaartallen etc...
			$velden = array ('studiejaar', 'lidjaar');
			# moet een getal tussen 1900 en 2100 zijn allemaal
			foreach($velden as $veld) {
				# kijken of ze in POST voorkomen...
				if (isset($_POST['frmdata'][$veld])) {
					$invoer = trim(strval($_POST['frmdata'][$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and $invoer != (int)$invoer) {
							$this->_formerror[$veld] = "Ongeldige karakters, typ een jaartal:";
						} elseif ($invoer < 1900 or $invoer > 2100) {
							$this->_formerror[$veld] = "Het jaartal ligt buiten toegestane grenzen:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->_delta['diff'][] = array (
								'veld' => $veld,
								'oud'  => $this->_tmpprofile[$veld],
								'nieuw'  => $invoer
							);
						}
					}
				}
			}
			
			# geboortedatum
			$veld = 'gebdatum';
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# Kijk of de invoer zinvol te splitsen is in d-m-YYYY
				$matches = array();
				if (!preg_match('/^(\d\d?)-(\d\d?)-(\d{4}$)/', $invoer, $matches)) {
					$this->_formerror[$veld] = "Ongeldige datumformaat, gebruik dag-maand-jaar:";
					$this->_tmpprofile['gebdag']  = '00';
					$this->_tmpprofile['gebmnd']  = '00';
					$this->_tmpprofile['gebjaar'] = '0000';
				} else {
					# dag van de maand
					$gebdag = (int)$matches[1];
					$gebmnd = (int)$matches[2];
					$gebjaar = (int)$matches[3];

					# is het wel een wijziging?
					if ($gebdag != (int)$this->_tmpprofile['gebdag'] or
					    $gebmnd != (int)$this->_tmpprofile['gebmnd'] or
					    $gebjaar != (int)$this->_tmpprofile['gebjaar'] ) {
					    # dan gaan we controleren of de nieuwe datum een bestaande
					    # datum is...
					    
					    # maak eerst een datum-string rechtstreeks van de ingevoerde waarden
					    $datumstr = sprintf('%02d-%02d-%04d',$gebdag,$gebmnd,$gebjaar);
					    # maak daarna een die we door mktime en date halen om te kijken of
					    # dezelfde datum er weer uit komt. bijv. 30 feb wordt 02 maart
					    # als deze string hetzelfde is als voorgaande is het dus een bestaande
					    # datum
					    $datumstr_adj = date("d-m-Y", mktime(0, 0, 0, $gebmnd, $gebdag, $gebjaar));
					    
					    if ($datumstr != $datumstr_adj) {
							$this->_formerror[$veld] = "Opgegeven datum bestaat niet:";
					    }
					}

					# als er geen fout is opgetreden veranderde waarde bewaren
					if (!isset($this->_formerror[$veld])) {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => 'gebdag',
							'oud'  => $this->_tmpprofile['gebdag'],
							'nieuw'  => $gebdag
						);
						$this->_delta['diff'][] = array (
							'veld' => 'gebmnd',
							'oud'  => $this->_tmpprofile['gebmnd'],
							'nieuw'  => $gebmnd
						);
						$this->_delta['diff'][] = array (
							'veld' => 'gebjaar',
							'oud'  => $this->_tmpprofile['gebjaar'],
							'nieuw'  => $gebjaar
						);
					# anders ingevulde waarde terugzetten in het invoervak
					} else {
						$this->_tmpprofile['gebdag']  = $gebdag;
						$this->_tmpprofile['gebmnd']  = $gebmnd;
						$this->_tmpprofile['gebjaar'] = $gebjaar;
					}
				}
			}
		}

		# Extra velden die gewijzigd kunnen worden door am. Vice-Abactis
		if ($this->hasPermission('P_LEDEN_MOD')) {

			$velden = array('postfix' => 7, 'voornamen' => 100, 'kerk' => 50, 'muziek' => 100);
			# voor al deze veldnamen...
			foreach($velden as $veld => $max_lengte) {
				if (isset($_POST['frmdata'][$veld])) {
					$invoer = trim(strval($_POST['frmdata'][$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and !is_utf8($invoer)) {
							$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
						} elseif (mb_strlen($invoer) > $max_lengte) {
							$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->_delta['diff'][] = array (
								'veld' => $veld,
								'oud'  => $this->_tmpprofile[$veld],
								'nieuw'  => $invoer
							);
						}
					}
				}
			}

			# kring en moot
			$velden = array ('kring' => 10, 'moot' => 4);
			foreach($velden as $veld => $max) {
				# kijken of ze in POST voorkomen, zo niet...
				if (!isset($_POST['frmdata'][$veld])) {
					$this->_formerror[$veld] = "Whraagh! ik mis een veld in de data! --> {$veld}";
				} else {
					$invoer = trim(strval($_POST['frmdata'][$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and $invoer != (int)$invoer) {
							$this->_formerror[$veld] = "Ongeldige karakters, kies een getal:";
						} elseif ($invoer < 0 or $invoer > $max) {
							$this->_formerror[$veld] = "De invoer ligt buiten toegestane grenzen:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->_delta['diff'][] = array (
								'veld' => $veld,
								'oud'  => $this->_tmpprofile[$veld],
								'nieuw'  => $invoer
							);
						}
					}
				}
			}
			
			# is deze persoon kringleider? (n)iet, (e)erstejaars, (o)uderejaars
			$veld = 'kringleider';
			# kijken of het veld in POST voorkomt, zo niet...
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if (!preg_match('/^[neo]$/', $invoer)) {
						$this->_formerror[$veld] = "Gebruik (n)iet, (e)erstejaars, (o)uderejaars:";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => $veld,
							'oud'  => $this->_tmpprofile[$veld],
							'nieuw'  => $invoer
						);
					}
				}
			}

			# is deze persoon motebal? (0) nee, (1) ja
			$veld = 'motebal';
			# kijken of het veld in POST voorkomt, zo niet...
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if (!preg_match('/^[01]$/', $invoer)) {
						$this->_formerror[$veld] = "Gebruik (0) nee, (1) ja:";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => $veld,
							'oud'  => $this->_tmpprofile[$veld],
							'nieuw'  => $invoer
						);
					}
				}
			}

			# is deze persoon (m)an/(v)rouw?
			$veld = 'geslacht';
			# kijken of het veld in POST voorkomt, zo niet...
			if (isset($_POST['frmdata'][$veld])) {
				$invoer = trim(strval($_POST['frmdata'][$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if (!preg_match('/^[mv]$/', $invoer)) {
						$this->_formerror[$veld] = "Gebruik (m)an, (v)rouw:";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->_delta['diff'][] = array (
							'veld' => $veld,
							'oud'  => $this->_tmpprofile[$veld],
							'nieuw'  => $invoer
						);
					}
				}
			}

		}
		
		# als er regels in formerror staan betekent het dat we niet verder gaan met opslaan van
		# wijzigingen, maar dat we er met een foutmelding nu uit knallen, en de invoer en de
		# foutmeldingen aan de gebruiker laten tonen
		if (count($this->_formerror) != 0) return 2;
		return 0;
		
	}
	function getTmpProfile() { return $this->_tmpprofile; }
	function getFormErrors() { return $this->_formerror; }

	function diff_to_sql() {
		# Zijn er wel wijzigingen?
		if (isset($this->_delta['diff']) and is_array($this->_delta['diff']) and count($this->_delta['diff']) > 0) {
			$sqldata = array();
			foreach ($this->_delta['diff'] as $diff) {
				$sqldata[$diff['veld']] = $this->_db->escape($diff['nieuw']);
			}

			# opslaan van de waarden in de database
			$this->_db->update_a('lid', 'uid', $this->_delta['uid'], $sqldata);
		}
	}

	# We gaan de wijzigingen doorvoeren in ldap, alleen moeten we wel rekening houden
	# met samengestelde velden!
	# als een van deze velden is veranderd, voegen we de andere velden in de groep ook
	# toe voordat we het naar ldap sturen.
	/*
	function diff_to_ldap() {
		$groep_naam  = array('voornaam', 'tusenvoegsel', 'achternaam');
		# kijken of minimaal een van de naam-velden voorkomt
		if (

		$groep_adres = array('adres', 'psotcode', 'plaats', 'land');
		
	
	}
	*/

	function getPermissions() { return $this->_profile['permissies']; }
	function getStatus()      { return $this->_profile['status']; }
	function getForumInstelling(){
		return array('forum_naam' => $this->_profile['forum_name']);
	}
	function getForumNaamInstelling(){ return $this->_profile['forum_name']; }
	function getMoot() { return $this->_profile['moot']; }
	function getFullName($uid = '') {
		if ($uid == '' or $uid == $this->_profile['uid']) {
			$fullname = $this->_profile['voornaam'];
			if ($this->_profile['tussenvoegsel'] != '')
				$fullname .=' '.$this->_profile['tussenvoegsel'];
			if ($this->_profile['achternaam'] != '')
				$fullname .=' '.$this->_profile['achternaam'];
		} else {
			$result = $this->_db->select("SELECT voornaam, tussenvoegsel, achternaam FROM lid WHERE uid='".$uid."' LIMIT 1;");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				$record = $this->_db->next($result);
				$fullname = str_replace('  ', ' ',implode(' ',array($record['voornaam'],$record['tussenvoegsel'],$record['achternaam'])));
			} else $fullname = 'Niet bekend';
		}
		return $fullname;
	}

	function getCivitasName($uid = ''){
		$sCivitasNaam='';
		if ($uid == '' or $uid == $this->_profile['uid']) {
			$sCivitasNaam = ($this->_profile['geslacht']=='v') ? 'Ama. ' : 'Am. ';
			if ($this->_profile['tussenvoegsel'] != '') $sCivitasNaam.=$this->_profile['tussenvoegsel'].' ';
			$sCivitasNaam.=$this->_profile['achternaam'];
		} else {
			$result = $this->_db->select("SELECT voornaam, tussenvoegsel, achternaam, postfix, geslacht FROM lid WHERE uid='".$uid."' LIMIT 1;");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				$record = $this->_db->next($result);
				$sCivitasNaam = ($record['geslacht']=='v') ? 'Ama. ' : 'Am. ';
				if ($record['tussenvoegsel'] != '') $sCivitasNaam.=$record['tussenvoegsel'].' ';
				$sCivitasNaam.=$record['achternaam'];				
				if ($record['postfix'] != '') $sCivitasNaam.=' '.$record['postfix'];
			} else $sCivitasNaam = 'Niet bekend';
		}
		
		return $sCivitasNaam;
	}

	function _loadPermissions() {
		# Hier staan de permissies die voor enkele onderdelen van
		# de website nodig zijn. Ze worden zowel op de 'echte'
		# website als in het beheergedeelte gebruikt.

		# READ = Rechten om het onderdeel in te zien
		# POST = Rechten om iets toe te voegen
		# MOD  = Moderate rechten, dus verwijderen enzo
		# Let op: de rechten zijn cumulatief en octaal
		
		$this->_permissions = array(
			'P_NOBODY'       => 00000000001,
			'P_LOGGED_IN'    => 00000000003, # Leden-menu, eigen profiel raadplegen
			'P_ADMIN'        => 00000000007, # Admin dingen algemeen...	
			'P_FORUM_READ'   => 00000000400, # Forum lezen
			'P_FORUM_POST'   => 00000000500, # Berichten plaatsen op het forum en eigen berichten wijzigen
			'P_FORUM_MOD'    => 00000000700, # Forum-moderator mag berichten van anderen wijzigen of verwijderen
			'P_DOCS_READ'    => 00000004000, # Documenten-rubriek lezen
			'P_DOCS_POST'    => 00000005000, # Documenten verwijderen of erbij plaatsen
			'P_DOCS_MOD'     => 00000007000, # euh?
			'P_PROFIEL_EDIT' => 00000010000, # Eigen gegevens aanpassen
			'P_LEDEN_READ'   => 00000040000, # Gegevens over andere leden raadplegen
			'P_LEDEN_EDIT'   => 00000020000, # Profiel van andere leden wijzigen
			'P_LEDEN_MOD'    => 00070070000, # samengestelde om te kunnen lezen en veranderen bij iedereen
			'P_AGENDA_READ'  => 00000400000, # Agenda bekijken
			'P_AGENDA_POST'  => 00000500000, # Items toevoegen aan de agenda
			'P_AGENDA_MOD'   => 00000700000, # euh?
			'P_NEWS_POST'    => 00001000000, # Nieuws plaatsen en wijzigen van jezelf
			'P_NEWS_MOD'     => 00003000000, # Nieuws-moderator mag berichten van anderen wijzigen of verwijderen
			'P_OUDLEDEN_EDIT'=> 00020000000, # Profiel van andere leden wijzigen
			'P_OUDLEDEN_READ'=> 00040000000, # Gegevens over andere leden raadplegen
			'P_OUDLEDEN_MOD' => 00070070000, # samengestelde om te kunnen lezen en veranderen bij iedereen
			                                 # oudleden-mod is gelijk aan leden-mod
			'P_MAAL_IK'      => 00100000000, # kan zich aan en afmelden voor maaltijd en eigen abo wijzigen
			'P_MAAL_WIJ'     => 00500000000, # kan ook anderen aanmelden (niet afmelden!)
			'P_MAAL_MOD'     => 00700000000, # mag maaltijd aan- en afmeldingen voor iedereen wijzigen
			'P_MAIL_POST'    => 02000000000, # mag berichtjes in de pubciemail rossen
			'P_MAIL_COMPOSE' => 04000000000, # mag alle berichtjes in de pubcie-mail bewerken, en volgorde wijzigen
			'P_MAIL_SEND'    => 06000000000, # mag de C.S.R.-mail verzenden
			'P_BIEB_READ'    => 00000000020, # Bibliotheek lezen
			'P_BIEB_EDIT'    => 00000000040, # Bibliotheek wijzigen		
			'P_BIEB_MOD'     => 00000000060, # Bibliotheek zowel wijzigen als lezen	
			# N.B. bij uitbreiding van deze octale getallen met nog een cijfer erbij gaat er iets mis, wat weten we nog niet.
		);

		# Deze waarden worden samengesteld uit bovenstaande permissies en
		# worden in de gebruikersprofielen gebruikt als aanduiding voor
		# welke permissie-groep de gebruiker in zit.

		$p = $this->_permissions;
		$this->_perm_user = array(
			'P_NOBODY'     => $p['P_NOBODY'] | $p['P_FORUM_READ'],
			'P_LID'        => $p['P_LOGGED_IN'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_DOCS_READ'] | $p['P_LEDEN_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_AGENDA_POST'] + $p['P_MAAL_WIJ'] + $p['P_MAIL_POST'],
			'P_OUDLID'     => $p['P_LOGGED_IN'] | $p['P_OUDLEDEN_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_FORUM_READ'],
			'P_MODERATOR'  => $p['P_ADMIN'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_MOD'] | $p['P_AGENDA_MOD'] | $p['P_MAAL_MOD'] | $p['P_MAIL_SEND'] | $p['P_NEWS_MOD'] | $p['P_BIEB_MOD']
		);
		# extra dingen, waarvoor de array perm_user zelf nodig is
		$this->_perm_user['P_PUBCIE']  = $this->_perm_user['P_MODERATOR'];
		$this->_perm_user['P_MAALCIE'] = $this->_perm_user['P_LID'] | $p['P_MAAL_MOD'];
		$this->_perm_user['P_BESTUUR'] = $this->_perm_user['P_LID'] | $p['P_OUDLEDEN_READ'] | $p['P_NEWS_MOD'] | $p['P_MAAL_MOD'] | $p['P_AGENDA_POST'] | $p['P_FORUM_MOD'];
		$this->_perm_user['P_VAB']     = $this->_perm_user['P_BESTUUR'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_MOD'] | $p['P_BIEB_READ'];
		$this->_perm_user['P_KNORRIE'] = $this->_perm_user['P_LID'] | $p['P_MAAL_MOD'];

	}

	function _makepasswd($pass) {
		$salt = mhash_keygen_s2k(MHASH_SHA1, $pass, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass.$salt).$salt);
	}

	function _checkpw($hash, $pass) {
		// Verify SSHA hash
		$ohash = base64_decode(substr($hash, 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($pass . $osalt));
		#echo "ohash: {$ohash}, nhash: {$nhash}";
		if ($ohash == $nhash) return true;
		return false;
	}

	function _isSecure($uid, $nick, $passwd, &$error) {
		# We doen een aantal standaard checks die een foutmelding kunnen produceren...
		$error = "";
	
		$sim_uid = 0; $foo = similar_text($uid,$passwd,$sim_uid);
		$sim_nick = 0; $foo = similar_text($nick,$passwd,$sim_nick);

		# Korter dan 6 of langer dan 16 mag niet...
		if (mb_strlen($passwd) < 6 or mb_strlen($passwd) > 60) {
			$error = "Het wachtwoord moet minimaal 6 en maximaal 60 tekens lang zijn. :-/";
		# is het geldige utf8?
		} elseif (!is_utf8($passwd)) {
			$error = "Het nieuwe wachtwoord bevat ongeldige karakters... :-(";
		} elseif (preg_match('/^[0-9]*$/', $passwd)) {
			$error = "Het nieuwe wachtwoord moet ook letters of leestekens bevatten... :-|";
		//eisen zijn wat zwaar, deze er even uit halen
		//} elseif (preg_match('/^[A-Za-z]*$/', $passwd)) {
		//	$error = "Het nieuwe wachtwoord moet ook een cijfer of leesteken bevatten... :-S";
		} elseif ($uid == $passwd) {
			$error = "Het wachtwoord mag niet gelijk zijn aan je gebruikersnaam! :-@";
		} elseif ($sim_uid > 60) {
			$error = "Het wachtwoord lijkt teveel op je gebruikersnaam ;-]";
		} elseif ($sim_nick > 60) {
			$error = "Het wachtwoord lijkt teveel op je bijnaam ;-]";
		#} elseif () {
		}
		return ($error == "");
	}

	function zoekLeden($zoekterm, $zoekveld, $moot, $sort, $zoekstatus = '') {
		$leden = array();
		
		# mysql escape dingesen
		$zoekterm = $this->_db->escape($zoekterm);
		$zoekveld = $this->_db->escape($zoekveld);
		
		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if($zoekveld=='naam'){
			$zoekfilter="
				voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR 
				nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%'";
		}else{
			$zoekfilter="{$zoekveld} LIKE '%{$zoekterm}%'";
		}
		$sort = $this->_db->escape($sort);

		# in welke status wordt gezocht, is afhankelijk van wat voor rechten de
		# ingelogd persoon heeft
		
		$statusfilter = '';
		# we zoeken in leden als
		# 1. ingelogde persoon dat alleen maar mag of
		# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet oudleden alleen heeft gekozen
		if (
			($this->hasPermission('P_LEDEN_READ') and !$this->hasPermission('P_OUDLEDEN_READ') ) or
			($this->hasPermission('P_LEDEN_READ') and $this->hasPermission('P_OUDLEDEN_READ') and $zoekstatus != 'oudleden')
		   ) {
			$statusfilter .= "status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'";
		}
		# we zoeken in oudleden als
		# 1. ingelogde persoon dat alleen maar mag of
		# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet leden alleen heeft gekozen
		if (
			(!$this->hasPermission('P_LEDEN_READ') and $this->hasPermission('P_OUDLEDEN_READ') ) or
			($this->hasPermission('P_LEDEN_READ') and $this->hasPermission('P_OUDLEDEN_READ') and $zoekstatus != 'leden')
		   ) {
			if ($statusfilter != '') $statusfilter .= " OR ";
			$statusfilter .= "status='S_OUDLID'";
		}
		# als er een specifieke moot is opgegeven, gaan we alleen in die moot zoeken
		$mootfilter = ($moot != 'alle') ? 'AND moot= '.(int)$moot : '';

		# controleer of we ueberhaupt wel wat te zoeken hebben hier
		if ($statusfilter != '') {
			$result = $this->_db->select("
				SELECT
					* 
				FROM 
					lid 
				WHERE 
					(".$zoekfilter.")
				AND 
					($statusfilter) 
				{$mootfilter}
				ORDER BY 
					{$sort}");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				while ($lid = $this->_db->next($result)) $leden[] = $lid;
			}
		}

		return $leden;
	}
	
	function nickExists($nick) {
		# mysql escape dingesen
		$nick = $this->_db->escape($nick);
		
		$result = $this->_db->select("SELECT * FROM `lid` WHERE `nickname` = '{$nick}'");
        if ($result !== false and $this->_db->numRows($result) > 0)
			return true;
		return false;
	}
	
	function isValidUid($uid) {
		return preg_match('/^[a-z0-9]{4}$/', $uid) > 0;
	}


	function uidExists($uid) {
		if (!$this->isValidUid($uid)) return false;
		
		$result = $this->_db->select("SELECT * FROM `lid` WHERE `uid` = '{$uid}'");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			#echo $this->_db->numRows($result);
			return true;
		}
		return false;
	}
	
	function getLidStatus($uid) {
		# is het wel een geldig lid-nummer?
		if (!$this->isValidUid($uid)) return false;
		
		# opzoeken status
		$uid = $this->_db->escape($uid);
		$result = $this->_db->select("SELECT `status` FROM `lid` WHERE `uid` = '{$uid}'");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			$record = mysql_fetch_assoc($result);
			return $record['status'];
		}	
		return false;
	}

	function getAlleLeden($sort) {
		$leden = array();

		# mysql escape dingesen
		$sort = $this->_db->escape($sort);

		$result = $this->_db->select("
			SELECT * 
			FROM 
				lid 
			WHERE ( 
				status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL' ) ORDER BY `{$sort}`");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($lid = $this->_db->next($result)) $leden[] = $lid;
		}

		return $leden;
	}

	function getVerjaardagen($maand, $dag=0) {
		$maand = (int)$maand; $dag = (int)$dag; $vrjdgn = array();
		$query="
			SELECT 
				uid, voornaam, tussenvoegsel, achternaam, geslacht, email, gebdag 
			FROM 
				lid 
			WHERE 
				(status='S_LID' OR `status`='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') 
			AND 
				gebmnd = '{$maand}'";
		if($dag!=0)	$query.=" AND gebdag=".$dag;
		$query.=" ORDER BY gebdag";
		$result = $this->_db->select($query);
			if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($vrjdg = $this->_db->next($result)) $vrjdgn[] = $vrjdg;
		}
		return $vrjdgn;
	}

	function getMaxKringen() {
		$maxkringen = 0;
		$result = $this->_db->select("SELECT MAX(`kring`) as `max` FROM `lid` WHERE (`status`='S_LID' OR `status`='S_GASTLID' OR `status`='S_NOVIET' OR status='S_KRINGEL')");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			$max = $this->_db->next($result);
			$maxkringen = $max['max'];
		}

		return $maxkringen;
	}

	function getMaxMoten() {
		$maxmoten = 0;
		$result = $this->_db->select("
			SELECT MAX(`moot`) as `max` FROM `lid` WHERE (`status`='S_LID' OR `status`='S_GASTLID' OR `status`='S_NOVIET' OR status='S_KRINGEL')");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			$max = $this->_db->next($result);
			$maxmoten = $max['max'];
		}

		return $maxmoten;
	}

	function getKringen() {
		$kring = array();
		$result = $this->_db->select("
			SELECT 
				voornaam, 
				tussenvoegsel, 
				achternaam, 
				moot, 
				kring, 
				motebal, 
				kringleider,
				status
			FROM 
				`lid` 
			WHERE 
				`status`='S_LID' OR `status`='S_GASTLID' OR `status`='S_NOVIET' OR status='S_KRINGEL'
			ORDER BY 
				`kringleider` DESC,
				`achternaam` ASC;");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($lid = $this->_db->next($result)) {
        $kring[$lid['moot']][$lid['kring']][] = array(
					'naam' => str_replace('  ', ' ', implode(' ', array($lid['voornaam'],$lid['tussenvoegsel'],$lid['achternaam']))),
					'motebal' => $lid['motebal'],
					'kringleider' => $lid['kringleider'],
					'status'=> $lid['status']
				);
			}
		}

		return $kring;
	}
	
	# deze functie wordt door maaltrack gebruikt om de namen van mensen en hun eetwens
	# toe te voegen aan een lijst met inschrijvingen
	# parameter: $lijst, een array waar een veld genaamd 'uid' in moet zitten
	# de functie maakt dan een veld 'naam' en 'eetwens' erbij
	function addNames(&$lijst) {
		foreach ($lijst as $l => $foo) {
			$result = $this->_db->select("
				SELECT `voornaam`, `tussenvoegsel`, `achternaam`, `eetwens`
				FROM `lid`
				WHERE `uid`='{$foo['uid']}'
			");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				$lid = $this->_db->next($result);
				$naam = $lid['voornaam'];
				if ($lid['tussenvoegsel'] != '') $naam .= " {$lid['tussenvoegsel']}";
				if ($lid['achternaam'] != '') $naam .= " {$lid['achternaam']}";
				$lijst[$l]['naam'] = $naam;
				$lijst[$l]['eetwens'] = $lid['eetwens'];
			} else {
				$lijst[$l]['naam'] = $l['uid']."/onbekend";			
				$lijst[$l]['eetwens'] = "";
			}
		}
	}
	
	function getSaldo($uid=''){
		if($uid==''){
			$uid=$this->getUid();
		}
		$query="
			SELECT
				saldo
			FROM
				socciesaldi
			WHERE
				uid='".$uid."'
			LIMIT 1;";
		$rSaldo=$this->_db->query($query);
		if($this->_db->numRows($rSaldo)){
			$aSaldo=$this->_db->next($rSaldo);
			return $aSaldo['saldo'];
		}else{
			return false;
		}
	}
	function logBezoek(){
		$uid=$this->getUid();
		$datumtijd=date('Y-m-d H:i:s');
		if(isset($_SERVER['REMOTE_ADDR'])){ 
			$ip=$this->_db->escape($_SERVER['REMOTE_ADDR']);
			if(opConfide()){ 
				$locatie='Confide'; 
			}elseif(substr($ip, 0, 8)=='130.161.'){ 
				$locatie='TU';
			}else{
				switch($ip){
					case '83.160.162.84': $locatie='VDelphia'; break;
					case '145.99.162.33': //HVG
					case '83.84.128.14': //Sief
						$locatie='HVG';
					break;
					case '145.94.89.206': $locatie='tEis'; break;
					case '145.94.91.223': //nance.tnw-s 
					case '145.94.91.245': //aljen
						$locatie='Aenslag';
					break;
					case '80.100.35.230': $locatie='tAiland'; break;
					case '82.156.121.74': $locatie='LachaiRoi'; break;
					case '82.171.125.214': $locatie='SpoorBijster'; break;
					case '82.171.113.19': $locatie='vSpeijk'; break;
					case '83.84.133.73': $locatie='denHertog'; break;
					case '62.51.57.11': $locatie='Molshoop'; break;
					case '62.234.90.217': $locatie='WankelCentrum'; break;
					case '82.171.112.16': $locatie='GoudenLeeuw'; break;
					case '145.99.161.74': $locatie='Koornmarkt'; break;
					case '82.171.127.200': $locatie='Internaat'; break;
					case '84.35.65.254': $locatie='Perron0'; break;
					case '82.170.83.173': $locatie='JongeGarde'; break;
					case '80.60.95.203': $locatie='Sonnenvanck'; break;
					case '82.156.239.192': $locatie='Caesarea'; break;
					case '62.51.55.15': $locatie="bras98"; break;
					case '145.94.59.158': //Jieter
					case '145.94.61.229': //rommel
						$locatie='Rommel'; 
					break;
					case '145.94.58.19': //Allert									
					case '145.94.59.219': //Peturr							    
					case '145.94.75.148': //Jorrit
						$locatie='Adam';
					break;
					case '145.94.154.180': //OD11.fttd-s
					case '145.94.141.116': //heidiho.fttd-s
					case '145.94.58.113': //tommie
					case '145.94.62.139': //jona
					case '145.94.121.167': //peter Goudswaard
						$locatie='OD11';
					break;
					default:
						$locatie='';
				}//einde switch
			}
		}else{ 
			$ip='0.0.0.0'; $locatie='';
		}
		if(isset($_SERVER['REQUEST_URI'])){ $url=$this->_db->escape($_SERVER['REQUEST_URI']); }else{ $url=''; }
		if(isset($_SERVER['HTTP_REFERER'])){ $referer=$this->_db->escape($_SERVER['HTTP_REFERER']); }else{ $referer=''; }
		$agent='';
		if(isset($_SERVER['HTTP_USER_AGENT'])){ 
			if(preg_match('/bot/i', $_SERVER['HTTP_USER_AGENT']) OR preg_match('/crawl/i', $_SERVER['HTTP_USER_AGENT']) 
				OR preg_match('/slurp/i', $_SERVER['HTTP_USER_AGENT']) OR preg_match('/Teoma/i', $_SERVER['HTTP_USER_AGENT'])){
				if(preg_match('/google/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='googleBot'; 
				}elseif(preg_match('/msn/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='msnBot'; 
				}elseif(preg_match('/yahoo/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='yahooBot';
				}elseif(preg_match('/Jeeves/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='askJeeves';
				}else{ $agent='onbekende bot';}
			}else{
				if(preg_match('/Windows\ NT\ 5\.1/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows XP | '; 
				}elseif(preg_match('/Windows\ NT\ 5\.0/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows 2K | ';
				}elseif(preg_match('/Win\ 9x/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows 9x | ';
				}elseif(preg_match('/Windows/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows anders | ';
				}elseif(preg_match('/Ubuntu\/Dapper/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Ubuntu Dapper | ';
				}elseif(preg_match('/Ubuntu\/Breezy/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Ubuntu Breezy | ';
				}elseif(preg_match('/Ubuntu/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Ubuntu | ';
				}elseif(preg_match('/Linux/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Linux | ';
				}elseif(preg_match('/Google\ Desktop/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Google Desktop | ';
				}elseif(preg_match('/Microsoft/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='iets M$ | '; 
				}elseif(preg_match('/Mac\ OS\ X/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='OS X | ';
				}else{ $agent='onbekend | ('.$_SERVER['HTTP_USER_AGENT'].')'; }
				if(preg_match('/Firefox\/1\.5/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='FF1.5';
				}elseif(preg_match('/Firefox\/1\.0/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='FF1.0'; 
				}elseif(preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='FF';
				}elseif(preg_match('/Mozilla\/5\.0/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Mozilla';
				}elseif(preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Opera';  
				}elseif(preg_match('/MSIE\ 6\.0/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='IE6';
				}elseif(preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='IE';
				}elseif(preg_match('/Safari/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Safari'; 
				}elseif(preg_match('/Google\ Desktop/', $_SERVER['HTTP_USER_AGENT'])){ $agent.=''; 
				}elseif(preg_match('/Microsoft/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.=''; 
				}else{ $agent.='onbekend ('.$_SERVER['HTTP_USER_AGENT'].')'; }
			}
			
		}
		$sLogQuery="
			INSERT INTO 
				log
			( 
				uid, ip, locatie, moment, url, referer, useragent
			)VALUES(
				'".$uid."', '".$ip."', '".$locatie."', '".$datumtijd."', '".$url."', '".$referer."', '".$agent."'
			);";
		if(!preg_match('/stats.php/', $url) AND $ip!='0.0.0.0'){
			$this->_db->query($sLogQuery);
		}
	}
}
?>
