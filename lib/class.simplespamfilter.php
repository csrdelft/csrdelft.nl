<?php
/*
 * class.simplespamfilter.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Simple spamfilter
 */


class SimpleSpamFilter{
	private $spamregex;

	private $string;
	private $score=0;

	public function __construct($string){
		$this->string=$string;
		$this->spamregex=
			"/s-e-x|zoofilia|sexyongpin|grusskarte|geburtstagskarten|animalsex|".
			"sex-with|dogsex|adultchat|adultlive|camsexlivesex|".
			"chatsex|onlinesex|adultporn|adultvideo|adultweb.|hardcoresex|hardcoreporn|".
			"teenporn|xxxporn|lesbiansex|live(girl|nude|sex|video)|camgirl|".
			"spycam|voyeursex|casino-online|online-casino|kontaktlinsen|cheapest-phone|".
			"laser-eye|eye-laser|fuelcellmarket|lasikclinic|cragrats|parishilton|".
			"paris-hilton|paris-tape|2large|fuel-(ing)?dispenser|huojia|".
			"jinxinghj|telematicsone|telematiksone|a-mortgage|diamondabrasives|".
			"reuterbrook|sex(-with|-plugin|-zone|cam|chat)|lazy-stars|eblja|liuhecai|".
			"buy-viagra|-cialis|-levitra|boy-and-girl-kissing|squirting/i";

		//Score gaat niet met meer dan 1 omghoog omdat preg_match na de eerste match stopt met zoeken.
		$this->score+=preg_match($this->spamregex, $this->string );
	}

	public function isSpam(){
		return $this->score>0;
	}


}
?>
