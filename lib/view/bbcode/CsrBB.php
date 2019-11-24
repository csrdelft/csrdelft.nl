<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\bb\BbEnv;
use CsrDelft\bb\Parser;
use CsrDelft\bb\tag\BbBold;
use CsrDelft\bb\tag\BbClear;
use CsrDelft\bb\tag\BbCode;
use CsrDelft\bb\tag\BbCommentaar;
use CsrDelft\bb\tag\BbDiv;
use CsrDelft\bb\tag\BbEmail;
use CsrDelft\bb\tag\BbHeading;
use CsrDelft\bb\tag\BbHorizontalRule;
use CsrDelft\bb\tag\BbItalic;
use CsrDelft\bb\tag\BbLeet;
use CsrDelft\bb\tag\BbLishort;
use CsrDelft\bb\tag\BbList;
use CsrDelft\bb\tag\BbListItem;
use CsrDelft\bb\tag\BbMe;
use CsrDelft\bb\tag\BbNewline;
use CsrDelft\bb\tag\BbNobold;
use CsrDelft\bb\tag\BbQuote;
use CsrDelft\bb\tag\BbStrikethrough;
use CsrDelft\bb\tag\BbSubscript;
use CsrDelft\bb\tag\BbSuperscript;
use CsrDelft\bb\tag\BbTable;
use CsrDelft\bb\tag\BbTableCell;
use CsrDelft\bb\tag\BbTableHeader;
use CsrDelft\bb\tag\BbTableRow;
use CsrDelft\bb\tag\BbUnderline;
use CsrDelft\view\bbcode\tag\BbActiviteit;
use CsrDelft\view\bbcode\tag\BbBestuur;
use CsrDelft\view\bbcode\tag\BbBijbel;
use CsrDelft\view\bbcode\tag\BbBoek;
use CsrDelft\view\bbcode\tag\BbCitaat;
use CsrDelft\view\bbcode\tag\BbCommissie;
use CsrDelft\view\bbcode\tag\BbDocument;
use CsrDelft\view\bbcode\tag\BbForumPlaatje;
use CsrDelft\view\bbcode\tag\BbForum;
use CsrDelft\view\bbcode\tag\BbFoto;
use CsrDelft\view\bbcode\tag\BbFotoalbum;
use CsrDelft\view\bbcode\tag\BbGroep;
use CsrDelft\view\bbcode\tag\BbImg;
use CsrDelft\view\bbcode\tag\BbInstelling;
use CsrDelft\view\bbcode\tag\BbIsHetAl;
use CsrDelft\view\bbcode\tag\BbKetzer;
use CsrDelft\view\bbcode\tag\BbLedenmemoryscores;
use CsrDelft\view\bbcode\tag\BbLid;
use CsrDelft\view\bbcode\tag\BbLocatie;
use CsrDelft\view\bbcode\tag\BbMaaltijd;
use CsrDelft\view\bbcode\tag\BbNeuzen;
use CsrDelft\view\bbcode\tag\BbOfftopic;
use CsrDelft\view\bbcode\tag\BbOndervereniging;
use CsrDelft\view\bbcode\tag\BbPeiling;
use CsrDelft\view\bbcode\tag\BbPrive;
use CsrDelft\view\bbcode\tag\BbQuery;
use CsrDelft\view\bbcode\tag\BbReldate;
use CsrDelft\view\bbcode\tag\BbSpotify;
use CsrDelft\view\bbcode\tag\BbTwitter;
use CsrDelft\view\bbcode\tag\BbUbboff;
use CsrDelft\view\bbcode\tag\BbUrl;
use CsrDelft\view\bbcode\tag\BbVerklapper;
use CsrDelft\view\bbcode\tag\BbVerticale;
use CsrDelft\view\bbcode\tag\BbVideo;
use CsrDelft\view\bbcode\tag\BbWerkgroep;
use CsrDelft\view\bbcode\tag\BbWoonoord;
use CsrDelft\view\bbcode\tag\BbYoutube;
use function substr_count;

/**
 * CsrBB.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class CsrBB extends Parser {

	protected $tags = [
		// Standard
		BbBold::class,
		BbClear::class,
		BbCode::class,
		BbCommentaar::class,
		BbDiv::class,
		BbEmail::class,
		BbHeading::class,
		BbHorizontalRule::class,
		BbItalic::class,
		BbLeet::class,
		BbLishort::class,
		BbList::class,
		BbListItem::class,
		BbMe::class,
		BbNewline::class,
		BbNobold::class,
		BbQuote::class,
		BbStrikethrough::class,
		BbSubscript::class,
		BbSuperscript::class,
		BbTable::class,
		BbTableCell::class,
		BbTableHeader::class,
		BbTableRow::class,
		BbUnderline::class,
		// Custom
		BbActiviteit::class,
		BbBestuur::class,
		BbBijbel::class,
		BbBoek::class,
		BbCitaat::class,
		BbCommissie::class,
		BbDocument::class,
		BbForum::class,
		BbForumPlaatje::class,
		BbFoto::class,
		BbFotoalbum::class,
		BbGroep::class,
		BbImg::class,
		BbInstelling::class,
		BbIsHetAl::class,
		BbKetzer::class,
		BbLedenmemoryscores::class,
		BbLid::class,
		BbLocatie::class,
		BbMaaltijd::class,
		BbNeuzen::class,
		BbOfftopic::class,
		BbOndervereniging::class,
		BbPeiling::class,
		BbPrive::class,
		BbQuery::class,
		BbReldate::class,
		BbSpotify::class,
		BbTwitter::class,
		BbUbboff::class,
		BbUrl::class,
		BbVerklapper::class,
		BbVerticale::class,
		BbVideo::class,
		BbWerkgroep::class,
		BbWoonoord::class,
		BbYoutube::class,
	];


	public static function parse($bbcode) {
		$parser = new CsrBB();
		return $parser->getHtml($bbcode);
	}

	public static function parseHtml($bbcode, $inline = false) {
		$parser = new CsrBB();
		$parser->allow_html = true;
		$parser->standard_html = $inline;
		return $parser->getHtml($bbcode);
	}

	public static function parseMail($bbcode, $light = false) {
		$env = new BbEnv();
		$env->light_mode = $light;
		$env->email_mode = true;
		$parser = new CsrBB($env);
		return $parser->getHtml($bbcode);
	}

	public static function parseLight($bbcode) {
		$env = new BbEnv();
		$env->light_mode = true;
		$parser = new CsrBB($env);
		return $parser->getHtml($bbcode);
	}

	/**
	 * Bij citeren mogen er geen ongesloten tags zijn om problemen te voorkomen.
	 * Werkt niet bij [ubboff] / [tekst].
	 *
	 * @param string $bbcode
	 * @return string
	 */
	public static function sluitTags($bbcode) {
		$aantalOngesloten = substr_count($bbcode, '[') - substr_count($bbcode, '[*]') - 2 * substr_count($bbcode, '[/');
		for ($i = 0; $i < $aantalOngesloten; $i++) {
			$bbcode .= '[/]';
		}
		return $bbcode;
	}

	/**
	 * Soms willen we voorkomen dat de gebruiker een ubboff tag gebruikt, zoals in forum reden.
	 * @param string $bbcode
	 * @return string
	 */
	public static function escapeUbbOff($bbcode) {
		return str_replace(['[/ubboff]', '[/tekst]'], ['[/]', '[/]'], $bbcode);
	}

	/**
	 * Omdat we niet willen dat dingen die in privé staan alsnog gezien kunnen worden
	 * bij het citeren, slopen we hier alles wat in privé-tags staat weg.
	 * @param string $bbcode
	 * @return string
	 */
	public static function filterPrive($bbcode) {
		// .* is greedy by default, dat wil zeggen, matched zoveel mogelijk.
		// door er .*? van te maken matched het zo weinig mogelijk, dat is precies
		// wat we hier willen, omdat anders [prive]foo[/prive]bar[prive]foo[/prive]
		// niets zou opleveren.
		// de /s modifier zorgt ervoor dat een . ook alle newlines matched.
		return preg_replace('/\[prive=?.*?\].*?\[\/prive\]/s', '', $bbcode);
	}

	/**
	 * Omdat we niet willen dat dingen die in commentaar staan alsnog gezien kunnen worden
	 * bij het citeren, slopen we hier alles wat in commentaar-tags staat weg.
	 * @param string $bbcode
	 * @return string
	 */
	public static function filterCommentaar($bbcode) {
		// .* is greedy by default, dat wil zeggen, matched zoveel mogelijk.
		// door er .*? van te maken matched het zo weinig mogelijk, dat is precies
		// wat we hier willen, omdat anders [commentaar]foo[/commentaar]bar[commentaar]foo[/commentaar]
		// niets zou opleveren.
		// de /s modifier zorgt ervoor dat een . ook alle newlines matched.
		return preg_replace('/\[commentaar=?.*?\].*?\[\/commentaar\]/s', '', $bbcode);
	}
}
