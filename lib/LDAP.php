<?php
namespace CsrDelft;

# C.S.R. Delft | pubcie@csrdelft.nl
# met dank aan Jeugdkerken NL
# -------------------------------------------------------------------
# LDAP.php
# -------------------------------------------------------------------
# Beheert LDAP-toegang
# N.B. Let op dat functies in deze klasse verantwoordelijk zijn voor
# de data die LDAP in gaat. Maak dus op de juiste manier gebruik
# van de ldap_escape_(dn|attribute) functies!
# -------------------------------------------------------------------

use Exception;

class LDAP {
	### private ###

	/** @var resource|bool  */
	var $_conn = false;
	var $_base_leden;
	var $_base_groepen;
	private $_base_people;
	private $_base_antiplesk;
	private $_base_mailbox;

	public function __construct($dobind = true) {
		# bepaal of we alleen verbinding maken, of ook meteen inloggen.
		# standaard is dit gewenst, in het geval dat deze klasse gebruikt
		# wordt om met een ldap bind gebruikersinfo te controleren niet.
		$this->connect($dobind);
	}

	# Openen van de LDAP connectie, die we regelmatig nodig hebben...

	function connect($dobind) {
		# zijn we al ingelogd?
		if ($this->_conn !== false)
			$this->disconnect();

		if (!file_exists(ETC_PATH . "ldap.ini")) {
			throw new Exception('LDAP not available');
		}
		$ldapini = parse_ini_file(ETC_PATH . "ldap.ini");
		$conn = ldap_connect($ldapini['ldap_host'], $ldapini['ldap_port']);
		ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_start_tls($conn);
		if ($dobind === true) {
			$bind = ldap_bind($conn, $ldapini['ldap_binddn'], $ldapini['ldap_passwd']);
			if ($bind !== true)
				return false;
		}
		# Onthouden van wat instellingen
		$this->_conn = $conn;
		$this->_base_leden = $ldapini['ldap_base_leden'];
		$this->_base_groepen = $ldapini['ldap_base_groepen'];

		return true;
	}

	# verbinding sluiten, maar alleen als er een geldige resource is

	function disconnect() {
		@ldap_close($this->_conn);
		$this->_conn = false;
	}

	# functie voor LDAPAuthMech (class.authmech.php) om gebruikersinlog te verifieren

	function checkBindPass($mech, $user, $pass) {
		$validbase = array(
			'people'	 => $this->_base_people,
			'antiplesk'	 => $this->_base_antiplesk,
			'mailbox'	 => $this->_base_mailbox
		);
		if (!array_key_exists($mech, $validbase))
			return false;

		# sanitaire controle
		if (!is_utf8($user))
			return false;
		if (!is_utf8($pass))
			return false;

		# als er geen bindingsangst is gaan we proberen met de ldap te binden...
		if (@ldap_bind($this->_conn, sprintf("uid=%s,%s", $this->ldap_escape_dn($user), $validbase[$mech]), $pass))
			return true;
		return false;
	}

	#### Ledenlijst ####
	# controleert of een gebruiker met de betreffende 'uid' voorkomt

	function isLid($uid) {
		$base = $this->_base_leden;
		$filter = sprintf("(uid=%s)", $this->ldap_escape_filter($uid));
		$result = ldap_search($this->_conn, $base, $filter);
		$num = ldap_count_entries($this->_conn, $result);
		if ($num == 0 or $num === false)
			return false;
		return true;
	}

	# een, of alle records opvragen

	function getLid($uid = '') {
		$base = $this->_base_leden;
		if ($uid == '')
			$filter = "(uid=*)";
		else
			$filter = sprintf("(uid=%s)", $this->ldap_escape_filter($uid));
		$result = ldap_search($this->_conn, $base, $filter);
		$leden = ldap_get_entries($this->_conn, $result);
		return $leden;
	}

	# Voeg een nieuw record toe
	# N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	# http://nl2.php.net/manual/en/function.ldap-add.php

	function addLid($uid, $entry) {
		$base = $this->_base_leden;
		$dn = 'uid=' . $this->ldap_escape_dn($uid) . ', ' . $base;

		# objectClass definities
		unset($entry['objectClass']);
		$entry['objectClass'][] = 'top';
		$entry['objectClass'][] = 'person';
		$entry['objectClass'][] = 'organizationalPerson';
		$entry['objectClass'][] = 'inetOrgPerson';
		$entry['objectClass'][] = 'mozillaAbPersonObsolete';

		return ldap_add($this->_conn, $dn, $entry);
	}

	# Wijzig de informatie van een lid
	# N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	# http://nl2.php.net/manual/en/function.ldap-add.php

	function modifyLid($uid, $entry) {
		$base = $this->_base_leden;
		$dn = 'uid=' . $this->ldap_escape_dn($uid) . ', ' . $base;
		return ldap_modify($this->_conn, $dn, $entry);
	}

	function removeLid($uid) {
		$base = $this->_base_leden;
		$dn = 'uid=' . $this->ldap_escape_dn($uid) . ', ' . $base;
		return ldap_delete($this->_conn, $dn);
	}

	#### Groepen ####
	# controleert of een groep met de betreffende 'cn' voorkomt

	function isGroep($cn) {
		$base = $this->_base_groepen;
		$filter = sprintf("(cn=%s)", $this->ldap_escape_filter($cn));
		$result = ldap_search($this->_conn, $base, $filter);
		$num = ldap_count_entries($this->_conn, $result);
		if ($num == 0 or $num === false)
			return false;
		return true;
	}

	# een, of alle records opvragen

	function getGroep($cn = '') {
		$base = $this->_base_groepen;
		if ($cn == '')
			$filter = "(cn=*)";
		else
			$filter = sprintf("(cn=%s)", $this->ldap_escape_filter($cn));
		$result = ldap_search($this->_conn, $base, $filter);
		$groepen = ldap_get_entries($this->_conn, $result);
		return $groepen;
	}

	/**
	 * Voeg een nieuw record toe
	 * N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	 * http://nl2.php.net/manual/en/function.ldap-add.php
	 *
	 * @param string $cn kortegroepnaam
	 * @param array $entry onderstaande array zonder [objectClass]
	 * @return bool gelukt/mislukt
	 *
	 * $entry zoals die door ldap_add() wordt toegevoegd:
	 * $entry = Array (
	  [cn] => kortenaamcommissie
	  [member] => Array (
	  [0] => uid=0431,ou=leden,dc=csrdelft,dc=nl
	  )
	  [objectClass] => Array (
	  [0] => top
	  [1] => groupOfNames
	  )
	  )
	 */
	function addGroep($cn, $entry) {
		$base = $this->_base_groepen;
		$dn = 'cn=' . $this->ldap_escape_dn($cn) . ', ' . $base;

		# objectClass definities
		unset($entry['objectClass']);
		$entry['objectClass'][] = 'top';
		$entry['objectClass'][] = 'groupOfNames';

		return ldap_add($this->_conn, $dn, $entry);
	}

	/**
	 * Wijzig de informatie van een groep
	 * N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	 * http://nl2.php.net/manual/en/function.ldap-add.php
	 *
	 * @param string $cn kortegroepnaam
	 * @param array $entry array zoals in addGroep maar zonder [objectClass]
	 * @return bool gelukt/mislukt
	 *
	 * ldap_modify overschrijft de members-array in ldap met nieuwe array.
	 */
	function modifyGroep($cn, $entry) {
		$base = $this->_base_groepen;
		$dn = 'cn=' . $this->ldap_escape_dn($cn) . ', ' . $base;
		return ldap_modify($this->_conn, $dn, $entry);
	}

	/**
	 * verwijder de hele groep uit ldap
	 *
	 * @param $cn string kortegroepnaam
	 * @return bool gelukt/mislukt
	 */
	function removeGroep($cn) {
		$base = $this->_base_groepen;
		$dn = 'cn=' . $this->ldap_escape_dn($cn) . ', ' . $base;
		return ldap_delete($this->_conn, $dn);
	}

	#### Escapen van LDAP-invoer ####
	# RFC2253

	function ldap_escape_dn($text) {
		# DN escaping rules
		# A DN may contain special characters which require escaping. These characters are:
		# , (comma), = (equals), + (plus), < (less than), > (greater than), ; (semicolon),
		# \ (backslash), and "" (quotation marks).
		$text = preg_replace('/([,=+<>;"\x5C])/', '\\\\$1', $text);

		# In addition, the # (number sign) requires
		# escaping if it is the first character in an attribute value, and a space character
		# requires escaping if it is the first or last character in an attribute value.
		$text = preg_replace("/^#/", "\\#", $text);
		$text = preg_replace("/^ /", "\\ ", $text);

		return $text;
	}

	# RFC2254
	# If a value should contain any of the following characters
	#
	#   Character       ASCII value
	#   ---------------------------
	#   *               0x2a
	#   (               0x28
	#   )               0x29
	#   \               0x5c
	#   NUL             0x00
	#
	# the character must be encoded as the backslash '\' character (ASCII
	# 0x5c) followed by the two hexadecimal digits representing the ASCII
	# value of the encoded character. The case of the two hexadecimal
	# digits is not significant.

	function ldap_escape_filter($text) {
		# ascii control characters er uit gooien, die zijn niet nodig in deze applicatie
		$text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
		# zie opmerking hierboven, \ staat voorop!
		$search = array("\\", "*", "(", ")", "\0");
		$replace = array("\\5C", "\\2A", "\\28", "\\29", "\\00");
		return str_replace($search, $replace, $text);
	}

}
