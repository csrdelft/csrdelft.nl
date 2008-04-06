<?php
class VBSourceOpinion extends VBItem
{
	var $sid;
	var $lid;
	var $rating;
	var $createdate;
	var $comment;
	
	
	public static function fromSQLResult($r)
	{
		$source = new VBSourceOpinion();
		$source->sid = $r['sid'];
		$source->lid = $r['lid'];
		$source->comment = $r['comment'];
		$source->rating = $r['rating'];
		$source->createdate = $r['createdate'];
		return $source;
	}
	
	public static function fromSQLResults($ar)
	{
		return VBItem::fromSQLResults($ar, VBSourceOpinion);
	}
	
}
?>