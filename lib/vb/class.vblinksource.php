<?php
class VBLinkSource extends VBSource
{
	
	public static function getEditDiv()
	{
		return VBSource::generateEditFields("<img src='images/link.png'/>Internet koppeling bewerken",'link',
			"Voer een URL in<br/><input type='text' name='link' value='Voer een URL in'  onfocus=\"this.value=''\"/><br/>");
	}

}
?>