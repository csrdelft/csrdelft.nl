<?php
class VBSourceSource extends VBItem
{
	var $source1;
	var $source2;
	var $lid;
	var $date;
	var $reason;
	var $status;
	var $insource1; //we are in source1, so link to source 2, otherwise reversed
	var $source1obj = null; //place to store cached source object
	var $source2obj = null;
	var $referToObj = null; //cached object that is reffered to, compared to to from parameter in cacheTo function
	static $excludes = array("insource1","source1obj","source2obj","referToObj");
	
	public static function fromSQLResult($r)
	{
		$t = new VBSourceSource();
		$t->source1 = $r['source1'];
		$t->source2 = $r['source2'];
		$t->lid = $r['lid'];
		$t->date = $r['date'];
		$t->reason = $r['reason'];
		$t->status = $r['status'];
		return $t;
	}
	
	public static function fromSQLResults($r)
	{
		return VBItem::fromSQLResults($r, VBSourceSource);
	}
	
	public function getInsertQuery()
	{
		return VBItem::createInsertQuery($this,self::$excludes,array());
	}
	
	public function getUpdateQuery()
	{
		return VBItem::createUpdateQuery($this,self::$excludes,array())." WHERE (source1 = '".$this->source1."' AND source2 = '".$this->source2."') OR (source2 = '".$this->source1."' AND source1 = '".$this->source2."') LIMIT 1";
	}

	public function getDeleteQuery()
	{
		return "DELETE FROM vb_sourcesource WHERE (source1 = '".$this->source1."' AND source2 = '".$this->source2."') OR (source2 = '".$this->source1."' AND source1 = '".$this->source2."') LIMIT 1";
	}
	
	public function getJSEditHandler()
	{
		return VBItem::createJSEditHandler($this,array("insource1","source1obj","source2obj","referToObj","lid","date","status"));		
	}
	
	public function getJSRemoveHandler()
	{
		return VBItem::createJSRemoveHandler($this,"source1=".$this->source1."&source2=".$this->source2);
	}
	
	
	public function setSourceObjects($obj1, $obj2)
	{
		$this->source1obj = $obj1;
		$this->source2obj = $obj2;
	}
	
	private function cacheSource1($vb)
	{
		$this->source1obj = $vb->getUncachedSourceById($this->source1);
	}
	
	private function cacheSource2($vb)
	{
		$this->source2obj = $vb->getUncachedSourceById($this->source2);
	}
	
	public function cacheTo($from,$vb)
	{
		if ($this->source1 == $from)
		{
			$this->cacheSource2($vb);
			$this->referToObj = $this->source2obj;
		}
		else if ($this->source2 == $from)
		{
			$this->cacheSource1($vb);
			$this->referToObj = $this->source1obj;
		}
		else die("Invalid source->source link, cacheTo could not be executed");
	}
	
	/**
	only allows editing of the reason, another source cannot be specified, in that case, use adding a relation
	*/
	public static function getEditDiv()
	{
		$innerhtml = VBItem::generateHiddenFields(array("source1"=>"-1","source2"=>"-1"));
		$innerhtml.="		
			Reden voor deze relatie:<br/>
			<textarea name='reason' rows='6' cols='80'></textarea><br/>
		";
		return VBItem::getEditDiv("<img src='images/book.png'/>Bron-bron relatie bewerken",$innerhtml, 'vbsourcesource');
	}
}
?>