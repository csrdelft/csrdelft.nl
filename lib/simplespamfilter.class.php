<?php

/**
 * SimpleSpamFilter.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 * Simpele spamfilter.
 * 
 */
class SimpleSpamFilter {

	private $spamregex;
	private $string;
	private $score = 0;

	public function __construct($string) {
		$this->string = $string;
		$this->spamregex = "/s-e-x|zoofilia|sexyongpin|grusskarte|geburtstagskarten|animalsex|" .
				"sex-with|dogsex|adultchat|adultlive|camsexlivesex|viagra|" .
				"chatsex|onlinesex|adultporn|adultvideo|adultweb.|hardcoresex|hardcoreporn|" .
				"teenporn|xxxporn|lesbiansex|live(girl|nude|sex|video)|camgirl|" .
				"spycam|voyeursex|casino-online|online-casino|kontaktlinsen|cheapest-phone|" .
				"laser-eye|eye-laser|fuelcellmarket|lasikclinic|cragrats|parishilton|" .
				"paris-hilton|paris-tape|2large|fuel-(ing)?dispenser|huojia|" .
				"jinxinghj|telematicsone|telematiksone|a-mortgage|diamondabrasives|" .
				"reuterbrook|sex(-with|-plugin|-zone|cam|chat)|lazy-stars|eblja|liuhecai|" .
				"buy-viagra|-cialis|-levitra|boy-and-girl-kissing|squirting|\[link=|<a href=/i";

		// score gaat niet met meer dan 1 omhoog omdat preg_match na de eerste match stopt met zoeken.
		$this->score += preg_match($this->spamregex, $this->string);
		if ($this->hasOnlyLinks($this->string)) {
			$this->score++;
		}
	}

	public function isSpam() {
		return $this->score > 0;
	}

	static function hasOnlyLinks($str) {
		// strip out all URLs from the comment
		$str = preg_replace("'https*://(\S*)'", "", $str);
		$str = preg_replace("'<a ([^<]*?)</a>'", "", $str);
		$str = preg_replace("'\[url= ([^<]*?)\[/url\]'", "", $str);
		// trim out any whitespace
		$str = trim($str);
		return empty($str);
	}

}
