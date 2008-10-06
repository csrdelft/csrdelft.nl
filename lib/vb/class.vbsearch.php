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
	
	
	
	
	/*
		name = naam van dit formulier, is gekoppeld aan de naam van het resultaatformulier etc, gebruikt om verschillende zoek
			formulieren op een pagina te onderscheiden
		searchfieldcode = HTML inputs voor alle parameters die naar de searchfunctie doorgegeven moeten worden, bijvoorbeeld:
			"Criterium: <input type='text' width='200' name='searchvalue'/><br/>"
		selectionfieldname = de naam van het veld dat gewijzigd dient te worden in het geval dat de resultaat selectie
			gebruikt dient te worden voor een ander formulier (bijvoorbeeld bij het leggen van relaties), 
			als het alleen om zoeken gaat, kan dit leeg blijven. 
	*/	
	public function createSearchForm($name,  $searchformhtml, $selectionfieldname='')
	{	
		#the xhttp request is copy pasted from  http://en.wikipedia.org/wiki/Ajax_%28programming%29
		return '
			<form id="'.$name.'SearchForm" >
				'.$searchformhtml.'
				<input type="hidden" name="name" value="'.$name.'"/>
				<input type="hidden" name="resultmode" value="'.($selectionfieldname==''?"search":"form").'"/>
				<input type="hidden" name="offset" value="0"/>
				<input type="hidden" name="limit" value = "10"/>
				<input type="button" id="vbzoekbtn" name="button" value="zoek" width = "120px" onclick=\'
					 var xmlHttp= newRequest();
					 xmlHttp.onreadystatechange = function() {
						if (xmlHttp.readyState == 4)
						   try { 
								if (xmlHttp.status == 200) 										
								{
									response = xmlHttp.responseText;
									response = response.replace(/#0/gm,"'.$name.'");
									response = response.replace(/#1/gm,"'.$selectionfieldname.'");
									document.getElementById("'.$name.'searchresults").innerHTML = response;
								}
						   } catch (e) {
							  document.getElementById("'.$name.'searchresults").innerHTML = "Error on Ajax return call : " + e.description + xmlHttp.responseText;
						   }							 
					 }
					 xmlHttp.open("post","ajaxsearch.php");
					var request = formToJSON(document.getElementById("'.$name.'SearchForm"));
					xmlHttp.send(request);
				\'/>
			</form>';
			//					 xmlHttp.send("$params = array('.$obj->getSearchParamsFromForm($name.'SearchForm').'+
			//			", \"offset\"=> "+document.getElementById("'.$name.'SearchForm").offset.value+
			//			", \"limit\"=>"+document.getElementById("'.$name.'SearchForm").limit.value+");");
//					var request = formToJSON();
	//				alert(request);
	/* 					var jtools = new Jsoner();
					var json = {};
					var request = jtools.populateFormToJson(document.getElementById("'.$name.'SearchForm"),json);
*/

	}
	
	
	public function createSearchResultDiv($name)
	{
		return '<div id="'.$name.'searchresults">Geen zoekresultaten...</div>';
	}
	
	/*
		Generates a form, that contains a field for which a selection is required
		name: the name of this set of search forms, editforms and resultdivs
		searchformhtml: the code of the searchform (see createSearchForm)
		resultfield:	the name of the input in the editform that represents the currentselection
		editformhtml: the code of the editform elements
	*/
	public function createSearchBasedEditForm($titel,$name, $searchformhtml, $resultfield, $editformhtml)
	{
		return VBItem::getEditDiv(
			$titel,
			$editformhtml.'<input type="hidden" name="'.$resultfield.'" value=""/>',
			$name,
			$this->createSearchForm($name, $searchformhtml,$resultfield).
			'<hr/>'.
			$this->createSearchResultDiv($name).'											
			<hr/>',
			0);
	}
	
	public function createEditFormLink($name)
	{
		return 'document.getElementById(\''.$name.'EditFormDiv\').style.display=\'block\';';
	}
	
	public function handleRequest()
	{
		//we have no http extension, otherwise use http_get_request_body()
		$body = @file_get_contents('php://input');
		$params = json_decode($body);
		$name = $params->name;
		$objs = $this->_vb->handleSearchRequest($name, $params);
		$curpage = 1 + floor($params->offset / $params->limit);
		$count =count($objs);
		$maxpage = ceil($params->maxcount / $params->limit);
		$till =  $params->offset + $count;
		//header
		echo  "<br/>resultaten ".min($till, (1+$params->offset))." - ".$till." (van ".$params->maxcount.")<br/><br/>";
		$i = 0;
		//var_dump($params);
		foreach($objs as $obj)
		{
			$class = strtolower(substr(VBItem::classToTable(get_class($obj)),3)); //extract action from class... (via table to get 'source')
			$msg = $obj->description;
			if (sizeof($msg) > 200)
				$msg = substr($msg,0,197)."...";
			//list items, depending on select (in a search form) or view (just as results) mode
			if(isset($params->resultmode) && $params->resultmode == "form")
			{
			echo "
				<div class='thema-grotebalk'>
					<table>
						<tr>
							<td>
								<input class='plaatje' type='radio' name='".$name."selection' id='".$name."sel".$i."'  onclick='document.getElementById(\"#0EditForm\").#1.value = \"".$obj->id."\";'/>
								<img class='plaatje' src='".$obj->getImage()."'/>
							</td><td>
								<div class='titel'>
									<label for='".$name."sel".$i."'>
										<a href='#' class='selectorrow'>".$obj->name."</a>
									</label>
								</div>
								<div class='bericht'>".$msg."<a target='_blank' href='index.php?actie=".$class."&id=".$obj->id."'> [bekijk]</a></div>
							</td>
					</table>
				</div>";
			}
			else
			{
				echo '<div class="thema-grotebalk">
						<table>
							<tr>
								<td rowspan="2"><img class="plaatje" src="'.$obj->getImage().'"/></td>
								<td><div class="titel">
									<a  href="index.php?actie='.$class.'&id='.$obj->id.'" title="'.$obj->description.'">'.$obj->name.'</a>
								</div></td>
							</tr><tr>
								<td><div class="bericht">'.$msg.'</div></td>
							</tr>
						</table>
					</div>';
			}
			$i++;
		}
		echo "Pagina: ";
		//header
		for($i = 0; $i < $maxpage; $i++)
		{
			$pagenm = $i + 1;
			//view next page by changing the limit and pressing the search button
			echo "<a href='#' onclick = 'document.getElementById(\"#0SearchForm\").offset.value = \"".$i*$params->limit."\";
				document.getElementById(\"#0SearchForm\").button.click();'>".($pagenm)."</a> ";
		}
	}
	
}