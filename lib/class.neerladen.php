<?php

class Neerladen {
	var $_db;
	
	function Neerladen() {
		$this->_db = MySql::instance();
	}
	
	function getDownloadData($id) {
		$sDocumentData = " 
			SELECT
				categorie, bestandsnaam
			FROM
				_document
			JOIN
				_documentbestand ON _document.id = documentID
			WHERE
				_documentbestand.id=".$id.";";
		
		$rDocData=$this->_db->select($sDocumentData);
		mysql_error();
		
		$data=false;
		if($rDocData) {	// als het gelukt is
			$data = mysql_fetch_array($rDocData);
		}
		
		return $data;
	}
	
	function getExtension($filename) {
		$pointpos = strrpos( $filename, "." );
		$pointpos != "" // if there are characters behind the last dot
			? $extension = substr($filename, $pointpos+1) // the extension is the text after the dot
			: $extension = ""; // the extension is empty (if there are no characters after the dot)
		return $extension;
	}
	
	function getMimeType($ext){
		$ext=strtolower($ext);
		switch($ext)
		{
			case "pdf" : $mime="application/pdf"; break;
			case "ps"  : $mime="application/postscript"; break;
			case "doc" : $mime="application/msword"; break;
			case "xls" : $mime="application/vnd.ms-excel"; break;
			case "ppt" : $mime="application/vnd.ms-powerpoint"; break;

			case "zip" : $mime="application/zip"; break;

			// plaatjes
			case "gif" : $mime="image/gif"; break;
			case "png" : $mime="image/png"; break;
			case "jpeg":
			case "jpg" : $mime="image/jpg"; break;
			case "bmp" : $mime="image/bmp"; break;
			case "tif" :
			case "tiff": $mime="image/tiff"; break;
			
			// audio en video
			case "mp3" : $mime="audio/mpeg"; break;
			case "wav" : $mime="audio/x-wav"; break;
			case "mpg" :
			case "mpeg": $mime="video/mpeg"; break;
			case "mov" : $mime="video/quicktime"; break;
			
			default    : $mime="application/octet-stream";
		}
		return $mime;
	}
}
?>
