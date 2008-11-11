<?php
/** De basis voor een object dat opgeslagen en bewerkt en weergegeven kan worden */
class VBItem
{
	/** 
	Deze functieneemt een array met (sql) veldbeschrijvingen en en klasnaam, en genereert er objecten uit (zie from sql result)
	*/
	public static function fromSQLResults($ar,$class)
	{
		$a = array();
		if ($ar && sizeof($ar)>0)
			foreach($ar as $source)
			{
				$tmp = new $class; //stupid construction, but we cannot invoke 
									//static methods on dynamical found classes
									//($)$tmp::fromSQLResult -> unexpected T_PAAMAYIM_NEKUDOTAYIM   :)
				$a[] = $tmp->fromSQLResult($source);
			}
		return $a;
	}	

	/**
	klust 2 standaardknopjes voor objecten, edit en remove. De exacte events worden bepaald door getJS(Edit|Remove)Handler
	*/
	public function getEditButtons()
	{
		$r = '
			<a href="#" onClick="
				'.$this->getJSEditHandler().'
				"><img class="button" src="images/edit.png" alt="E"/></a>
			<a href="#" onClick="
				'.$this->getJSRemoveHandler().'
				"><img class="button" src="images/remove.png" alt="X"/></a>
		';
		return $r;
	}
	
	/**deze functie geeft de standaard edit handler, gebruikt VBItem: createJSEdithandler, override deze funtie
	 voor specifiek gedrag
	*/
	public function getJSEditHandler()
	{
		return VBItem::createJSEditHandler($this,array());		
	}
	
	 /**creeeer een standaard handler, door van elk veld een veld in een formulier met de waarde te zetten. Gebruik
	 *excludes om de betreffende variable niet te zetten
	*/
	public function createJSEditHandler($obj, $excludes)
	{
		$class = strtolower(get_class($obj));
		$objfields = get_object_vars($obj);
		$r="";
		//loop through all variables in object (note: not initialized vars will be ignored)
		foreach($objfields as $key => $waarde)
			if (property_exists($this,$key) && !in_array($key,$excludes))
				$r.=VBItem::getJSEditAssignment($class,$key,$waarde);
		$r.= VBItem::getJSEditAssignment($class,"submit","Opslaan");
		$r.= "document.getElementById('".$class."EditFormDiv').style.display='block';";
		return $r;		
	}
	
	/** Creeerrt een standaard remove handler, het object wordt geidenticeerd met this.id. Override om gedrag aan te passen */
	public function getJSRemoveHandler()
	{
		return VBItem::createJSRemoveHandler($this,"id=".$this->id);
	}
	
	/** hulp functie voor genereren van remove handler, genereert een melding. Gebruik uniqe als key=value om het object te identificeren */
	public function createJSRemoveHandler($obj, $unique)
	{
		return "if(confirm('Weet u zeker dat u dit object wilt verwijderen?'))
					document.location = 'index.php?actie=remove&class=".get_class($obj)."&".$unique."';";
	}
	
	/**genereert een aantal verborgenvelden, met de namen uit de array parameter */
	public static function generateHiddenFields($ar)
	{
		$r ="";
		foreach($ar as $key => $waarde)
			$r.='<input type="hidden" name="'.$key.'" value="'.$waarde.'"/>';
		return $r;
	}
	
	/** generates a default addHandler //TODO: is it used somewhere? */
	public function getJSAddHandler()
	{
		$class = strtolower(get_class($this));
		$r = $this->getJSEditHandler();
		//deze overriden the dingen die zojuist in edithandler gewijzigd zijn. 
		$r.=VBItem::getJSEditAssignment($class,"submit","Toevoegen");
		return $r;
	}
	
	/** creeert een insert query voor dit objet, gebruikt de hulpfunctie createInsertQuery */
	public function getInsertQuery()
	{
		return VBItem::createInsertQuery($this,array(),array());
	}
	
	/** creert een update query, maakt gebruik van de hulpfunctie createUpdateQuery */
	public function getCommitQuery()
	{
		return VBItem::createUpdateQuery($this,array(),array());
	}
	
	/** creëert een standaard verwijder query */
	public function getDeleteQuery()
	{
		$query = "DELETE FROM ".VBitem::classToTable(get_class($this))." WHERE id = '".$this->id."'";
		return $query;
	}
	
	/** hulpfunctie voor het creeeren van edit handlers: zet het veld van een bewerkformulier van een bepaalde klasse op een specifieke waarde */
	public static function getJSEditAssignment($class, $field, $value)
	{
		return "\ndocument.".$class."EditForm.".$field.".value='".$value."';";
	}
	
	/** update het huidige object op basis van POST of GET request, met uitzondering van de velden in de tweede parameter */
	public  function updateFromRequest($request, $excludes)
	{
		foreach($request as $key => $waarde)
		{
			if (property_exists($this,$key) && !in_array($key,$excludes))
				$this->$key = $waarde;
		}
	}
	
	/** vult een object in op basis van een query resultaat */
	public static function objectFromQueryResult($obj, $queryresult)
	{
		foreach($queryresult as $key => $waarde)
		{
			if (property_exists($obj, $key))
				$obj->$key = $waarde;
		}
	}
	
	/** hulpfunctie voor het creëeren van een query. De eerste parameter is het object om in te voegen, 
	* de tweede de velden die niet gezet zullen worden, de derde is een key => value array met alternatieve waarden voor die in het object */
	public static function createInsertQuery($obj, $excludes, $overrides)
	{
		$objfields = get_object_vars($obj);
		$fields = "";
		$values = "";
		//loop through all variables in object (note: not initialized vars will be ignored)
		foreach($objfields as $key => $waarde)
			if (!in_array($key, $excludes)) //ignore this field
			{
	    		$fields.=$key.", ";
	    		//TODO: escape waardes
	    		if (isset($overrides[$key])) //override value
	    			$values.="'".VBItem::escape($overrides[$key])."', ";
	    		else
	            	$values.="'".VBItem::escape($waarde)."', ";
			}
        $fields = substr($fields,0,strlen($fields)-2); //trim comma's
        $values = substr($values,0,strlen($values)-2);  
        $query = "INSERT INTO ".VBitem::classToTable(get_class($obj))." (".$fields.") VALUES (".$values.")" ;
        return $query;
	}
	
	/** hulpfunctie voor het creëeren van een update query, werken identiek aan die in createInsertQuery */
	public static function createUpdateQuery($obj, $excludes, $overrides)
	{
		$objfields = get_object_vars($obj);
		$fields = "";
		
		//loop through all variables in object (note: not initialized vars will be ignored)
		foreach($objfields as $key => $waarde)
			if (!in_array($key, $excludes)) //ignore this field
			{
	    		$fields.=$key."= ";
	    		//TODO: escape waardes
	    		if (isset($overrides[$key])) //override value
	    			$fields.="'".VBItem::escape($overrides[$key])."', ";
	    		else
	            	$fields.="'".VBItem::escape($waarde)."', ";
			}
        $fields = substr($fields,0,strlen($fields)-2); //trim comma's
        return $query = "UPDATE ".VBitem::classToTable(get_class($obj))." SET ".$fields;
	}
	
	/** creëert een standaard edit formulier voor een bepaalde classe. innerhtml kan gebruikt worden extra velden etc etc in het formulier te stoppen */
	public static function getEditDiv($titel, $innerhtml, $name, $beforeformhtml='', $usedefaultaction=1)
	{
		$formpostfix='EditForm';
		return '
			<div class="editdiv" id="'.$name.$formpostfix.'Div">
				<div class="editdivinner">
					<div class ="editdivheader">
						<table width="100%"><tr><td >
						'.$titel.'</td><td  class="rightjustify" width="20px">
							<a href="#" onClick="document.getElementById(\''.$name.$formpostfix.'Div\').style.display=\'none\';">X</a>
						</td></tr></table>
					</div><br/>'.$beforeformhtml.'
					<form enctype="multipart/form-data"  method="post" id="'.$name.$formpostfix.'" name="'.$name.$formpostfix.'" action="/vb/index.php">
					'.($usedefaultaction?
						'<input type="hidden" name="actie" value="commit"/>
						<input type="hidden" name="class" value="'.$name.'"/>'
						:'').
						$innerhtml.'
						<div class="rightjustify">
							<hr/>
							<input type="submit" name="submit" value="Opslaan"/>
							<input type="reset" value="Annuleren" onClick="document.getElementById(\''.$name.$formpostfix.'Div\').style.display=\'none\';"/>
						</div>
					</form>
				</div>
			</div>
			';
	}
	
	function getImage()
	{
		return "";
	}
	
	/** wat is de database tabel die hoort bij een bepalde php class naam? */
	public static function classToTable($class)
	{
		switch(strtolower($class))
		{
			case 'vblinksource':
			case 'vbdiscussionsource':
			case 'vbfilesource':
			case 'vbbooksource':
				return 'vb_source';
			default:
				return strtolower(substr($class,0,2)."_".substr($class,2));
		}
	}
	
	/** welke klasse hoort bij een bepaalde tabel?  TODO: */
	private static function tableToClass($table)
	{
	}
	
	/** escape query injections */
	private static function escape($str) {
		// from class.mysql.php
		return mysql_real_escape_string($str);
	}
	
}
?>