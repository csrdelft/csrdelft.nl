<?php
class VBSubject extends VBItem
{
	var $id;
	var $lid;
	var $parent;
	var $name;
	var $description;
	var $isLeaf;
	var $status;
	var $ip;
	var $createdate;
	var $children = array();
	var $sources = array();
	var $discussions = array();
	var $parentobj;
	//field that should not be saved, inserted or edited automatically
	static $excludes = array("children","sources","id","discussions","parentobj");
	
	function __construct()
	{
		$this->lid = Lid::get_lid()->getUid();
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->createdate = getDateTime();
		$this->isLeaf = 1;
		$this->status = 'open';
		$this->id = -1;
	}
	
	public function getInsertQuery()
	{
		return VBItem::createInsertQuery($this,self::$excludes,array());
	}
	
	public function getUpdateQuery()
	{
		return VBItem::createUpdateQuery($this,self::$excludes,array())." WHERE id = ".$this->id;
	}
	
	public function getJSEditHandler()
	{
		//these fields cannot be edited by users, so dont generate javascript for them,
		//however we need 'id'and 'parent'
		return VBItem::createJSEditHandler($this, 
			array("children","sources","discussions","lid","isLeaf","status","ip","createdate",  "parentobj"));
	}
	
	
	
	public static function fromSQLResult($r)
	{
		$t = new VBSubject();
		VBItem::objectFromQueryResult($t, $r);
		return $t;
	}
	

	public static function fromSQLResults($r)
	{
		return VBItem::fromSQLResults($r, VBSubject);
	}
	
	public static function getEditDiv()
	{
		$innerhtml = VBItem::generateHiddenFields(array("id"=>"-1","parent"=>"-1"));
		$innerhtml.="		
			Naam van het nieuwe onderwerp:<br/>
			<input type='text' width='200' name='name'/><br/>
			Omschrijving:<br/>
			<textarea name='description' rows='6' cols='80'></textarea><br/>
		";
		return VBItem::getEditDiv($innerhtml, 'vbsubject','subjectdiv');
	}
	
	public function getSearchForm()
	{
		return "Criterium: <input type='text' width='200' name='searchvalue'/><br/>";
		
	}
	
	public function getSearchParamsFromForm($formname)
	{
		return '\"searchvalue\"=>\""+escape(document.getElementById("'.$formname.'").searchvalue.value)+"\""'; 
	}
	
	public function getSearchQuery($param)
	{
		$searchvalue = mysql_escape_string(urldecode($param['searchvalue']));
		return "FROM vb_subject WHERE locate('".$searchvalue."',name) or locate('".$searchvalue."', description) ";
	}
	
	public function toString()
	{
		return "<b>".$this->name."</b><br/>".$this->description;
	}
}
?>