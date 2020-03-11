<?php
require_once __DIR__.'/../../vendor/autoload.php';

use CsrDelft\entity\profiel\log\ProfielLogCoveeTakenVerwijderChange;
use CsrDelft\entity\profiel\log\ProfielLogTextEntry;
use CsrDelft\entity\profiel\log\ProfielUpdateLogGroup;
use CsrDelft\entity\profiel\log\UnparsedProfielLogGroup;
use Ferno\Loco\ConcParser;
use Ferno\Loco\Grammar;
use Ferno\Loco\GreedyMultiParser;
use Ferno\Loco\LazyAltParser;
use Ferno\Loco\RegexParser;
use Ferno\Loco\StringParser;
use Ferno\Loco\Utf8Parser;
use Phinx\Migration\AbstractMigration;

class ConvertProfielLog extends AbstractMigration {
	public function change() {
		$parser = new ProfielLogParser();
		$serializer = new \Zumba\JsonSerializer\JsonSerializer();
		$dbAdapter = $this->getAdapter();
		$pdo = $dbAdapter->getConnection();
		$results = $this->query('SELECT uid, changelog from profielen')->fetchAll();
		foreach ($results as $row) {
			$parsed = $parser->parse($row['changelog']);
			$statement = $pdo->prepare('UPDATE profielen set changelog=:parsed where uid=:uid');
			$statement->bindValue(':parsed', $serializer->serialize($parsed));
			$statement->bindValue(':uid', $row['uid']);
			$statement->execute();
		}
	}
}


/**
 * I know this code is ugly, but it will only be used once for the conversion of the logs.
 * Class ProfielLogParser
 * @package CsrDelft
 */
class ProfielLogParser {
	private $grammar;

	public function __construct() {
		try {
			$this->grammar = new Grammar(
				"LOG",
				array(
					"LOG" => new ConcParser([new GreedyMultiParser("LOGITEM", 0, null), "STRING"], function ($items, $str = "") {
						if ($str !== "") {
							$items[] = new UnparsedProfielLogGroup($str);
						}
						return array_reverse(filter_null($items));
					}),
					"LOGITEM" => new LazyAltParser([
						"LOGITEM_CHANGES",
						"LOGITEM_CREATED",
						"LOGITEM_CREATED_STATUS",
						"LOGITEM_STATUS",
						new StringParser("[div][/div][hr]", function () {
							return null;
						}),
						new ConcParser([new StringParser("[div]"), new GreedyMultiParser(
							"AFMELDEN_ABO",
							0,
							1
						), new GreedyMultiParser(
							"CORVEE_CHANGES",
							0,
							1
						), new StringParser("[/div][hr]")], function ($xignore, $abo, $corvee) {
							return new ProfielUpdateLogGroup(null, null, filter_null([$abo, $corvee]));
						}),
					]),
					"LOGITEM_STATUS" => new ConcParser([
						new StringParser("[div]Statusverandering van "),
						"LID",
						new StringParser(" op "),
						"DATE",
						new StringParser("[br]"),
						"CHANGES",
						new StringParser("[/div][hr]")],
						function ($xignore, $lid, $xxignore, $date, $xxxignore, $changes) {
							return new ProfielUpdateLogGroup($lid, $date, filter_null($changes));
						}
					),
					"LOGITEM_CHANGES" => new ConcParser(
						[
							new RegexParser("/^(\[div\])?Bewerking van /"),
							"LID",
							new StringParser(" op "),
							"DATE",
							new StringParser("[br]"),
							"CHANGES",
							new RegexParser("/^(\[\/div\])?\[hr\]/")
						],
						function ($xignore, $lid, $xx, $date, $xxx, $changes) {
							return new ProfielUpdateLogGroup($lid, $date, filter_null($changes));
						}
					),
					"CHANGES" => new ConcParser([new GreedyMultiParser(
						"AFMELDEN_ABO",
						0,
						1
					), new GreedyMultiParser(
						"CORVEE_CHANGES",
						0,
						1
					),
						new GreedyMultiParser(
							"CHANGE",
							0,
							null
						), new GreedyMultiParser(
							"CORVEE_CHANGES",
							0,
							1
						), new GreedyMultiParser(
							"AFMELDEN_ABO",
							0,
							1
						)], function () {
						return array_merge(...func_get_args());
					}
					),
					'CORVEE_CHANGES' => new ConcParser([
						new RegexParser("/^Verwijderde corveetaken:?\[br\]/"),
						new GreedyMultiParser(
							new ConcParser([new RegexParser("/^(ma|di|wo|do|vr)/"), "SIMPLESTRING", new StringParser("[br]")],
								function ($day, $rest) {
									return $day . $rest;
								}),
							0,
							null
						)
					], function ($xignore, $taken) {
						return new ProfielLogCoveeTakenVerwijderChange($taken);
					}),
					'AFMELDEN_ABO' => new ConcParser([
						new RegexParser("/^Afmelden abo's: (.* uitgezet\. ?)?/"),
						new StringParser("[br]")
					], function ($text) {
						return new ProfielLogTextEntry($text);
					}),
					"LOGITEM_CREATED_STATUS" => new ConcParser([
						new RegexParser("/^(\[div\])?Aangemaakt als (.*) door /"),
						"LID",
						new StringParser(" op "),
						"DATE",
						"STRING"
					], function ($xignore, $lid, $xxignore, $date, $xxxignore) {
						return new \CsrDelft\entity\profiel\log\ProfielCreateLogGroup($lid, $date);
					}),
					"LOGITEM_CREATED" => new ConcParser([
						new RegexParser("/^Aangemaakt door /"),
						"LID",
						new StringParser(" op "),
						"DATE",
						new StringParser("[br]")
					], function ($xignore, $lid, $xxignore, $date, $xxxignore) {
						return new \CsrDelft\entity\profiel\log\ProfielCreateLogGroup($lid, $date);
					}),
					"CHANGE" => new RegexParser("/^\(([^\(\)]*)\) ([^\[\]]*) => ([^\[\]]*)\[br\]/", function ($all, $prop, $old, $new) {
						return new \CsrDelft\entity\profiel\log\ProfielLogValueChange($prop, $old, $new);
					}),

					"LID" => new ConcParser([new StringParser("[lid="),
						"SIMPLESTRING",
						new StringParser("]")],
						function ($xignore, $uid, $xxignore) {
							return $uid;
						}),

					"DATE" => new ConcParser([new StringParser("[reldate]"),
						"SIMPLESTRING",
						new StringParser("[/reldate]")],
						function ($xignore, $date, $xxingore) {
							$date = date_create_from_format('Y-m-d H:i:s', $date);
							return $date == false ? null : $date;

						}),

					"SIMPLESTRING" => new RegexParser("/^[^\[\]]+/"),
					"PROPERTY" => new GreedyMultiParser(new Utf8Parser(array("[", "]", "(", ")")), 0, null),
					"VALUE" => new RegexParser("/^(?! => )*/"),
					"STRING" => new RegexParser("/^[\s\S]*/"),
					"ISTRING" => new RegexParser("/^[^\[div\]]*/")
				)
			);
		} catch (\Ferno\Loco\GrammarException $e) {

		}

	}

	function parse($text) {
		if ($text === "") {
			return [];
		}
		try {
			return $this->grammar->parse($text);
		} catch (\Ferno\Loco\ParseFailureException $e) {
			return null;
		}
	}
}

function filter_null($array) {
	return array_filter($array, function ($elem) {
		return $elem !== null;
	});
}
