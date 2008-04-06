<?php
# C.S.R. Delft | vormingsbank@csrdelft.nl
# -------------------------------------------------------------------
# class.vbsearch.php
# -------------------------------------------------------------------
# author: Michel Weststrate
# klass met allerlei hulpfuncties zoeken

class VBSearch {
	var $_db;		//De database connectie
	var $_vb;		//De VormingsBank databeheer klasse
	
	### public ###
	public function VBSearch(&$vb){
		$this->_db=MySql::get_MySql();
		$this->_vb=$vb;
	}
	
	public function createSearchDiv($name, $class,$resultfield, $defaultfields, $customhtml)
	{
		#the xhttp request is copy pasted from  http://en.wikipedia.org/wiki/Ajax_%28programming%29
		$obj = new $class();
		$r='
			<div class="editdiv" id="'.$name.'searchdiv">
				<p style="display:inline">
					<a href="#" onClick="document.getElementById(\''.$name.'searchdiv\').style.display=\'none\';">X</a>
					<b>Object zoeken</b>
					<form method=\'post\' id=\''.$name.'SearchForm\' action=\'/vb/index.php\'>
						'.$obj->getSearchForm().'
						<input type="hidden" actie="dosimplesearch">
						<input type="hidden" id="offset" value="0">
						<input type="hidden" id="limit" value = "10">
						<input type="button" id="button" name="button" value="zoek" onclick=\'
							 var xmlHttp=null; 
							 try {	xmlHttp = new XMLHttpRequest();  } catch (e) {
								try {  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
								   xmlHttp=new ActiveXObject("Microsoft.XMLHTTP"); }	 }
							 xmlHttp.onreadystatechange = function() {
								if (xmlHttp.readyState == 4)
								   try { 
										if (xmlHttp.status == 200) 										
										{
											response = xmlHttp.responseText;
											response = response.replace(/#0/gm,"'.$name.'");
											response = response.replace(/#1/gm,"'.$resultfield.'");
											document.getElementById("'.$name.'searchresults").innerHTML = response;
										}
								   } catch (e) {
									  document.getElementById("'.$name.'searchresults").innerHTML = "Error on Ajax return call : " + e.description + xmlHttp.responseText;
								   }							 
							 }
							 xmlHttp.open("post","ajaxsearch.php?class='.$class.'");
							 xmlHttp.send("$params = array('.$obj->getSearchParamsFromForm($name.'SearchForm').'+
								", \"offset\"=> "+document.getElementById("'.$name.'SearchForm").offset.value+
								", \"limit\"=>"+document.getElementById("'.$name.'SearchForm").limit.value+");");
						\'>
					</form>
					<div id="'.$name.'searchresults">Geen zoekresultaten...</div>						
					<form method=\'post\' id=\''.$name.'EditForm\' action=\'/vb/index.php\'>
						<input type="hidden" name="'.$resultfield.'" value=""/>
						'.VBItem::generateHiddenFields($defaultfields).'						
						'.$customhtml.'
						<input type="submit" name="submit" value="Ga"/>
					</form>
				</p>
			</div>
			';
		return $r;
	}
	
	public function createSearchDivLink($name)
	{
		return 'document.getElementById(\''.$name.'searchdiv\').style.display=\'block\';';
	}
	
	public function handleRequest()
	{
		$class = $_GET['class'];
		$obj = new $class();
		if ($obj == null)
			die("Invalid search request");
		//we have no http extension, otherwise use http_get_request_body()
		$body = @file_get_contents('php://input');
		eval($body); //makes $params available
		//TODO: maybe we should parse JSON or unserialize somethign like that
		$query = $obj->getSearchQuery($params);
		//echo "SELECT count(*) ".$query; //TODO: use mysql_num_rows?
		//echo "SELECT * ".$query." LIMIT ".$params['offset'].", ".$params['limit'];
		$r = $this->_vb->singleSelect("SELECT count(*) ".$query);
		$maxcount = (int) $r['count(*)'];
		$offset = $params['offset'];
		$limit = $params['limit'];
		$curpage = 1 + floor($offset / $limit);
		$objs = $this->_vb->multipleSelect("SELECT * ".$query." LIMIT ".$offset.", ".$limit);
		if (!$objs)
			die("error during searchquery: "+$query);
		$objs = VBItem::fromSQLResults($objs, $class);
		$count =count($objs);
		$maxpage = ceil($maxcount / $limit);
		$till =  $offset + $count;
		//header
		echo  "<h3>Zoek resultaten</h3><br/>resultaten pagina ".$curpage."/".$maxpage.": ".$offset." - ".$till." (totaal: ".$maxcount.")<hr/>
			<table>";
		foreach($objs as $obj)
			echo "<tr><td><a href='#' class='selectorrow' onclick='document.getElementById(\"#0\EditForm\").#1.value = \"".$obj->id."\";'>".$obj->toString()."</a></td></tr>";
		echo "</table><hr>Pagina: ";
		//header
		for($i = 0; $i < $maxpage; $i++)
		{
			$pagenm = $i + 1;
			echo "<a href='#' onclick = 'document.getElementById(\"#0SearchForm\").offset.value = \"".$i*$limit."\";
				document.getElementById(\"#0SearchForm\").button.click();'>".($pagenm)."</a> ";
		}
	}
	
}