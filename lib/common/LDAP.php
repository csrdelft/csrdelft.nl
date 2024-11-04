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

use CsrDelft\common\Util\TextUtil;

class LDAP
{
	//## private ###

	/** @var \LDAP\Connection|resource|bool */
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

	public function connect($dobind): bool
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
			if ($bind !== true) {
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

	public function disconnect(): void
	{
		@ldap_close($this->conn);
		$this->conn = false;
	}

	// functie voor LDAPAuthMech (class.authmech.php) om gebruikersinlog te verifieren

	//### Ledenlijst ####
	// controleert of een gebruiker met de betreffende 'uid' voorkomt

	// een, of alle records opvragen

	public function getLid($uid = ''): array|false
	{
		$base = $this->baseLeden;
		if ($uid == '') {
			$filter = '(uid=*)';
		} else {
			$filter = sprintf('(uid=%s)', $this->ldap_escape_filter($uid));
		}
		$result = ldap_search($this->conn, $base, $filter);
		return ldap_get_entries($this->conn, $result);
	}

	// Voeg een nieuw record toe
	// N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	// http://nl2.php.net/manual/en/function.ldap-add.php

	// Wijzig de informatie van een lid
	// N.B. $entry is een array die al in het juiste formaat moet zijn opgemaakt
	// http://nl2.php.net/manual/en/function.ldap-add.php

	//### Groepen ####
	// controleert of een groep met de betreffende 'cn' voorkomt

	// een, of alle records opvragen

	//### Escapen van LDAP-invoer ####
	// RFC2253

	/**
	 * @return null|string|string[]
	 *
	 * @psalm-return array<string>|null|string
	 */
	private function ldap_escape_dn(string $text): array|string|null
	{
		// DN escaping rules
		// A DN may contain special characters which require escaping. These characters are:
		// , (comma), = (equals), + (plus), < (less than), > (greater than), ; (semicolon),
		// \ (backslash), and "" (quotation marks).
		$text = preg_replace("/([,=+<>;\"\\\])/", '\\\\$1', $text);

		// In addition, the # (number sign) requires
		// escaping if it is the first character in an attribute value, and a space character
		// requires escaping if it is the first or last character in an attribute value.
		$text = preg_replace('/^#/', '\\#', $text);
		return preg_replace('/^ /', '\\ ', $text);
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

	/**
	 * @return string|string[]
	 *
	 * @psalm-return array<string>|string
	 */
	private function ldap_escape_filter($text): array|string
	{
		// ascii control characters er uit gooien, die zijn niet nodig in deze applicatie
		$text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
		// zie opmerking hierboven, \ staat voorop!
		$search = ['\\', '*', '(', ')', "\0"];
		$replace = ["\\5C", "\\2A", "\\28", "\\29", "\\00"];
		return str_replace($search, $replace, $text);
	}
}
