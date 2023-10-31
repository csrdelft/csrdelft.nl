<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\Lib\Bb\BbEnv;
use CsrDelft\Lib\Bb\BbTag;
use CsrDelft\Lib\Bb\Parser;
use CsrDelft\Lib\Bb\Tag\BbBold;
use CsrDelft\Lib\Bb\Tag\BbClear;
use CsrDelft\Lib\Bb\Tag\BbCode;
use CsrDelft\Lib\Bb\Tag\BbCommentaar;
use CsrDelft\Lib\Bb\Tag\BbDiv;
use CsrDelft\Lib\Bb\Tag\BbEmail;
use CsrDelft\Lib\Bb\Tag\BbHeading;
use CsrDelft\Lib\Bb\Tag\BbHorizontalRule;
use CsrDelft\Lib\Bb\Tag\BbItalic;
use CsrDelft\Lib\Bb\Tag\BbLeet;
use CsrDelft\Lib\Bb\Tag\BbLishort;
use CsrDelft\Lib\Bb\Tag\BbList;
use CsrDelft\Lib\Bb\Tag\BbListItem;
use CsrDelft\Lib\Bb\Tag\BbMe;
use CsrDelft\Lib\Bb\Tag\BbNewline;
use CsrDelft\Lib\Bb\Tag\BbNobold;
use CsrDelft\Lib\Bb\Tag\BbQuote;
use CsrDelft\Lib\Bb\Tag\BbStrikethrough;
use CsrDelft\Lib\Bb\Tag\BbSubscript;
use CsrDelft\Lib\Bb\Tag\BbSuperscript;
use CsrDelft\Lib\Bb\Tag\BbTable;
use CsrDelft\Lib\Bb\Tag\BbTableCell;
use CsrDelft\Lib\Bb\Tag\BbTableHeader;
use CsrDelft\Lib\Bb\Tag\BbTableRow;
use CsrDelft\Lib\Bb\Tag\BbUnderline;
use CsrDelft\common\ContainerFacade;
use CsrDelft\view\bbcode\tag\BbAftel;
use CsrDelft\view\bbcode\tag\BbBijbel;
use CsrDelft\view\bbcode\tag\BbBoek;
use CsrDelft\view\bbcode\tag\BbCitaat;
use CsrDelft\view\bbcode\tag\BbAanmelder;
use CsrDelft\view\bbcode\tag\BbCodeInline;
use CsrDelft\view\bbcode\tag\BbDocument;
use CsrDelft\view\bbcode\tag\BbForum;
use CsrDelft\view\bbcode\tag\BbForumPlaatje;
use CsrDelft\view\bbcode\tag\BbFoto;
use CsrDelft\view\bbcode\tag\BbFotoalbum;
use CsrDelft\view\bbcode\tag\BbImg;
use CsrDelft\view\bbcode\tag\BbInstelling;
use CsrDelft\view\bbcode\tag\BbIsHetAl;
use CsrDelft\view\bbcode\tag\BbLedenmemoryscores;
use CsrDelft\view\bbcode\tag\BbLid;
use CsrDelft\view\bbcode\tag\BbMaaltijd;
use CsrDelft\view\bbcode\tag\BbNeuzen;
use CsrDelft\view\bbcode\tag\BbNovietVanDeDag;
use CsrDelft\view\bbcode\tag\BbOfftopic;
use CsrDelft\view\bbcode\tag\BbOrderedList;
use CsrDelft\view\bbcode\tag\BbParagraph;
use CsrDelft\view\bbcode\tag\BbPeiling;
use CsrDelft\view\bbcode\tag\BbPrive;
use CsrDelft\view\bbcode\tag\BbQuery;
use CsrDelft\view\bbcode\tag\BbBb;
use CsrDelft\view\bbcode\tag\BbReldate;
use CsrDelft\view\bbcode\tag\BbTaal;
use CsrDelft\view\bbcode\tag\BbUbboff;
use CsrDelft\view\bbcode\tag\BbUrl;
use CsrDelft\view\bbcode\tag\BbVerklapper;
use CsrDelft\view\bbcode\tag\embed\BbAudio;
use CsrDelft\view\bbcode\tag\embed\BbLocatie;
use CsrDelft\view\bbcode\tag\embed\BbSpotify;
use CsrDelft\view\bbcode\tag\embed\BbTwitter;
use CsrDelft\view\bbcode\tag\embed\BbVideo;
use CsrDelft\view\bbcode\tag\embed\BbYoutube;
use CsrDelft\view\bbcode\tag\groep\BbActiviteit;
use CsrDelft\view\bbcode\tag\groep\BbBestuur;
use CsrDelft\view\bbcode\tag\groep\BbCommissie;
use CsrDelft\view\bbcode\tag\groep\BbGroep;
use CsrDelft\view\bbcode\tag\groep\BbKetzer;
use CsrDelft\view\bbcode\tag\groep\BbOndervereniging;
use CsrDelft\view\bbcode\tag\groep\BbVerticale;
use CsrDelft\view\bbcode\tag\groep\BbWerkgroep;
use CsrDelft\view\bbcode\tag\groep\BbWoonoord;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use function substr_count;

/**
 * CsrBB.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class CsrBB extends Parser
{
	public function getTags()
	{
		return [
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
			BbBb::class,
			BbActiviteit::class,
			BbAudio::class,
			BbBestuur::class,
			BbBijbel::class,
			BbBoek::class,
			BbCitaat::class,
			BbCodeInline::class,
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
			BbOrderedList::class,
			BbParagraph::class,
			BbPeiling::class,
			BbPrive::class,
			BbQuery::class,
			BbReldate::class,
			BbSpotify::class,
			BbTaal::class,
			BbTwitter::class,
			BbUbboff::class,
			BbUrl::class,
			BbVerklapper::class,
			BbVerticale::class,
			BbVideo::class,
			BbWerkgroep::class,
			BbWoonoord::class,
			BbYoutube::class,
			BbNovietVanDeDag::class,
			BbAanmelder::class,
			BbAftel::class,
		];
	}
	/**
	 * @var ContainerInterface (bevat alleen CsrDelft\view\bbcode\tags)
	 */
	private $container;

	public function __construct(ServiceLocator $container)
	{
		parent::__construct();

		$this->container = $container;
	}

	public function parse($bbcode)
	{
		$this->allow_html = false;
		$this->standard_html = false;
		return $this->getHtml($bbcode);
	}

	public function parseHtml($bbcode, $inline = false)
	{
		$this->allow_html = true;
		$this->standard_html = $inline;
		return $this->getHtml($bbcode);
	}

	public static function parseMail($bbcode)
	{
		$env = new BbEnv();
		$env->mode = 'light';
		$parser = ContainerFacade::getContainer()->get(CsrBB::class);
		$parser->setEnv($env);
		return $parser->getHtml($bbcode);
	}

	public static function parseLight($bbcode)
	{
		$env = new BbEnv();
		$env->mode = 'light';
		$parser = ContainerFacade::getContainer()->get(CsrBB::class);
		$parser->setEnv($env);
		return $parser->getHtml($bbcode);
	}

	public static function parsePreview($bbcode)
	{
		$env = new BbEnv();
		$env->mode = 'preview';
		$parser = ContainerFacade::getContainer()->get(CsrBB::class);
		$parser->setEnv($env);
		return $parser->getHtml($bbcode);
	}

	public function parsePlain($bbcode)
	{
		$env = new BbEnv();
		$env->mode = 'plain';
		$this->setEnv($env);
		return $this->getHtml($bbcode);
	}

	/**
	 * Bij citeren mogen er geen ongesloten tags zijn om problemen te voorkomen.
	 * Werkt niet bij [ubboff] / [tekst].
	 *
	 * @param string $bbcode
	 * @return string
	 */
	public static function sluitTags($bbcode)
	{
		$aantalOngesloten =
			substr_count($bbcode, '[') -
			substr_count($bbcode, '[*]') -
			2 * substr_count($bbcode, '[/');
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
	public static function escapeUbbOff($bbcode)
	{
		return str_replace(['[/ubboff]', '[/tekst]'], ['[/]', '[/]'], $bbcode);
	}

	/**
	 * Omdat we niet willen dat dingen die in privé staan alsnog gezien kunnen worden
	 * bij het citeren, slopen we hier alles wat in privé-tags staat weg.
	 * @param string $bbcode
	 * @return string
	 */
	public static function filterPrive($bbcode)
	{
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
	public static function filterCommentaar($bbcode)
	{
		// .* is greedy by default, dat wil zeggen, matched zoveel mogelijk.
		// door er .*? van te maken matched het zo weinig mogelijk, dat is precies
		// wat we hier willen, omdat anders [commentaar]foo[/commentaar]bar[commentaar]foo[/commentaar]
		// niets zou opleveren.
		// de /s modifier zorgt ervoor dat een . ook alle newlines matched.
		return preg_replace(
			'/\[commentaar=?.*?\].*?\[\/commentaar\]/s',
			'',
			$bbcode
		);
	}

	protected function createTagInstance(string $tag, Parser $parser, $env): BbTag
	{
		if ($this->container->has($tag)) {
			$tag = $this->container->get($tag);
		} else {
			$tag = new $tag();
		}
		$tag->setParser($parser);
		$tag->setEnv($env);

		return $tag;
	}
}
