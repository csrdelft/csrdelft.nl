<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace CsrDelft\common;

// C.S.R. Delft | pubcie@csrdelft.nl
// met dank aan Jeugdkerken NL
// -------------------------------------------------------------------
// LDAP.php
// -------------------------------------------------------------------
// Beheert LDAP-toegang
// N.B. Let op dat functies in deze klasse verantwoordelijk zijn voor
// de data die LDAP in gaat. Maak dus op de juiste manier gebruik
// van de ldap_escape_(dn|attribute) functies!
// -------------------------------------------------------------------
use LDAP\Connection;
use CsrDelft\common\Util\TextUtil;

class LDAP
{
	//## private ###
	/** @var Connection|resource|bool */
	private $conn = false;
	private $baseLeden;
	private $baseGroepen;
	private $basePeople;
	private $baseAntiplesk;
	private $baseMailbox;

	public function __construct($dobind = true)
	{
		// bepaal of we alleen verbinding maken, of ook meteen inloggen.
		// standaard is dit gewenst, in het geval dat deze klasse gebruikt
		// wordt om met een ldap bind gebruikersinfo te controleren niet.
		$this->connect($dobind);
	}

	// Openen van de LDAP connectie, die we regelmatig nodig hebben...

	public function connect($dobind)
	{
		// zijn we al ingelogd?
		if ($this->conn !== false) {
			$this->disconnect();
		}

		if (!$_ENV['LDAP_HOST']) {
			throw new CsrException('LDAP not available');
		}
		$conn = ldap_connect($_ENV['LDAP_HOST'], (int) $_ENV['LDAP_PORT']);
		ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		if ($_ENV['LDAP_TLS'] == 'true') {
			ldap_start_tls($conn);
		}
		if ($dobind === true) {
			$bind = ldap_bind($conn, $_ENV['LDAP_BINDDN'], $_ENV['LDAP_PASSWD']);
			if (!$bind) {
				return false;
			}
		}
		// Onthouden van wat instellingen
		$this->conn = $conn;
		$this->baseLeden = $_ENV['LDAP_BASE_LEDEN'];
		$this->baseGroepen = $_ENV['LDAP_BASE_GROEPEN'];

		return true;
	}

	// verbinding sluiten, maar alleen als er een geldige resource is

	public function disconnect()
	{
		@ldap_close($this->conn);
		$this->conn = false;
	}

	// functie voor LDAPAuthMech (class.authmech.php) om gebruikersinlog te verifieren

	public function checkBindPass($mech, $user, $pass)
	{
		$validbase = [
			'people' => $this->basePeople,
			'antiplesk' => $this->baseAntiplesk,
			'mailbox' => $this->baseMailbox,
		];
		if (!array_key_exists($mech, $validbase)) {
			return false;
		}

		// sanitaire controle
		if (!TextUtil::is_utf8($user) || !TextUtil::is_utf8($pass)) {
			return false;
		}

		// als er geen bindingsangst is gaan we proberen met de ldap te binden...
		return @ldap_bind(
			$this->conn,
			sprintf('uid=%s,%s', $this->ldap_escape_dn($user), $validbase[$mech]),
			$pass
		);
	}

	//### Ledenlijst ####
	// controleert of een gebruiker met de betreffende 'uid' voorkomt

	public function isLid($uid)
	{
		$base = $this->baseLeden;
		$filter = sprintf('(uid=%s)', $this->ldap_escape_filter($uid));
		$result = ldap_search($this->conn, $base, $filter);
		$num = ldap_count_entries($this->conn, $result);
		return $num != 0 && $num !== false;
	}

	// een, of alle records opvragen

	public function getLid($uid = '')
	{
		$base = $this->baseLeden;
		$filter =
			$uid == ''
				? '(uid=*)'
				: sprintf('(uid=%s)', $this->ldap_escape_filter($uid));
		$result = ldap_search($this->conn, $base, $filter);
		return ldap_get_entries($this->conn, $result);
	}

	// Voeg een nieuw record toe
	// N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	// http://nl2.php.net/manual/en/function.ldap-add.php

	public function addLid($uid, $entry)
	{
		$base = $this->baseLeden;
		$dn = 'uid=' . $this->ldap_escape_dn($uid) . ', ' . $base;

		// objectClass definities
		unset($entry['objectClass']);
		$entry['objectClass'][] = 'top';
		$entry['objectClass'][] = 'person';
		$entry['objectClass'][] = 'organizationalPerson';
		$entry['objectClass'][] = 'inetOrgPerson';
		$entry['objectClass'][] = 'mozillaAbPersonObsolete';

		return ldap_add($this->conn, $dn, $entry);
	}

	// Wijzig de informatie van een lid
	// N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	// http://nl2.php.net/manual/en/function.ldap-add.php

	public function modifyLid($uid, $entry)
	{
		$base = $this->baseLeden;
		$dn = 'uid=' . $this->ldap_escape_dn($uid) . ', ' . $base;
		return ldap_modify($this->conn, $dn, $entry);
	}

	public function removeLid($uid)
	{
		$base = $this->baseLeden;
		$dn = 'uid=' . $this->ldap_escape_dn($uid) . ', ' . $base;
		return ldap_delete($this->conn, $dn);
	}

	//### Groepen ####
	// controleert of een groep met de betreffende 'cn' voorkomt

	public function isGroep($cn)
	{
		$base = $this->baseGroepen;
		$filter = sprintf('(cn=%s)', $this->ldap_escape_filter($cn));
		$result = ldap_search($this->conn, $base, $filter);
		$num = ldap_count_entries($this->conn, $result);
		return $num != 0 && $num !== false;
	}

	// een, of alle records opvragen

	public function getGroep($cn = '')
	{
		$base = $this->baseGroepen;
		$filter =
			$cn == '' ? '(cn=*)' : sprintf('(cn=%s)', $this->ldap_escape_filter($cn));
		$result = ldap_search($this->conn, $base, $filter);
		return ldap_get_entries($this->conn, $result);
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
	 * [cn] => kortenaamcommissie
	 * [member] => Array (
	 * [0] => uid=0431,ou=leden,dc=csrdelft,dc=nl
	 * )
	 * [objectClass] => Array (
	 * [0] => top
	 * [1] => groupOfNames
	 * )
	 * )
	 */
	public function addGroep($cn, $entry)
	{
		$base = $this->baseGroepen;
		$dn = 'cn=' . $this->ldap_escape_dn($cn) . ', ' . $base;

		// objectClass definities
		unset($entry['objectClass']);
		$entry['objectClass'][] = 'top';
		$entry['objectClass'][] = 'groupOfNames';

		return ldap_add($this->conn, $dn, $entry);
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
	public function modifyGroep($cn, $entry)
	{
		$base = $this->baseGroepen;
		$dn = 'cn=' . $this->ldap_escape_dn($cn) . ', ' . $base;
		return ldap_modify($this->conn, $dn, $entry);
	}

	/**
	 * verwijder de hele groep uit ldap
	 *
	 * @param string $cn kortegroepnaam
	 * @return bool gelukt/mislukt
	 */
	public function removeGroep($cn)
	{
		$base = $this->baseGroepen;
		$dn = 'cn=' . $this->ldap_escape_dn($cn) . ', ' . $base;
		return ldap_delete($this->conn, $dn);
	}

	//### Escapen van LDAP-invoer ####
	// RFC2253

	private function ldap_escape_dn($text)
	{
		// DN escaping rules
		// A DN may contain special characters which require escaping. These characters are:
		// , (comma), = (equals), + (plus), < (less than), > (greater than), ; (semicolon),
		// \ (backslash), and "" (quotation marks).
		$text = preg_replace("/([,=+<>;\"\\\])/", '\\\\$1', (string) $text);

		// In addition, the # (number sign) requires
		// escaping if it is the first character in an attribute value, and a space character
		// requires escaping if it is the first or last character in an attribute value.
		$text = preg_replace('/^#/', '\\#', (string) $text);
		return preg_replace('/^ /', '\\ ', (string) $text);
	}

	// RFC2254
	// If a value should contain any of the following characters
	//
	//   Character       ASCII value
	//   ---------------------------
	//   *               0x2a
	//   (               0x28
	//   )               0x29
	//   \               0x5c
	//   NUL             0x00
	//
	// the character must be encoded as the backslash '\' character (ASCII
	// 0x5c) followed by the two hexadecimal digits representing the ASCII
	// value of the encoded character. The case of the two hexadecimal
	// digits is not significant.

	private function ldap_escape_filter($text)
	{
		// ascii control characters er uit gooien, die zijn niet nodig in deze applicatie
		$text = preg_replace('/[\x00-\x1F\x7F]/', '', (string) $text);
		// zie opmerking hierboven, \ staat voorop!
		$search = ['\\', '*', '(', ')', "\0"];
		$replace = ["\\5C", "\\2A", "\\28", "\\29", "\\00"];
		return str_replace($search, $replace, $text);
	}
}
