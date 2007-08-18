<?php


#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.documenten.php
# -------------------------------------------------------------------
#
# -------------------------------------------------------------------
# Historie:
# 23-01-2006 Matthijs Neven
# . gemaakt
#

//require_once ('class.mysql.php');

class Documenten {
	var $_db;
	var $_lid;

	private $homedir = '';
	private $trash='';
	function Documenten(& $lid, & $db) {
		$this->homedir=DATA_PATH.'/leden/documenten/uploads/';
		$this->trash=DATA_PATH.'/leden/documenten/trash/';
		$this->_lid = & $lid;
		$this->_db = & $db;
	}

	function getDocumenten($catid = 0) {
		$sQuery = "
					SELECT
								ID, naam, categorie, datum
					FROM
								document
					WHERE ";
		if ($catid > 0) {
			$sQuery .= "categorie=" . $catid . " AND ";
		}
		$sQuery .= "	verwijderd = '0'
						ORDER BY
		 			      		categorie, datum DESC, naam DESC";

		$rDocumenten = $this->_db->select($sQuery);
		//array met alle documenten, bevat arrays met categorien
		$aDoc = array ();
		//array met categorie
		$aCat = array ();
		$addCatId = '';
		while ($aDocData = $this->_db->next($rDocumenten)) {
			// bij nieuwe cat: catnaam en catid neerzetten
			// anders: document

			//catID omzetten in naam
			$catid = $aDocData['categorie'];
			//			$naam = $this->getCategorieNaam($catid);
			//			$aDocData['catnaam']= $naam;
			//			$aDocData['catid']= $catid;			

			// Nieuwe categorie
			if ($addCatId !== $catid) { // id's worden vergeleken
				if (!empty ($addCatId)) {
					$aDoc[] = $aCat;
					$aCat = array ();
				}
				$naam = $this->getCategorieNaam($catid);
				$aCat[0] = $naam;
				$aCat[1] = $catid;
				$addCatId = $catid;
			}
			// weghalen om verwarring te voorkomen (catnaam en catid zijn present)
			unset ($aDocData['categorie']);
			//document toevoegen
			$aCat[] = $aDocData;
		}

		//laatste categorie toevoegen
		$aDoc[] = $aCat;

		return $aDoc;
	}

	function getCategorien() {
		//return $this->getCats();
		$rCategorien = $this->_db->select("
					SELECT
								ID, naam
					FROM
								documentencategorie
					ORDER BY
								ID	");

		//array met categorie
		$aCat = array ();

		while ($aCatData = $this->_db->next($rCategorien)) {
			$aCat[] = $aCatData;
		}

		return $aCat;
	}

	function getCategorieNaam($catID) {
		//array met categorie
		$aCat = $this->getCategorien();
		for ($i = 0; $i < count($aCat); $i++) {
			if ($aCat[$i]['ID'] == $catID)
				return $aCat[$i]['naam'];
		}
		return "foute naam";
	}

	//	function getCategorieID($catNaam){
	//		//array met categorie
	//		$aCat= $this->getCategorien();
	//
	//		for($i = 0; $i<count($aCat); $i++){
	//			if($aCat[$i]['naam'] == $catNaam)
	//			return $aCat[$i]['ID'];
	//		}
	//		return "fout ID";
	//	}
	//	
	//	function add($pad, $name, $catNaam){
	//                  $datum=date('y-m-d');
	//                  $uid=$this->_lid->getUid();
	//
	//                  //als het document al bestaat -> niet toevoegen
	//                  $dubbel=0;
	//                  $aDoc=$this->getDocumenten();
	//                  for($i=1;$i<count($aDoc);$i++){
	//                      if($catNaam == $aDoc[$i][0])
	//                          $dubbel++;
	//                      for($j=1;$j<count($aDoc[$i]);$j++){
	//                          if($pad == $aDoc[$i][$j]['bestandsnaam'])
	//                              $dubbel++;
	//                          if($name == $aDoc[$i][$j]['naam'])
	//                              $dubbel++;
	//                     }
	//                  }
	//                  if($dubbel<3){
	//                      $cat = $this->getCategorieID($catNaam);
	//                      $this->_db->query("INSERT INTO documenten (naam, bestandsnaam, cat, eigenaar, datum) VALUES ('".$name."', '".$pad."', '".$cat."', '".$uid."', '".$datum."')");
	//                      return true;
	//                  }
	//                  else return false;
	//	}

	function getExtensionsByID($id) {
		$query = "
				SELECT id, bestandsnaam
				FROM documentbestand
				WHERE documentID = $id;
				";

		$rFilenames = $this->_db->select($query);
		echo mysql_error();

		$aExtensions = array ();
		while ($file = $this->_db->next($rFilenames)) {
			$ext = $this->getExtension($file['bestandsnaam']);
			unset ($file['bestandsnaam']);
			$file['extensie'] = $ext;
			$aExtensions[] = $file;
		}
		return $aExtensions;
	}

	// TODO: uit upload halen?!
	function getExtension($filename) {
		$pointpos = strrpos($filename, ".");
		$pointpos != "" // if there are characters behind the last dot
			? $extension = substr($filename, $pointpos +1) // the extension is the text after the dot
	: $extension = ""; // the extension is empty (if there are no characters after the dot)
		return $extension;
	}

	//datbase-functions
	function addDocument($title, $cat, $date, $filename, $documentid) {
		if (isset ($title, $cat, $date, $filename)) {
			$res = true;

			if (!$this->singleFileMode) {
				$query = "	
									INSERT INTO document (naam, categorie, datum)
									VALUES ('" . $title . "', '" . $cat . "', '" . $date . "');";
				$this->_db->query($query);
				echo mysql_error();
				if (mysql_error() !== '')
					$res = false;

				$docid = mysql_insert_id();
			} else {
				if (isset ($documentid)) {
					$docid = $documentid;
				} else {
					echo "Error in addDocument";
				}
			}

			$query = "
							INSERT INTO documentbestand (documentID, bestandsnaam)
							VALUES ('" . $docid . "', '" . $filename . "');";
			$this->_db->query($query);

			echo mysql_error();
			if (mysql_error() !== '')
				$res = false;

			return $res;
		}
		return false;
	}

	// returns the category-id in which the name exists (if so). Otherwise, 0
	function nameExistInDb($name) {
		$rName = $this->_db->select("
					SELECT	categorie
					FROM	document
					WHERE	naam = '" . $name . "';");
		if (mysql_num_rows($rName) == 0) {
			return 0;
		} else {
			$arr = mysql_fetch_array($rName);
			return $arr['categorie'];
		}
	}

	// gives an result-set with the categories
	function getCats($sort = null) {
		$query = "
					SELECT
							ID, naam
					FROM
							documentencategorie ";
		if (isset ($sort) && $sort == 'naam') {
			$query .= "
							ORDER BY
								naam";
		}
		$query .= ";";

		$rCats = $this->_db->select($query);
		return $rCats;
	}

	//fills the dropdown-menu for categories
	// parameter is an integer
	function getCatsOptions($selectedCat = 0) {
		$rCats = $this->getCats('naam');
		$catsOptions = '';

		while ($cat = mysql_fetch_array($rCats)) {
			$catid = $cat['ID'];
			$catname = $cat['naam'];
			$catsOptions .= '<option value="' . $catid . '"';
			if ($selectedCat == $catid)
				$catsOptions .= ' selected="true"';
			$catsOptions .= '>' . $catname;
		}
		return $catsOptions;
	}

	function updateDocument($id, $title, $cat) {
		//echo 'Binnengekomen gegevens voor updateDocument():<br>id:'.$id.', title:'.$title.', cat:'.$cat.'.';
		// parameters checken
		if (isset ($id, $title, $cat) && is_numeric($id) && is_numeric($cat) && is_string($title)) {
			
				// gegevens van bestand ophalen
	$sFileData = "
				SELECT
					categorie, bestandsnaam
				FROM
					document
				JOIN
					documentbestand ON document.id = documentID
				WHERE
					document.id=" . $id . ";";
			$rFileData = $this->_db->select($sFileData);
			if (!$rFileData) {
				mysql_error();
				return false;
			}
			$aFileData = mysql_fetch_array($rFileData);

			$oldCat = $aFileData['categorie'];
			$filename = $aFileData['bestandsnaam'];

			// bestand verplaatsen
			$newCat = $cat;
			unset ($cat);
			$ready = true;
			if ($oldCat != $newCat) {
				$ready = rename($this->homedir . $oldCat . '/' . $filename, $this->homedir . $newCat . '/' . $filename);
			}

			if ($ready) {
				$query = "
								UPDATE
									document
								SET
									`naam` ='" . $title . "', `categorie`=" . $newCat . "
								WHERE
									`id`=" . $id . ";";

				$rUpdate = $this->_db->query($query);
				// true teruggeven als de update gelukt is
				if ($rUpdate) {
					return true;
				} else {
					echo mysql_error();
				}
			}
		}
		// false terug geven als de parameters onjuist zijn of de update mislukt is
		return false;
	}

	function deleteDocument($docid) {
	
		// Note: both dir-strings end with a '/'

		// requesting document category
		// TODO: in methode!!
		$rCat = $this->_db->select("
					SELECT
							categorie
					FROM
							document
					WHERE
							id=" . $docid . ";");
		if (!$rCat) {
			return false;
		} else {
			$aCat = $this->_db->next($rCat);
			$catid = (int) $aCat['categorie'];
			if ($catid <= 0 || $catid > 20) { // if catid is invalid
				return false;
			}
		}

		// requesting to-be-moved-filenames
		$rFiles = $this->_db->select("
					SELECT
						bestandsnaam
					FROM
						documentbestand
					WHERE
						documentID = " . $docid . ";");
		if (!$rFiles) {
			return false;
		} else {
			// move the files to trash-directory
			$aFilesDone = array ();
			while ($aFilename = $this->_db->next($rFiles)) {
				$filename = $aFilename['bestandsnaam'];
				$postpath = $catid . '/' . $filename;
				$move = rename($this->homedir . $postpath, $this->trash . $postpath);
				if (!$move) { // moving failed: do rollback and return false
					foreach ($aFilesDone as $filename) {
						$postpath = $catid . '/' . $filename;
						rename($this->trash.$postpath, $this->homedir . $postpath);
					}
					return false;
				} else { // moving succeeded
					$aFilesDone[] = $filename;
				}
			}
		}

		// updating document-record
		$rUpdate = $this->_db->select("
					UPDATE
						document
					SET
						verwijderd='1'
					WHERE
						id=" . $docid . ";
				");
		if (!$rUpdate) { // updating failed, perform rollback and return false
			foreach ($aFilesDone as $filename) {
				$postpath = $catid . '/' . $filename;
				rename($this->trash . $postpath, $this->homedir . $postpath);
			}
			return false;
		} else { // both moving and updating succeeded
			return true;
		}
	}

	function getCatIDByDocumentID($docid) {
		$sQuery = "
				SELECT
					categorie
				FROM
					document
				WHERE
					id=" . $docid . ";";

		$rCatID = $this->_db->select($sQuery);
		echo mysql_error();

		if ($rCatID) {
			$aCatID = $this->_db->next($rCatID);
			$res = (int) $aCatID['categorie'];
			return $res;
		} else {
			return false;
		}
	}

	function getDocumentNameById($docid) {
		$sQuery = "
				SELECT
					naam
				FROM
					document
				WHERE
					id=" . $docid . ";";

		$rDocName = $this->_db->select($sQuery);
		echo mysql_error();

		if ($rDocName) {
			$aDocName = $this->_db->next($rDocName);
			$res = $aDocName['naam'];
			return $res;
		} else {
			return false;
		}
	}

	function performUpload($docid) {
		if (!isset ($docid, $_FILES['file'])) {
			return false;
		}
		//TODO: Deze naam is natuurlijk knetternietszeggend, dat moet beter!
		require_once ('class.toevoegen.php');
		$toevoegen = new Toevoegen($this->_db, $this->_lid);

		$toevoegen->uploadFiles(true);
		$errorcodes = $toevoegen->getErrorcodes();
		if (isset ($errorcodes[1]) && $errorcodes[1] == UPLOAD_CUSTOM_ERR_SUCCEEDED) { // als het gelukt is
			$result = $toevoegen->addFileExtension($docid, $_FILES['file']['name']);
			if (!$result) {
				echo 'iets mis met add file extension (in performUpload())';
			}
			return $result;
		} else {
			//			echo 'errorcodes: '.print_r($errorcodes);
			return false;
		}
	}
}
?>
