<?php
class VBBookSource extends VBSource
{
	var $schrijver;
	var $uitgever;
	var $jaar;
	var $isbn;
	
	public static function getEditDiv()
	{
		return VBSource::generateEditFields("<img src='images/book.png'/>Boek bewerken/toevoegen",'book',
			'<input type="hidden" name="link" value=""/>
			Schrijver: <br/><input type="text" width="300" name="schrijver"/><br/>
			Uitgever: <br/><input type="text" width="300" name="uitgever"/><br/>
			Jaar: <br/><input type="text" width="300" name="jaar"/><br/>
			ISBN: <br/><input type="text" width="300" name="isbn"/><br/>');
	}
	
	public  function updateFromRequest($request, $excludes)
	{
		foreach($request as $key => $waarde)
		{
			if (property_exists($this,$key) && !in_array($key,$excludes))
			{
				$this->$key = $waarde;
			}
		}
		$this->link = $this->schrijver."##".$this->uitgever."##".$this->jaar."##".$this->isbn;
	}
}
?>