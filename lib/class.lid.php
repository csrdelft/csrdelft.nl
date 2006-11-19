<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------
#

require_once ('class.ldap.php');

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
	# kan de veranderingen naar ldap wegschrijven
	var $_delta = array();
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
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$user}' LIMIT 1");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$profile = $this->_db->next($result);
			if ($this->_checkpw($profile['password'], $pass)) {
				$this->_profile = $profile;
				$_SESSION['_uid'] = $profile['uid'];
				return true;
			}
		}
		# anders via de nickname N.B. deze nickname search is *case-insensitive*
		$result = $this->_db->select("SELECT * FROM lid WHERE nickname = '{$user}' LIMIT 1");
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
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$_SESSION['_uid']}' LIMIT 1");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$this->_profile = $this->_db->next($result);
			return true;
		}
		return false;
	}
	

	function logout() {
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
		
		# en gebruiker opzoeken
		$uid = $this->_db->escape($uid);
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$uid}' LIMIT 1");
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
	function loadPostTmpProfile($form=false) {
		# foutmeldingen leeggooien
		$this->_formerror = array();
		# delta leeggooien
		$this->_delta = array();
		
		//kijken of $form een array is. Als dat niet zo is, de postarry inladen
		if(!is_array($form)){
			//de post-array inladen in $form
			$form=$_POST['frmdata'];
		}
		
		# 1. eerst de tekstvelden die het lid zelf mag wijzigen
		# NB: beroep en eetwens wordt niet getoond in het profiel bij S_LID, adres ouders niet bij S_OUDLID
		$velden = array('adres' => 100, 'postcode' => 20, 'woonplaats' => 50, 'land' => 50, 
			'o_adres' => 100, 'o_postcode' => 20, 'o_woonplaats' => 50, 'o_land' => 50, 
			'skype' => 50, 'eetwens' => 50, 'beroep' => 750, 'bankrekening' => 12 );
		# voor al deze veldnamen...
		foreach($velden as $veld => $max_lengte) {
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if ($invoer != "" and !is_utf8($invoer)) {
						$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
					} elseif (mb_strlen($invoer) > $max_lengte) {
						$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
					}
					# als er geen fout is opgetreden veranderde waarde bewaren
					if (!isset($this->_formerror[$veld])) {
						# bewaar oude en nieuwe waarde in delta
						$this->storeDeltaProfile($veld, $invoer);
					}
					# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
					# of voor diff_to_*
					$this->_tmpprofile[$veld] = $invoer;
				}
			}
		}
		
		# 2. Nickname -> nickname mag nog niet voorkomen N.B. deze nickname search is *case-insensitive*
		$veld = 'nickname';
		$max_lengte = 20;

		if (isset($form[$veld])) {
			$invoer = trim(strval($form[$veld]));
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
					$this->storeDeltaProfile($veld, $invoer);
				}
				# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
				# of voor diff_to_*
				$this->_tmpprofile[$veld] = $invoer;
			}
		}

		# 7. Website
		$veld = 'website';
		$max_lengte = 80;

		if (isset($form[$veld])) {
			$invoer = trim(strval($form[$veld]));
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
					$this->storeDeltaProfile($veld, $invoer);
				}
				# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
				# of voor diff_to_*
				$this->_tmpprofile[$veld] = $invoer;
			}
		}
			
		# 3. forum-instellingen
		$veld = 'forum_name';
		if (isset($form[$veld])) {
			$invoer = trim(strval($form[$veld]));
			if ($invoer != 'civitas' and $invoer != 'nick') $invoer = 'civitas';
			# is het wel een wijziging?
			if ($invoer != $this->_tmpprofile[$veld]) {
				# bewaar oude en nieuwe waarde in delta
				$this->storeDeltaProfile($veld, $invoer);
				# nieuwe waarde in tmpprofile voor diff_to_*
				$this->_tmpprofile[$veld] = $invoer;
			}
		}
		
		
		# 4. telefoonvelden
		$velden = array('telefoon', 'mobiel', 'o_telefoon');
		foreach ($velden as $veld) {
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# geldige telefoonnummers...
					if (!preg_match('/^(\d{4}-\d{6}|\d{3}-\d{7}|\d{2}-\d{8}|\+\d{10-20})$/', $invoer) and $invoer != "") {
						$this->_formerror[$veld] = "Geldig formaat: 0187-123456; 015-2135681; 06-12345678; +31152135681";
					}
					
					# als er geen fout is opgetreden veranderde waarde bewaren
					if (!isset($this->_formerror[$veld])) {
						# bewaar oude en nieuwe waarde in delta
						$this->storeDeltaProfile($veld, $invoer);
					}
					# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
					# of voor diff_to_*
					$this->_tmpprofile[$veld] = $invoer;
				}
			}
		}
		
		# 5. ICQ nummer
		$veld = 'icq';
		if (isset($form[$veld])) {
			$invoer = trim(strval($form[$veld]));
			# is het wel een wijziging?
			if ($invoer != $this->_tmpprofile[$veld]) {
				if (!preg_match('/^\d{5,10}$/', $invoer) and $invoer != "") {
					$this->_formerror[$veld] = "Gebruik 5 tot 10 getallen:";
				}

				# als er geen fout is opgetreden veranderde waarde bewaren
				if (!isset($this->_formerror[$veld])) {
					# bewaar oude en nieuwe waarde in delta
					$this->storeDeltaProfile($veld, $invoer);
				}
				# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
				# of voor diff_to_*
				$this->_tmpprofile[$veld] = $invoer;
			}
		}

		# 6. Mailadressen
		$velden = array('email', 'msn');
		foreach ($velden as $veld) {
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
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
						$this->storeDeltaProfile($veld, $invoer);
					}
					# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
					# of voor diff_to_*
					$this->_tmpprofile[$veld] = $invoer;
				}
			}
		}

		# 9. Jabber ID
		$velden = array('jid');
		foreach ($velden as $veld) {
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
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
						$this->storeDeltaProfile($veld, $invoer);
					}
					# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
					# of voor diff_to_*
					$this->_tmpprofile[$veld] = $invoer;
				}
			}
		}

		# 8. password veranderen
		$velden = array('oldpass', 'nwpass', 'nwpass2');
		$pwveldenset = true;
		# controleren of velden in de invoer zitten
		foreach ($velden as $veld) if (!isset($form[$veld])) $pwveldenset = false;
		# alleen doorgaan als ze er alledrie zijn
		if ($pwveldenset === true) {
			$oldpass = strval($form['oldpass']);
			$nwpass = strval($form['nwpass']);
			$nwpass2 = strval($form['nwpass2']);
		  
			$tmperror='';
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
					$hash = $this->_makepasswd($nwpass);
					$this->storeDeltaProfile('password', $hash);
					# nieuwe waarde voor diff_to_*
					$this->_tmpprofile['password'] = $hash;
				}
			}
		}

		# Extra velden die gewijzigd kunnen worden... Oudleden kunnen meer elementaire velden wijzigen
		# als hun naam, hun studiejaar etc, om de oudledenlijst compleet te krijgen.
		# De Vice-Abactis kan de info van iedereen wijzigen.
		
		if ($this->_profile['status'] == 'S_OUDLID' or $this->hasPermission('P_LEDEN_MOD')) {
			
			# Info over naam => verplichte velden! (ook vanwege sn/cn velden in ldap!)
			$velden = array('voornaam' => 50, 'achternaam' => 50);
			# voor al deze veldnamen...
			foreach($velden as $veld => $max_lengte) {
				# kijken of ze in POST voorkomen...
				if (isset($form[$veld])) {
					$invoer = trim(strval($form[$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer == "") {
							$this->_formerror[$veld] = "Dit veld mag niet leeggelaten worden:";
						} elseif ($invoer != "" and !is_utf8($invoer)) {
							$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
						} elseif (mb_strlen($invoer) > $max_lengte) {
							$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->storeDeltaProfile($veld, $invoer);
						}
						# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
						# of voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}
		
			# Info over naam, studieomschrijving
			$velden = array('tussenvoegsel' => 15, 'studie' => 100);
			# voor al deze veldnamen...
			foreach($velden as $veld => $max_lengte) {
				# kijken of ze in POST voorkomen...
				if (isset($form[$veld])) {
					$invoer = trim(strval($form[$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and !is_utf8($invoer)) {
							$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
						} elseif (mb_strlen($invoer) > $max_lengte) {
							$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->storeDeltaProfile($veld, $invoer);
						}
						# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
						# of voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}

			# jaartallen etc...
			$velden = array ('studiejaar', 'lidjaar');
			# moet een getal tussen 1900 en 2100 zijn allemaal
			foreach($velden as $veld) {
				# kijken of ze in POST voorkomen...
				if (isset($form[$veld])) {
					$invoer = trim(strval($form[$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and $invoer != (int)$invoer) {
							$this->_formerror[$veld] = "Ongeldige karakters, typ een jaartal:";
						} elseif ($invoer < 1900 or $invoer > 2100) {
							$this->_formerror[$veld] = "Het jaartal ligt buiten toegestane grenzen:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->storeDeltaProfile($veld, $invoer);
						}
						# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
						# of voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}
			
			# geboortedatum
			$veld = 'gebdatum';
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
				# Kijk of de invoer zinvol te splitsen is in YY-mm-dd
				$matches = array();
				if (!preg_match('/^(\d{4})-(\d\d?)-(\d\d?)$/', $invoer, $matches)) {
					$this->_formerror[$veld] = "Ongeldige datumformaat, gebruik dag-maand-jaar:";
					$this->_tmpprofile['gebdatum']  = $this->_profiel['gebdatum'];
				} else {
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile['gebdatum']) {
					  # dan gaan we controleren of de nieuwe datum een bestaande
					  # datum is...
					  
					  #	door strtotime heenhalen en kijken of het dezelfde datum is
					  if ($invoer != date("Y-m-d", strtotime($invoer))) {
							$this->_formerror[$veld] = "Opgegeven datum bestaat niet:";
					  }

						# als er geen fout is opgetreden veranderde waarde bewaren
						if (!isset($this->_formerror[$veld])) {
							# bewaar oude en nieuwe waarde in delta
							$this->storeDeltaProfile($veld, $invoer);
						}
						# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
						# of voor diff_to_*
						$this->_tmpprofile['gebdatum']  = $invoer;

					}
				}
			}
		}

		# Extra velden die gewijzigd kunnen worden door am. Vice-Abactis
		if ($this->hasPermission('P_LEDEN_MOD')) {

			$velden = array('postfix' => 7, 'voornamen' => 100, 'kerk' => 50, 'muziek' => 100);
			# voor al deze veldnamen...
			foreach($velden as $veld => $max_lengte) {
				if (isset($form[$veld])) {
					$invoer = trim(strval($form[$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and !is_utf8($invoer)) {
							$this->_formerror[$veld] = "Ongeldige karakters, gebruik reguliere tekst:";
						} elseif (mb_strlen($invoer) > $max_lengte) {
							$this->_formerror[$veld] = "Gebruik maximaal {$max_lengte} karakters:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->storeDeltaProfile($veld, $invoer);
						}
						# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
						# of voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}

			# kring en moot
			$velden = array ('kring' => 10, 'moot' => 4);
			foreach($velden as $veld => $max) {
				# kijken of ze in POST voorkomen, zo niet...
				if (!isset($form[$veld])) {
					$this->_formerror[$veld] = "Whraagh! ik mis een veld in de data! --> {$veld}";
				} else {
					$invoer = trim(strval($form[$veld]));
					# is het wel een wijziging?
					if ($invoer != $this->_tmpprofile[$veld]) {
						# controleren op juiste inhoud...
						if ($invoer != "" and $invoer != (int)$invoer) {
							$this->_formerror[$veld] = "Ongeldige karakters, kies een getal:";
						} elseif ($invoer < 0 or $invoer > $max) {
							$this->_formerror[$veld] = "De invoer ligt buiten toegestane grenzen:";
						} else {
							# bewaar oude en nieuwe waarde in delta
							$this->storeDeltaProfile($veld, $invoer);
						}
						# nieuwe waarde in tmpprofile, is of voor afbeelden in het invulvak,
						# of voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}
			
			# is deze persoon kringleider? (n)iet, (e)erstejaars, (o)uderejaars
			$veld = 'kringleider';
			# kijken of het veld in POST voorkomt, zo niet...
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if (!preg_match('/^[neo]$/', $invoer)) {
						$this->_formerror[$veld] = "Gebruik (n)iet, (e)erstejaars, (o)uderejaars:";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->storeDeltaProfile($veld, $invoer);
						# nieuwe waarde in tmpprofile voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}

			# is deze persoon motebal? (0) nee, (1) ja
			$veld = 'motebal';
			# kijken of het veld in POST voorkomt, zo niet...
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if (!preg_match('/^[01]$/', $invoer)) {
						$this->_formerror[$veld] = "Gebruik (0) nee, (1) ja:";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->storeDeltaProfile($veld, $invoer);
						# nieuwe waarde in tmpprofile voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}

			# is deze persoon (m)an/(v)rouw?
			$veld = 'geslacht';
			# kijken of het veld in POST voorkomt, zo niet...
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					if (!preg_match('/^[mv]$/', $invoer)) {
						$this->_formerror[$veld] = "Gebruik (m)an, (v)rouw:";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->storeDeltaProfile($veld, $invoer);
						# nieuwe waarde in tmpprofile voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
					}
				}
			}
			
			# wat is de status van dit lid?
			$veld = 'status';
			# kijken of het veld in POST voorkomt, zo niet...
			if (isset($form[$veld])) {
				$invoer = trim(strval($form[$veld]));
				# is het wel een wijziging?
				if ($invoer != $this->_tmpprofile[$veld]) {
					# controleren op juiste inhoud...
					$aValide=array('S_LID', 'S_GASTLID', 'S_KRINGEL', 'S_NOVIET', 'S_OUDLID', 'S_NOBODY');
					if (!in_array($invoer, $aValide)) {
						$this->_formerror[$veld] = "Deze status bestaat niet.";
					} else {
						# bewaar oude en nieuwe waarde in delta
						$this->storeDeltaProfile($veld, $invoer);
						# nieuwe waarde in tmpprofile voor diff_to_*
						$this->_tmpprofile[$veld] = $invoer;
						//rechten ook uitzetten als iemand geen lid meer is.
						if($invoer=='S_NOBODY'){
							$this->storeDeltaProfile('permissies', 'P_NOBODY');
							$this->_tmpprofile['permissies'] = 'P_NOBODY';
						//of als iemand oudlid wordt:
						}elseif($invoer=='S_OUDLID'){
							$this->storeDeltaProfile('permissies', 'P_OUDLID');
							$this->_tmpprofile['permissies'] = 'P_OUDLID';
						}
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
	/*
	* Deze functie slaat velden op in de delta array 
	*/
	function storeDeltaProfile($veld, $invoer){
		$this->_delta[$veld] = array (
			'oud'  => $this->_tmpprofile[$veld],
			'nieuw'  => $invoer
		);						
	}
	function getTmpProfile() { return $this->_tmpprofile; }
	function getFormErrors() { return $this->_formerror; }

	function diff_to_sql() {
		# Zijn er wel wijzigingen?
		if (isset($this->_delta) and is_array($this->_delta) and count($this->_delta) > 0) {
			$sqldata = array();
			foreach ($this->_delta as $veld => $diff) {
				$sqldata[$veld] = $this->_db->escape($diff['nieuw']);
			}

			# opslaan van de waarden in de database
			$this->_db->update_a('lid', 'uid', $this->_tmpprofile['uid'], $sqldata);
			
			//profiel-cache weggooien
			$profiel=new Smarty_csr();
			$profiel->clear_cache('profiel.tpl', $this->_tmpprofile['uid']);
		}
	}


	# Profiel uitlezen uit de database en in LDAP zetten
	function save_ldap() {
	
		# Alleen leden, novieten en kringels staan in LDAP
		if (preg_match('/^S_(LID|NOVIET|KRINGEL)$/', $this->_tmpprofile['status'])) {

			$result = $this->_db->select("
				SELECT
					uid, voornaam, tussenvoegsel, achternaam, adres, postcode, woonplaats, telefoon,
					mobiel, email, password, website, nickname, land
				FROM
					lid
				WHERE
					uid = {$this->_tmpprofile['uid']}
			");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				$lid = $this->_db->next($result);

				# ldap entry in elkaar snokken
				$entry = array();
				$entry['uid'] = $lid['uid'];
				$entry['givenname'] = str_replace('  ', ' ',implode(' ',array($lid['voornaam'],$lid['tussenvoegsel'])));
				$entry['sn'] = $lid['achternaam'];
				$entry['cn'] = str_replace('  ', ' ',implode(' ',array($lid['voornaam'],$lid['tussenvoegsel'],$lid['achternaam'])));
				$entry['mail'] = $lid['email'];
				$entry['homephone'] = $lid['telefoon'];
				$entry['mobile'] = $lid['mobiel'];
				$entry['homepostaladdress'] = implode('$',array($lid['adres'],$lid['postcode'],$lid['woonplaats']));
				$entry['o'] = 'C.S.R. Delft';
				$entry['mozillanickname'] = $lid['nickname'];
				$entry['mozillausehtmlmail'] = 'FALSE';
				$entry['mozillahomestreet'] = $lid['adres'];
				$entry['mozillahomelocalityname'] = $lid['woonplaats'];
				$entry['mozillahomepostalcode'] = $lid['postcode'];
				$entry['mozillahomecountryname'] = $lid['land'];
				$entry['mozillahomeurl'] = $lid['website'];
				$entry['description'] = 'Ledenlijst C.S.R. Delft';
				$entry['userPassword'] = $lid['password'];

				# voor woonoord moeten we even moeilijk doen
				require_once('class.woonoord.php');
				$woonoord = new Woonoord($this->_db, $this);
				$wo = $woonoord->getWoonoordByUid($this->_tmpprofile['uid']);
				if ($wo !== false) $entry['ou'] = $wo['naam'];

				# lege velden er uit gooien
				foreach ($entry as $i => $e) if ($e == '') unset ($entry[$i]);
				
				# if ($this->hasPermission('P_LEDEN_MOD')) print_r($entry);
			
				# LDAP verbinding openen
				$ldap = new LDAP();
			
				# bestaat deze uid al in ldap? dan wijzigen, anders aanmaken
				if ($ldap->isLid($entry['uid'])) $ldap->modifyLid($entry['uid'], $entry);
				else $ldap->addLid($entry['uid'], $entry);
			
				# verbinding sluiten
				$ldap->disconnect();
			}
		# Als het een andere status is even kijken of de uid in ldap voorkomt, en zo ja wissen
		} else {
			# LDAP verbinding openen
			$ldap = new LDAP();
			
			# bestaat deze uid in ldap? dan verwijderen
			if ($ldap->isLid($this->_tmpprofile['uid'])) $ldap->removeLid($this->_tmpprofile['uid']);
			
			# verbinding sluiten
			$ldap->disconnect();
		}
	
	}

	function getPermissions() { return $this->_profile['permissies']; }
	function getStatus()      { return $this->_profile['status']; }
	function getForumInstelling(){ return array('forum_naam' => $this->_profile['forum_name']); }
	function getForumNaamInstelling(){ return $this->_profile['forum_name']; }
	
	/*
	* Deze functie maakt een link met de naam, als de gebruiker is ingelogged, anders gewoon een naam.
	* Dit om te voorkomen dat er op 100 plekken foute paden staan als dat een keer verandert.
	*/
	function getNaamLink($uid, $civitas=false, $link=false, $aNaam=false){
		$sNaam='';
		if($aNaam===false){
			if($uid == $this->_profile['uid']){
				$aNaam=$this->_profile;
			}else{
				$rNaam=$this->_db->select("SELECT voornaam, tussenvoegsel, achternaam FROM lid WHERE uid='".$uid."' LIMIT 1;");
				if($rNaam!==false and $this->_db->numRows($rNaam)==1){
					$aNaam=$this->_db->next($rNaam);
				}else{
					$aNaam=array('voornaam'=>'ho', 'achternaam'=>'ho', 'tussenvoegsel'=>'ho', 'status'=>'ho');
				}
			}
		}
		if($link AND $this->hasPermission('P_LOGGED_IN')){
			$sNaam.='<a href="/intern/profiel/'.$uid.'">';
		}
		if($civitas){
			if($aNaam['status']=='S_NOVIET'){
				$sNaam.='noviet '.mb_htmlentities($aNaam['voornaam']);
			}else{
				$sNaam.=($aNaam['geslacht']=='v') ? 'Ama. ' : 'Am. ';
				if($aNaam['tussenvoegsel'] != '') $sNaam.=$aNaam['tussenvoegsel'].' ';
				$sNaam.=mb_htmlentities($aNaam['achternaam']);				
				if($aNaam['postfix'] != '') $sNaam.=' '.$aNaam['postfix'];
			}
		}else{
			$sNaam.=mb_htmlentities(naam($aNaam['voornaam'], $aNaam['achternaam'], $aNaam['tussenvoegsel']));
		}
		if($link AND $this->hasPermission('P_LOGGED_IN')){ $sNaam.='</a>'; }
		
		return $sNaam;	
	}
	
	function getMoot() { return $this->_profile['moot']; }
	function getFullName($uid = '') {
		if ($uid == '' or $uid == $this->_profile['uid']) {
			$fullname=naam($this->_profile['voornaam'], $this->_profile['achternaam'], $this->_profile['tussenvoegsel']);
		} else {
			$result = $this->_db->select("SELECT voornaam, tussenvoegsel, achternaam FROM lid WHERE uid='".$uid."' LIMIT 1;");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				$record = $this->_db->next($result);
				$fullname = naam($record['voornaam'], $record['achternaam'], $record['tussenvoegsel']);
			} else $fullname = 'Niet bekend';
		}
		return $fullname;
	}

	function getCivitasName($uid = ''){
		$sCivitasNaam='';
		if ($uid == '' or $uid == $this->_profile['uid']) {
			if($this->_profile['status']=='S_NOVIET'){
				$sCivitasNaam='noviet '.$this->_profile['voornaam'];
			}else{
				$sCivitasNaam = ($this->_profile['geslacht']=='v') ? 'Ama. ' : 'Am. ';
				if ($this->_profile['tussenvoegsel'] != '') $sCivitasNaam.=$this->_profile['tussenvoegsel'].' ';
				$sCivitasNaam.=$this->_profile['achternaam'];
				if ($this->_profile['postfix'] != '') $sCivitasNaam.=' '.$this->_profile['postfix'];
			}
		} else {
			$result = $this->_db->select("SELECT voornaam, tussenvoegsel, achternaam, postfix, geslacht, status FROM lid WHERE uid='".$uid."' LIMIT 1;");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				$record = $this->_db->next($result);
				if($record['status']=='S_NOVIET'){
					$sCivitasNaam='noviet '.$record['voornaam'];
				}else{
					$sCivitasNaam = ($record['geslacht']=='v') ? 'Ama. ' : 'Am. ';
					if ($record['tussenvoegsel'] != '') $sCivitasNaam.=$record['tussenvoegsel'].' ';
					$sCivitasNaam.=$record['achternaam'];				
					if ($record['postfix'] != '') $sCivitasNaam.=' '.$record['postfix'];
				}
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
			'P_OUDLID'     => $p['P_LOGGED_IN'] | $p['P_LEDEN_READ'] | $p['P_OUDLEDEN_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_FORUM_READ'],
			'P_MODERATOR'  => $p['P_ADMIN'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_MOD'] | $p['P_AGENDA_MOD'] | $p['P_MAAL_MOD'] | $p['P_MAIL_SEND'] | $p['P_NEWS_MOD'] | $p['P_BIEB_MOD']
		);
		# extra dingen, waarvoor de array perm_user zelf nodig is
		$this->_perm_user['P_PUBCIE']  = $this->_perm_user['P_MODERATOR'];
		$this->_perm_user['P_MAALCIE'] = $this->_perm_user['P_LID'] | $p['P_MAAL_MOD'];
		$this->_perm_user['P_BESTUUR'] = $this->_perm_user['P_LID'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_READ'] | $p['P_NEWS_MOD'] | $p['P_MAAL_MOD'] | $p['P_AGENDA_POST'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'];
		$this->_perm_user['P_VAB']     = $this->_perm_user['P_BESTUUR']  | $p['P_OUDLEDEN_MOD'];
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
		$zoekfilter='';
		
		# mysql escape dingesen
		$zoekterm = trim($this->_db->escape($zoekterm));
		$zoekveld = trim($this->_db->escape($zoekveld));
		
		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if($zoekveld=='naam' AND !preg_match('/^\d{2}$/', $zoekterm)){
			if(preg_match('/ /', trim($zoekterm))){
				$zoekdelen=explode(' ', $zoekterm);
				$iZoekdelen=count($zoekdelen);
				if($iZoekdelen==2){
					$zoekfilter="( voornaam LIKE '%".$zoekdelen[0]."%' AND achternaam LIKE '%".$zoekdelen[1]."%' ) OR";
					$zoekfilter.="( voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR
                                        nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%' )";
				}else{
					$zoekfilter="( voornaam LIKE '%".$zoekdelen[0]."%' AND achternaam LIKE '%".$zoekdelen[$iZoekdelen-1]."%' )";
				}
			}else{
				$zoekfilter="
					voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR 
					nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%'";
			}
		}else{
			if(preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld=='uid' OR $zoekveld=='naam')){
				//zoeken op lichtingen...
				$zoekfilter="SUBSTRING(uid, 1, 2)='".$zoekterm."'";
				//echo $zoekfilter;
			}else{
				$zoekfilter="{$zoekveld} LIKE '%{$zoekterm}%'";
			}
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
			$sZoeken="
				SELECT
					uid, nickname, voornaam, tussenvoegsel, achternaam, postfix, adres, postcode, woonplaats, land, telefoon,
					mobiel, email, geslacht, voornamen, icq, msn, skype, jid, website, beroep, studie, studiejaar, lidjaar, 
					gebdatum, moot, kring, kringleider, motebal, 
					o_adres, o_postcode, o_woonplaats, o_land, o_telefoon, 
					kerk, muziek, eetwens
				FROM 
					lid 
				WHERE 
					(".$zoekfilter.")
				AND 
					($statusfilter) 
				{$mootfilter}
				ORDER BY 
					{$sort}";
			$result = $this->_db->select($sZoeken);
			if ($result !== false and $this->_db->numRows($result) > 0) {
				while ($lid = $this->_db->next($result)) $leden[] = $lid;
			}
		}

		return $leden;
	}
	
	function nickExists($nick) {
		# mysql escape dingesen
		$nick = $this->_db->escape($nick);
		
		$result = $this->_db->select("SELECT * FROM lid WHERE nickname = '{$nick}'");
        if ($result !== false and $this->_db->numRows($result) > 0)
			return true;
		return false;
	}
	
	function isValidUid($uid) {
		return preg_match('/^[a-z0-9]{4}$/', $uid) > 0;
	}


	function uidExists($uid) {
		if (!$this->isValidUid($uid)) return false;
		
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$uid}'");
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
		$result = $this->_db->select("SELECT status FROM lid WHERE uid = '{$uid}'");
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
				status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL' ) ORDER BY {$sort}");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($lid = $this->_db->next($result)) $leden[] = $lid;
		}

		return $leden;
	}

	function getVerjaardagen($maand, $dag=0) {
		$maand = (int)$maand; $dag = (int)$dag; $verjaardagen = array();
		$query="
			SELECT 
				uid, voornaam, tussenvoegsel, achternaam, geslacht, email, 
				EXTRACT( DAY FROM gebdatum) as gebdag
			FROM 
				lid 
			WHERE 
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') 
			AND 
				EXTRACT( MONTH FROM gebdatum)= '{$maand}'";
		if($dag!=0)	$query.=" AND gebdag=".$dag;
		$query.=" ORDER BY gebdag;";
		$result = $this->_db->select($query);
		
		if ($result !== false and $this->_db->numRows($result) > 0) {
			while($verjaardag=$this->_db->next($result)){
				$verjaardagen[] = $verjaardag;
			}
		}
		return $verjaardagen;
	}

	function getMaxKringen($moot=0) {
		$maxkringen = 0;
		$sMaxKringen="
			SELECT 
				MAX(kring) as max 
			FROM 
				lid 
			WHERE 
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') ";
		if($moot!=0){ $sMaxKringen.="AND moot=".$moot; }
		$sMaxKringen.="	LIMIT 1;";
		
    $result = $this->_db->select($sMaxKringen);
    if ($result !== false and $this->_db->numRows($result) > 0) {
			$max = $this->_db->next($result);
			$maxkringen = $max['max'];
			return $maxkringen;
		}else{
			return 0;
		}
	}

	function getMaxMoten() {
		$maxmoten = 0;
		$result = $this->_db->select("
			SELECT MAX(moot) as max FROM lid WHERE (status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')");
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
				uid, 
				voornaam, 
				tussenvoegsel, 
				achternaam, 
				moot, 
				kring, 
				motebal, 
				kringleider,
				email,
				status
			FROM 
				lid 
			WHERE 
				status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'
			ORDER BY 
				kringleider DESC,
				achternaam ASC;");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($lid = $this->_db->next($result)) {
				$kring[$lid['moot']][$lid['kring']][] = array(
					'uid'=> $lid['uid'],
					'naam' => naam($lid['voornaam'], $lid['achternaam'], $lid['tussenvoegsel']),
					'motebal' => $lid['motebal'],
					'kringleider' => $lid['kringleider'],
					'status'=> $lid['status'],
					'email'=> $lid['email']
				);
			}
		}

		return $kring;
	}
	# Deze functie voegt iemand aan een kring toe
	function addUid2kring($uid, $kring, $moot=0){
		//controle op invoer
		//if (!$this->isValidUid($uid)) return false;
		//$kring=(int)$kring; if($kring>10) return false;
		//$moot=(int)$moot; if($moot>4) return false;
		$sKringInvoer="
			UPDATE 
				lid
			SET
				kring=".$kring."";
		if($moot!=0) $sKringInvoer.=", moot=".$moot;
		$sKringInvoer.="			
			WHERE 
				uid='".$uid."'
			LIMIT 1;";
		return $this->_db->query($sKringInvoer);
	}
	# deze functie wordt gebruikt om extra info toe te voegen als de inschrijving voor een
	# maaltijd gesloten wordt, en de inschrijvingen naar de maaltijdgesloten tabel worden
	# overgezet: de volledige naam en eetwens
	function getNaamEetwens($uid = '') {
		if ($uid == '') $uid = $this->_profile['uid'];
		$result = $this->_db->select("
			SELECT voornaam, tussenvoegsel, achternaam, eetwens
			FROM lid
			WHERE uid='{$uid}'
		");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			$record = $this->_db->next($result);
			return array(
				'naam' => naam($record['voornaam'], $record['achternaam'], $record['tussenvoegsel']), 
				'eetwens' => $record['eetwens']);
		}
		return false;
	}
	function getEetwens(){ return $this->_profile['eetwens']; }
	function setEetwens($eetwens){
		$eetwens=trim($this->_db->escape($eetwens));
		//ff streepjes enzo eruit halen, anders komen die op de maaltijdlijst.
		if(strlen($eetwens)<3){ $eetwens=''; }
		$sEetwens="UPDATE lid SET eetwens='".$eetwens."' WHERE uid='".$this->getUid()."';";
		return $this->_db->query($sEetwens);
	}
	
	# deze functie wordt door maaltrack gebruikt om de namen van mensen en hun eetwens
	# toe te voegen aan een lijst met inschrijvingen
	# parameter: $lijst, een array waar een veld genaamd 'uid' in moet zitten
	# de functie maakt dan een veld 'naam' en 'eetwens' erbij
	/* DIT WORDT NIET MEER GEBRUIKT,
	maar gewoon in de selectiequery's van de maaltijd geregeld
	function addNames(&$lijst) {
		foreach ($lijst as $l => $foo) {
			$result = $this->_db->select("
				SELECT voornaam, tussenvoegsel, achternaam, eetwens
				FROM lid
				WHERE uid='{$foo['uid']}'
			");
			if ($result !== false and $this->_db->numRows($result) > 0) {
				$lid = $this->_db->next($result);
				$lijst[$l]['naam'] = naam($lid['voornaam'], $lid['achternaam'], $lid['tussenvoegsel']);
				$lijst[$l]['eetwens'] = $lid['eetwens'];
			} else {
				$lijst[$l]['naam'] = $l['uid']."/onbekend";			
				$lijst[$l]['eetwens'] = "";
			}
		}
	}*/
	
	function getSaldi($uid='', $alleenRood=false){
		if($uid==''){ $uid=$this->getUid(); }
		$query="
			SELECT
				saldo as soccie,
				maalSaldo as maalcie
			FROM
				socciesaldi
			WHERE
				uid='".$uid."'
			LIMIT 1;";
		$rSaldo=$this->_db->query($query);
		if($this->_db->numRows($rSaldo)){
			$aSaldo=$this->_db->next($rSaldo);
			if($alleenRood){
				$return=false;
				if($aSaldo['soccie']<0){
					$return[]=array('naam' => 'SocCie', 'saldo' => sprintf("%01.2f",$aSaldo['soccie']));
				}
				if($aSaldo['maalcie']<0){
					$return[]=array('naam' => 'MaalCie', 'saldo' => sprintf("%01.2f",$aSaldo['maalcie']));
				}
				return $return;
			}else{
				return $aSaldo;
			}
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
