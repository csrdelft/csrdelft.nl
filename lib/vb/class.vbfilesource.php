<?php
class VBFileSource extends VBSource
{
	
	public static function getEditDiv()
	{
		//note that a hidden link field is created, it is not used, but avoids overriding the
		//default behaviour of VBSource.getEditHandler
		return VBSource::generateEditFields("<img src='images/file.png'/>Bestand bron bewerken", 'file',
			'<input type="hidden" name="MAX_FILE_SIZE" value="10485760">
			 <input type="hidden" name="link" value="">
			Voer een bestand in<br/><input name="file1" type="file" size="40"><br/>
			<emph>de maximale grootte voor bestanden is 10mb, zorg voor een zinnige, uniek bestandsnaam die duidelijk de inhoud aangeeft,
			geen "index.html" of "werkgroep_3.doc"</emph>');
			
	}
	
}
?>