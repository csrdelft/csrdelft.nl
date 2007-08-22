<?php
define("UPLOAD_CUSTOM_ERR_SUCCEEDED", 20);
define("UPLOAD_CUSTOM_ERR_TOO_BIG", 21);
define("UPLOAD_CUSTOM_ERR_INVALID_CHARS", 22);
define("UPLOAD_CUSTOM_ERR_MOVE_FAILED", 23);
define("UPLOAD_CUSTOM_ERR_IS_NOT_UPLOADED_FILE", 24);
define("UPLOAD_CUSTOM_ERR_NO_FILES", 25);
define("UPLOAD_CUSTOM_ERR_ALREADY_EXIST", 26);
define("UPLOAD_CUSTOM_ERR_NO_TITLE", 30);
define("UPLOAD_CUSTOM_ERR_NO_CATEGORY", 31);
define("UPLOAD_CUSTOM_ERR_TITLE_EXISTS", 32);
define("UPLOAD_CUSTOM_ERR_INSERT_FAILED", 33);
//TODO: kijk eens naar het uploaden van een plaatje bij het nieuws, en zie dat het een stuk 
//eenvoudiger kan...
class Toevoegen {
	var $errorcodes;
	var $_db;
	var $_lid;
	
	function Toevoegen(&$db, &$lid) {
		$this->errorcodes = array();
		$this->_db = &$db;
		$this->_lid = &$lid;
	}
	
	function getExtension($filename) {
		$pointpos = strrpos( $filename, "." );
		$pointpos != "" // if there are characters behind the last dot
			? $extension = substr($filename, $pointpos+1) // the extension is the text after the dot
			: $extension = ""; // the extension is empty (if there are no characters after the dot)
		return $extension;
	}
	
	
	//datbase-functions
	function addDocument($title, $cat, $date, $filename, $uid) {
		if(isset($title, $cat, $date, $filename)) {
			$query = "	
				INSERT INTO
					document (naam, categorie, datum, eigenaar)
				VALUES
					('".$title."', '".$cat."', '".$date."', '".$uid."');";
			$this->_db->query($query);
			echo mysql_error();
			if(mysql_error() !== '') return false;

			$docid = mysql_insert_id();

			return $this->addFileExtension($docid, $filename);
		}
		return false;
	}

	function addFileExtension($docid,$filename){
		if(isset($docid,$filename)) {
			$res = true;
			$query = "
				INSERT INTO
					documentbestand (documentID, bestandsnaam)
				VALUES
					('".$docid."', '".$filename."');";
			$this->_db->query($query);	

			echo mysql_error();
			if(mysql_error() !== '') $res=false;
			
			return $res;
		}
		return false;
	}
	
	// returns the category-id in which the name exists (if so). Otherwise, 0
	function nameExistInDb($name) {
		$rName = $this->_db->select("
			SELECT	categorie
			FROM	document
			WHERE	naam = '".$name."';"
		);
		if( mysql_num_rows($rName) == 0 ) {
			return 0;
		} else {
			$arr = mysql_fetch_array($rName);
			return $arr['categorie'];
		}
	}
	
	
	//the upload-function
	function uploadFiles($singleMode=false) {
		$homedir = DATA_PATH.'/documenten/uploads/'; // upload-directory
		$maxfilesize = 10*1024*1024; // maximum file size, checked by this scipt
		$filenamepattern = '/[a-zA-Z0-6\-_]/';
		$date = date('Y-m-d'); // de datum voor de db-insert
		
//		echo '<script language="javascript">alert(\'count($_FILES)= '.count($_FILES).'\')</script>';
//		
//		if(isset($_FILES) && empty($_FILES) || !isset($_FILES)) {
//			$this->errorcode[] = UPLOAD_CUSTOM_ERR_NO_FILES;
//			return; // returns if there are no files to upload
//		}
		
		// there ARE files to upload){
		$counter = 1;
		for($i = 0; $i < count($_FILES); $i++, $counter++)
		{
			if($singleMode){
//				print_r($_FILES);
				$file=$_FILES['file'];
			} else {
				$file = $_FILES['file'.$counter];
			}
						
		    $filename	= $file['name'];
		    $temp	= $file['tmp_name']; 
		    $error	= $file['error'];
		    $size	= $file['size'];
		    
		    // read the fields with the title and the category-id
		    if(isset($_POST) && !empty($_POST) && is_array($_POST)) {
		    	if($singleMode){
		    		$cat = $_POST[('cat')]; // aanname: er is een POST-field met name 'cat'
		    	} else {
			    	if(isset($_POST[('title'.$counter)]) && !empty($_POST[('title'.$counter)])) {
				    	$title = mb_htmlentities($_POST[('title'.$counter)]); // Opgegeven titel
			    	} else if(!empty($filename)) {
						$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_NO_TITLE;
						continue;
					} // als er geen bestand is opgegeven maakt het ook niet uit dat er geen title is.
					if(isset($_POST[('cat'.$counter)]) && !empty($_POST[('cat'.$counter)])) {
			    		$cat = $_POST[('cat'.$counter)]; // Opgegeven categorie
					} // als hier nog een else komt: doe het als hierboven!
				}
		    }
		    
		    if($error == UPLOAD_ERR_OK) // There is no error, the file uploaded with success
		    {
	            if(!is_uploaded_file($temp)) {
	            	$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_IS_NOT_UPLOADED_FILE;
	            	continue;
	            }
	            
                if($size > $maxfilesize)
                {
                	$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_TOO_BIG;
                	continue;
                }
				
				if (!preg_match($filenamepattern,$filename)) {
				// als de bestandsnaam ongeldige tekens bevat
					$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_INVALID_CHARS;
					continue;
              	}
              	
              	// Valid filename
              	
				// Already-exist-check voor file
				$destination = $homedir.$cat.'/'.$filename;
				if( !file_exists($destination) ) {
					$move = move_uploaded_file($temp, $destination);
				} else {
					$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_ALREADY_EXIST;
					continue;
				}
				
				// Already-exist-check in database
				if($catExistingRecord=$this->nameExistInDb($title)) {
					$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_NO_TITLE;
					continue;
				}

				if(isset($move) && $move) {
					$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_SUCCEEDED;
                   	
					if(!$singleMode) {
						// The filename must be escaped so the query will be accepted any time
						// The title has been altered already. The filename cannot be done earlier because
						// it has to be saved properly on the filesystem.
						$filename = mysql_real_escape_string($filename);
	                   	// DB insert
	                   	if(! $this->addDocument($title, $cat, $date, $filename, $this->_lid->getUid()) ) {
	                   		$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_INSERT_FAILED;
						}
					}
                   	continue;
				} else {
                   	$this->errorcodes[$counter] = UPLOAD_CUSTOM_ERR_MOVE_FAILED;
                   	continue;
				}
			} else {
				$this->errorcodes[$counter] = $error;
			}
		}
	}

	function getErrorcodes() {
		return $this->errorcodes;
	}
	
	function getNumberOfErrors($errorcodes){
		$counter=0;
		$noFile=0;
		foreach($errorcodes as $error){
			if($error!=UPLOAD_CUSTOM_ERR_SUCCEEDED && $error!=UPLOAD_ERR_NO_FILE){
				$counter++;
			} else if($error==UPLOAD_ERR_NO_FILE){
				$noFile++;				
			}
		}
		if(count($errorcodes)==$noFile){
			return -1;
		} else {
			return $counter;
		}
	}

	/* DEZELFDE TWEE FUNCTIES ALS IN CLASS.DOCUMENTEN.PHP ....... */
	// gives an result-set with the categories
	function getCats($sort=null) {
		$query="
			SELECT
					ID, naam
			FROM
					documentencategorie ";
		if(isset($sort) && $sort=='naam'){
			$query.="
				ORDER BY
					naam";
		}
		$query.=";";

		$rCats = $this->_db->select($query);
		return $rCats;
	}

	// Fills the dropdown-menu for categories
	// parameter is an integer
	function getCatsOptions($selectedCat=0) {
		$rCats = $this->getCats('naam');
		$catsOptions = '';

		while($cat = mysql_fetch_array($rCats)){
			$catid = $cat['ID'];
			$catname = $cat['naam'];
			$catsOptions .= '<option value="'.$catid.'"';
			if($selectedCat == $catid) $catsOptions .= ' selected="true"';
			$catsOptions .='>'.$catname.'</option>'."\n";
		}
		return $catsOptions;
	}
}
?>
