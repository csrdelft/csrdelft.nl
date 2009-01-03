<?php
class VBSubjectSource extends VBItem
{
	var $subjid;
	var $sourceid;
	var $reason;
	var $createdate;
	var $lid;
	var $sourceobj; //cache linked source
	var $subjobj; //chache linked subject
	var $subjname; //chached subjectname

	//field that should not be saved, inserted or edited automatically
	static $excludes = array("sourceobj","subjobj","subjname");

	function __construct()
	{
		$this->lid = Lid::instance()->getUid();
		$this->createdate = getDateTime();
		$this->subjid = -1;
		$this->sourceid = -1;
	}

	public static function fromSQLResult($r)
	{
		$source = new VBSubjectSource();
		$source->subjid = $r['subjid'];
		$source->sourceid = $r['sourceid'];
		$source->reason = $r['reason'];
		$source->createdate = $r['createdate'];
		$source->lid = $r['lid'];
		if (isset($r['subjname']))		//yes, we can use result from join now
			$source->subjname = $r['subjname'];
		return $source;
	}

	public static function fromSQLResults($ar)
	{
		return VBItem::fromSQLResults($ar, VBSubjectSource);
	}

	public function getInsertQuery()
	{
		return VBItem::createInsertQuery($this,self::$excludes,array());
	}

	public function getDeleteQuery()
	{
		return "DELETE FROM vb_subjectsource WHERE subjid = '".$this->subjid."' AND sourceid = '".$this->sourceid."'";
	}

	public function getUpdateQuery()
	{
		return VBItem::createUpdateQuery($this,self::$excludes,array())." WHERE subjid = '".$this->subjid."' AND sourceid = '".$this->sourceid."'";
	}

	public function getJSEditHandler()
	{
		return VBItem::createJSEditHandler($this,array("lid","createdate","sourceobj","subjobj","subjname"));
	}

	public function getJSRemoveHandler()
	{
		return VBItem::createJSRemoveHandler($this,"sourceid=".$this->sourceid."&subjid=".$this->subjid);
	}


		/**
	only allows editing of the reason, another source cannot be specified, in that case, use adding a relation
	*/
	public static function getEditDiv()
	{
		$innerhtml = VBItem::generateHiddenFields(array("subjid"=>"-1","sourceid"=>"-1"));
		$innerhtml.="
			Reden voor deze relatie:<br/>
			<textarea name='reason' rows='6' cols='80'></textarea><br/>
		";
		return VBItem::getEditDiv("<img src='images/leaf.png'/>Onderwerp-bron relatie bewerken",$innerhtml, 'vbsubjectsource');
	}
}
?>