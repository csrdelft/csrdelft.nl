<?php

namespace CsrDelft;

use CsrDelft\model\entity\profiel\ProfielLogCoveeTakenVerwijderChange;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use CsrDelft\model\entity\profiel\ProfielUpdateLogGroup;
use CsrDelft\model\entity\profiel\UnparsedProfielLogGroup;
use Ferno\Loco\Conc;
use Ferno\Loco\Grammar;
use Ferno\Loco\GreedyMultiParser;
use Ferno\Loco\LazyAltParser;
use Ferno\Loco\RegexParser;
use Ferno\Loco\Utf8Parser;
use Ferno\Loco\StringParser;
use Ferno\Loco\ConcParser;


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
					"LOG" => new ConcParser([new GreedyMultiParser("LOGITEM", 0, null), "STRING"], function ($items, $str="") {
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
						new StringParser("[div][/div][hr]",function(){return null;}),
						new ConcParser([new StringParser("[div]"), new GreedyMultiParser(
							"AFMELDEN_ABO",
							0,
							1
						), new GreedyMultiParser(
							"CORVEE_CHANGES",
							0,
							1
						), new StringParser("[/div][hr]")], function($x, $abo, $corvee) {
							return new ProfielUpdateLogGroup(null, null, filter_null([$abo, $corvee]));
						}),
					//	//,
					]),
					//	"DIV" => new ConcParser([new StringParser("[div]"), "DIV_CONTENT", new StringParser("[/div][hr]")], function ($x, $content, $xx) {
					//		return new UnparsedProfielLogEntry($content);
					//	}),
					//	"DIV_CONTENT" => new GreedyMultiParser(new LazyAltParser([new StringParser("[hr]"), "LID", "DATE", new StringParser("[br]"), "SIMPLESTRING"]), 1, null),
					"LOGITEM_STATUS" => new ConcParser([
						new StringParser("[div]Statusverandering van "),
						"LID",
						new StringParser(" op "),
						"DATE",
						new StringParser("[br]"),
						"CHANGES",
						new StringParser("[/div][hr]")],
						function ($x, $lid, $xx, $date, $xxx, $changes) {
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
						function ($x, $lid, $xx, $date, $xxx, $changes) {
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
								function ($day, $rest, $br) {
									return $day . $rest;
								}),
								0,
								null
							)
					], function ($x, $taken) {
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
						//new RegexParser("/^(\[/div\]\[hr\]|\[br\])/")
					], function ($x, $lid, $xx, $date, $xxx) {
						return new \CsrDelft\model\entity\profiel\ProfielCreateLogGroup($lid, $date);
					}),
					"LOGITEM_CREATED" => new ConcParser([
						new RegexParser("/^Aangemaakt door /"),
						"LID",
						new StringParser(" op "),
						"DATE",
						new StringParser("[br]")
					], function ($x, $lid, $xx, $date, $xxx) {
						return new \CsrDelft\model\entity\profiel\ProfielCreateLogGroup($lid, $date);
					}),
					"CHANGE" => new RegexParser("/^\(([^\(\)]*)\) ([^\[\]]*) => ([^\[\]]*)\[br\]/", function ($all, $prop, $old, $new) {
						return new \CsrDelft\model\entity\profiel\ProfielLogValueChange($prop, $old, $new);
					}),

					"LID" => new ConcParser([new StringParser("[lid="),
						"SIMPLESTRING",
						new StringParser("]")],
						function ($x, $uid, $xx) {
							return $uid;
						}),

					"DATE" => new ConcParser([new StringParser("[reldate]"),
						"SIMPLESTRING",
						new StringParser("[/reldate]")],
						function ($x, $date, $xx) {
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
	return array_filter($array, function($a) {
		return $a !== null;
	});
}