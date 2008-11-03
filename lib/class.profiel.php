<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once ('class.ldap.php');

class Profiel extends Lid{
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
	
	function Profiel(){ 
		parent::Lid(); 
	}
	

	### public ###
	# naast het profiel van de huidige gebruiker is er een variabele _tmpprofile,
	# die gebruikt wordt voor het inladen van een profiel wat op de profiel-pagina
	# getoond wordt, en waar wijzigingen in gemaakt worden.

	# profiel inladen
	function loadSqlTmpProfile($uid) {
		# kijken of uid een goed formaat heeft
		if(!$this->isValidUid($uid)){ return false; }
		
		# en gebruiker opzoeken
		$uid = $this->_db->escape($uid);
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '".$uid."' LIMIT 1");
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
				# 2e check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
				# omdat this->nickExists in mysql case-insensitive zoek
				} elseif ($invoer != "" and strtolower($this->_tmpprofile[$veld]) != strtolower($form[$veld])
						and $this->nickExists($invoer)) {
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
					if (!preg_match('/^(\d{4}-\d{6}|\d{3}-\d{7}|\d{2}-\d{8}|\+\d{10,20})$/', $invoer) and $invoer != "") {
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
		
		# studienummer
		$veld = 'studienr';
		if (isset($form[$veld])) {
			$invoer = trim(strval($form[$veld]));
			# is het wel een wijziging?
			if ($invoer != $this->_tmpprofile[$veld]) {
				# controleren op juiste inhoud...
				if (!preg_match('/^[0-9]{6,7}$/', $invoer, $matches)) {
					$this->_formerror[$veld] = "Geen geldig studienummer opgegeven:";
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
						// Maaltijdabos verwijderen indien de status veranderd is in Geen Lid of Oudlid.
						if($invoer=='S_NOBODY'||$invoer=='S_OUDLID'){
							if(!$this->deleteMaaltijdresten($this->_tmpprofile['uid'])){
								$this->_formerror[$veld] = "Maaltijdresten verwijderen mislukt! Statuswijziging niet doorgevoerd.";
							}
						}
						//rechten ook uitzetten als iemand geen lid meer is.
						if($invoer=='S_NOBODY'){
							$this->storeDeltaProfile('permissies', 'P_NOBODY');
							$this->_tmpprofile['permissies'] = 'P_NOBODY';
						//of als iemand oudlid wordt:
						}elseif($invoer=='S_OUDLID'){
							$this->storeDeltaProfile('permissies', 'P_OUDLID');
							$this->_tmpprofile['permissies'] = 'P_OUDLID';
						//als iemand weer lid wordt.
						}elseif($invoer=='S_LID' AND $this->_tmpprofile[$veld]!='S_LID'){
							$this->storeDeltaProfile('permissies', 'P_LID');
							$this->_tmpprofile['permissies'] = 'P_LID';
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
			
			//naam-cache leegkekken voor dit lid
			$lc=LidCache::get_LidCache();
			$lc->flushLid($this->_tmpprofile['uid']);
		}
	}


	# Profiel uitlezen uit de database en in LDAP zetten
	function save_ldap() {
	
		# Alleen leden, gastleden, novieten en kringels staan in LDAP ( en Knorrie öO~ )
		if (preg_match('/^S_(LID|GASTLID|NOVIET|KRINGEL)$/', $this->_tmpprofile['status'])
		    or $this->_tmpprofile['uid'] == '9808') {
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
				/*
				 TODO: Dit doen met de nieuwe groepenketzer.
				 require_once('class.woonoord.php');
				$woonoord = new Woonoord($this->_db, $this);
				$wo = $woonoord->getWoonoordByUid($this->_tmpprofile['uid']);
				if ($wo !== false) $entry['ou'] = $wo['naam'];
				*/
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
	function resetWachtwoord($uid){
		if(!$this->uidExists($uid)){ return false; }
		$password=substr(md5(time()), 0, 8);
		$passwordhash=$this->_makepasswd($password);
		
		$sNieuwWachtwoord="
			UPDATE
				lid
			SET
				password='".$passwordhash."'
			WHERE
				uid='".$uid."'
			LIMIT 1;";
		$mailto=$this->getEmail($uid);
		//mail maken
		$mail="
Hallo ".$this->getFullName($uid).",

U heeft een nieuw wachtwoord aangevraagd voor http://csrdelft.nl. U kunt nu inloggen met de volgende combinatie:

".$uid."
".$password."

U kunt uw wachtwoord wijzigen in uw profiel: http://csrdelft.nl/communicatie/profiel/".$uid." .

Met vriendelijke groet,

Namens de PubCie,

".$this->getNaamLink($this->getUid(), 'full', false, false, false)."

P.S.: Mocht u nog vragen hebben, dan kan u natuurlijk altijd e-posts sturen naar pubcie@csrdelft.nl";
		return $this->_db->query($sNieuwWachtwoord) AND mail($mailto, 'Nieuw wachtwoord voor de C.S.R.-stek', $mail, "Bcc: pubcie@csrdelft.nl");

	}

	// Verwijdert alle maaltijdabos en alle toekomstige (ongesloten) maaltijdaanmeldingen voor het opgegeven lid.
	function deleteMaaltijdresten($uid){
		if(!$this->uidExists($uid)){ return false; }
		$sDeleteAbosQuery="
			DELETE FROM
				maaltijdabo
			WHERE
				uid='".$uid."';";
		$rDeleteAbos=$this->_db->query($sDeleteAbosQuery);

		$sDeleteAanmeldingenQuery="
			DELETE
				a
			FROM
				maaltijdaanmelding a
			LEFT JOIN
				maaltijd m
			ON
				m.id = a.maalid
			WHERE
				a.uid='".$uid."'
			AND
				m.gesloten='0'
			AND
				m.datum > UNIX_TIMESTAMP();";
		$rDeleteAanmeldingen=$this->_db->query($sDeleteAanmeldingenQuery);

		if($rDeleteAbos===false||$rDeleteAanmeldingen===false)
			return false;
		else
			return true;
	}
}
?>
