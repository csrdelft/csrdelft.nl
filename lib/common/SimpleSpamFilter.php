<?php

namespace CsrDelft\common;

/**
 * SimpleSpamFilter.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 * Simple spamfilter.
 *
 */
class SimpleSpamFilter
{
	private $spamregex;

	public function __construct()
	{
		$this->spamregex =
			'/s-e-x|zoofilia|sexyongpin|grusskarte|geburtstagskarten|animalsex|' .
			'sex-with|dogsex|adultchat|adultlive|camsexlivesex|viagra|' .
			'chatsex|onlinesex|adultporn|adultvideo|adultweb.|hardcoresex|hardcoreporn|' .
			'teenporn|xxxporn|lesbiansex|live(girl|nude|sex|video)|camgirl|' .
			'spycam|voyeursex|casino-online|online-casino|kontaktlinsen|cheapest-phone|' .
			'laser-eye|eye-laser|fuelcellmarket|lasikclinic|cragrats|parishilton|' .
			'paris-hilton|paris-tape|2large|fuel-(ing)?dispenser|huojia|' .
			'jinxinghj|telematicsone|telematiksone|a-mortgage|diamondabrasives|' .
			'reuterbrook|sex(-with|-plugin|-zone|cam|chat)|lazy-stars|eblja|liuhecai|' .
			'buy-viagra|-cialis|-levitra|boy-and-girl-kissing|squirting|\[link=|<a href=/i';
	}

	public function isSpam($string)
	{
		$score = 0;
		// score gaat niet met meer dan 1 omhoog omdat preg_match na de eerste match stopt met zoeken.
		$score += preg_match($this->spamregex, (string) $string);
		if ($this->hasOnlyLinks($string)) {
			$score++;
		}
		return $score > 0;
	}

	private function hasOnlyLinks($str)
	{
		// strip out all URLs from the comment
		$str = preg_replace("'https*://(\S*)'", '', $str);
		$str = preg_replace("'<a ([^<]*?)</a>'", '', $str);
		$str = preg_replace("'\[url= ([^<]*?)\[/url\]'", '', $str);
		// trim out any whitespace
		$str = trim($str);
		return empty($str);
	}
}
