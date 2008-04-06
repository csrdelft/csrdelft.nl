<?php
class VBSource extends VBItem
{
	var $id;
	var $name;
	var $description;
	var $link;
	var $votesum;
	var $votecount;
	var $lid;
	var $createdate;
	var $ip;
	var $sourceType;
	var $relatedSources = array();
	var $parents = array();
	var $opinions = array();
	//field that should not be saved, inserted or edited automatically
	static $excludes = array("relatedSources","parents","opinions","id");
	
	function __construct()
	{
		$this->lid = Lid::get_lid()->getUid();
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->createdate = getDateTime();
		$this->id = -1;
		$this->votesum=0;
		$this->votecount=0;
	}
	
	public function getInsertQuery()
	{
		return VBItem::createInsertQuery($this,self::$excludes,array());
	}
	
	public function getUpdateQuery()
	{
		return VBItem::createUpdateQuery($this,self::$excludes,array())." WHERE id = ".$this->id;
	}
	
	
	public static function fromSQLResult($r)
	{
		$source;
		switch($r['sourceType'])
		{
			case 'link':
				$source = new VBLinkSource();
				break;
			case 'book':
				$source = new VBBookSource();
				break;
			case 'discussion':
				$source = new VBDiscussionSource();
				break;
			case 'file':
				$source = new VBFileSource();
				break;
			default:
			{
				var_dump($r);
				die('Kan bron niet correct laden: '.$r['id'].":".$r['sourceType']);				
			}
		}
		$source->id = $r['id'];
		$source->name = $r['name'];
		$source->description = $r['description'];
		$source->link = $r['link'];
		$source->votesum = $r['votesum'];
		$source->votecount = $r['votecount'];
		$source->lid = $r['lid'];
		$source->createdate = $r['createdate'];
		$source->ip = $r['ip'];
		$source->sourceType = $r['sourceType'];
		return $source;
	}
	
	public static function fromSQLResults($ar)
	{
		return VBItem::fromSQLResults($ar, VBSource);
	}
	
	public function setRelations($parents, $linkedsources, $opinions)
	{
		$this->relatedSources = $linkedsources;
		$this->parents = $parents;
		$this->opinions = $opinions;
	}
	
	public function voting()
	{
		if ($this->votecount>0)
			return $this->votesum / $this->votecount;
		return "nog geen beoordeling uitgebracht";		
	}
	
	public function getJSEditHandler()
	{
		return VBItem::createJSEditHandler($this,array("votesum","votecount","lid","createdate","ip","relatedSources","parents","opinions"));		
	}
	
	public function getJSAddHandler()
	{
		$r = "";
		$classes = array("link","book","discussion","file");
		foreach($classes as $c)
		{
			$class = "vb".$c."source";
			$r.= "if (document.getElementById('sourceTypeDropDown').value=='".$c."') {";
			$obj = new $class();
			$r.= $obj->getJSEditHandler();
			$r.=VBItem::getJSEditAssignment($class,"submit","Toevoegen");
			//TODO: fix, this goes wrongfds
			$r.=VBItem::getJSEditAssignment($class,"sourceType",$c);
			$r.=VBItem::getJSEditAssignment($class,"autoLinkToSubject","-1");
			$r.="document.".$class."EditForm.autoLinkToSubject.value=document.getElementById('SubjectIdField').value;";
			$r.= "}";
		}
		return $r;
	}
	
	public static function generateEditFields($kind, $linkinput)
	{
		$innerhtml = VBItem::generateHiddenFields(array("id"=>"-1","sourceType"=>"undefined","autoLinkToSubject"=>"1"));
		$innerhtml.="		
			Naam van de ".$kind.":<br/>
			<input type='text' width='200' name='name'/><br/>
			Omschrijving:<br/>
			<textarea name='description' rows='6' cols='80'></textarea><br/>
		";
		$innerhtml.=$linkinput;
		return VBItem::getEditDiv($innerhtml, 'vb'.$kind.'source',$kind.'editdiv');
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
		return "FROM vb_source WHERE locate('".$searchvalue."',name) or locate('".$searchvalue."', description) ";
	}
	
	public function toString()
	{
		return "<b>".$this->name."</b><br/>".$this->description;
	}
}
?>