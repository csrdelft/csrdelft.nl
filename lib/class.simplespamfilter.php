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
			"buy-viagra|-cialis|-levitra|boy-and-girl-kissing|squirting|\[link=|<a href=/i";

		//Score gaat niet met meer dan 1 omghoog omdat preg_match na de eerste match stopt met zoeken.
		$this->score+=preg_match($this->spamregex, $this->string );
		if($this->hasOnlyLinks($this->string)){
			$this->score++;
		}
	}

	public function isSpam(){
		return $this->score>0;
	}
	static function hasOnlyLinks($comment) {
		// strip out all URLs from the comment
		$comment = preg_replace("'https*://(\S*)'", "", $comment);
		$comment = preg_replace("'<a ([^<]*?)</a>'", "", $comment);
		$comment = preg_replace("'\[url= ([^<]*?)\[/url\]'", "", $comment);
		// trim out any whitespace
		$comment = trim($comment);
		return empty($comment);
	}


}
?>
