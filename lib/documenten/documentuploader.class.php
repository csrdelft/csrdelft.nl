<?php
/*
 * Verschillende manieren om bestanden te uploaden voor de documentenketzer.
 *
 * DocumentUploader defenieert wat standaardfunctionaliteit, de andere classes
 * zorgen voor de speciale functies. In DocumentUploader::getAll() moet een
 * eventueel nieuw object aan de array toegevoegd worden.
 */

require_once 'mimemagic/MimeMagic.php'; //mediawiki's mime magic class

abstract class DocumentUploader{

	public $beschrijving;
	public $isActive=false;

	public $errors;

	public $filename;
	public $mimetype='application/octet-stream';
	public $size;

	public function __construct(){
	}
	public function getNaam(){ return get_class($this); }

	protected function addError($error){ $this->errors.=$error."\n"; }
	public function getErrors(){ return $this->errors; }

	abstract public function valid();						//is de formulierinvoer geldig voor deze methode?
	abstract public function movefile(Document $document);	//bestand uiteindelijk opslaan op de juiste plek.

	public function getFilename(){ return $this->filename; }
	public function getMimetype(){ return $this->mimetype; }
	public function getSize(){ 	   return $this->size; }

	public function viewRadiobutton(){
		echo '<input type="radio" name="methode" id="r'.$this->getNaam().'" value="'.$this->getNaam().'" ';
		if($this->isActive){ echo 'checked="checked"'; }
		echo ' />';
		echo '<label for="r'.$this->getNaam().'">'.$this->beschrijving.'</label>';
	}

	abstract public function view();

	/*
	 * Geef een array terug met de aanwezige uploadmethodes.
	 * Bij een nieuw document willen we geen bestand behouden, want er
	 * is nog helemaal geen bestand, dus die kunnen we uitsluiten.
	 */

	public static function getAll($document, $active, $includeKeepfile=true){
		$methodes=array('DUKeepfile', 'DUFileupload', 'DUFromurl', 'DUFromftp');
		$return=array();
		foreach($methodes as $methode){
			if(!$includeKeepfile AND $methode=='DUKeepfile'){
				continue;
			}
			$return[$methode]=new $methode($document);
			if($active==$methode){
				$return[$methode]->isActive=true;
			}
		}
		return $return;
	}
}
class DUKeepfile extends DocumentUploader{
	public $document=null;
	public function __construct(Document $document){
		$this->document=$document;

		$this->filename=$document->getBestandsnaam();
		$this->mimetype=$document->getMimetype();
		$this->size=$document->getSize();

		$this->beschrijving='Huidige behouden';
	}

	public function valid(){ return true; }
	public function moveFile(Document $document){
		//do nothing here.
		return true;
	}

	public function view(){
		echo $this->document->getBestandsnaam().' ('.format_filesize($this->document->getSize()).')';
	}

}

class DUFileupload extends DocumentUploader{

	private $file; //relevante inhoud van $_FILES;

	public function __construct(){
		$this->beschrijving='Uploaden in browser';
	}

	public function valid(){
		if(!isset($_FILES['file_upload'])){
			$this->addError('Formulier niet compleet');
		}
		$this->file=$_FILES['file_upload'];
		if($this->file['error']!=0){
			switch($this->file['error']){
				case 1:
					$this->addError('Bestand is te groot: Maximaal '.ini_get('upload_max_filesize').'B ');
				break;
				case 4:
					$this->addError('Selecteer een bestand');
				break;
				default:
					$this->addError('Upload-error: error-code: '.$this->file['error']);
			}
		}
		if($this->getErrors()==''){
			$this->filename=$this->file['name'];
			$this->mimetype=$this->file['type'];
			$this->size=$this->file['size'];
			return true;
		}else{
			return false;
		}
	}
	public function moveFile(Document $document){
		return $document->moveUploaded($this->file['tmp_name']);
	}

	public function view(){
		echo '<label for="fromUrl">Selecteer bestand: </label><input type="file" name="file_upload" />';
	}
}
class DUFromurl extends DocumentUploader{

	private $file; //string met het hele bestand.
	private $url='http://';

	public function __construct(){
		$this->beschrijving='Ophalen vanaf url';
	}

	public function valid(){
		if(!isset($_POST['url'])){
			$this->addError('Formulier niet compleet');
		}
		if(!url_like($_POST['url'])){
			$this->addError('Dit lijkt niet op een url...');
		}
		if(!in_array(ini_get('allow_url_fopen'), array('On', 'Yes', 1))){
			$this->addError('PHP.ini configuratie fout: allow_url_fopen moet op On staan...');
		}
		$this->url=$_POST['url'];

		if($this->getErrors()==''){
			$this->file=@file_get_contents($this->url);
			if(strlen($this->file)==0){
				$this->addError('Bestand is leeg, check de url.');
			}else{
				$naam=substr(trim($this->url), strrpos($this->url, '/')+1);

				//Bestand tijdelijk omslaan om mime-type te bepalen.
				$tmpfile=TMP_PATH.'docuketz0r'.microtime().'.tmp';
				if(is_writable(TMP_PATH)){
					file_put_contents($tmpfile, $this->file);
					$mimetype=MimeMagic::singleton()->guessMimeType($tmpfile);
					unlink($tmpfile);

					$this->filename=preg_replace("/[^a-zA-Z0-9\s\.\-\_]/", '', $naam);
					$this->mimetype=$mimetype;
					$this->size=strlen($this->file);
				}else{
					$this->addError('Ophalen vanaf url mislukt: TMP_PATH is niet beschrijfbaar.');
				}
			}
		}
		return $this->getErrors()=='';
	}
	public function moveFile(Document $document){
		return $document->putFile($this->file);
	}

	public function view(){
		echo '
			<label for="fromUrl">Geef url op:</label>
			<div class="indent">
				<input type="text" name="url" class="fromurl" value="'.$this->url.'" /><br />
				<span class="small">Bestanden zullen met het mime-type <code>application/octet-stream</code> worden opgeslagen.</span>
			</div>';

	}
}
class DUFromftp extends DocumentUploader{

	private $file;	//naam van het gekozen bestand.
	private $path;	//pad naar de public-ftp documentenmap.

	public function __construct(){
		$this->path=PUBLIC_FTP.'/documenten/';
		$this->beschrijving='Uit publieke FTP-map';
	}
	private function getFilelist(){
		$handler = opendir($this->path);
		while($file = readdir($handler)) {
			//we willen geen directories en geen verborgen bestanden.
			if(!is_dir($this->path.$file) AND substr($file,0,1)!='.'){
				$results[] = $file;
			}
		}

		closedir($handler);
		return $results;
	}

	public function valid(){
		if(!isset($_POST['ftpfile'])){
			$this->addError('Formulier niet compleet.');
		}
		if(!file_exists($this->path.$_POST['ftpfile'])){
			$this->addError('Bestand is niet aanwezig in public FTP-map');
		}
		if($this->getErrors()==''){
			$this->file=$_POST['ftpfile'];
			$this->filename=$_POST['ftpfile'];
			$this->size=filesize($this->path.$this->file);
			$this->mimetype=MimeMagic::singleton()->guessMimeType($this->path.$this->file);
		}
		return $this->getErrors()=='';
	}
	public function moveFile(Document $document){
		if($document->copyFile($this->path.$this->file)){
			//moeten we het bestand ook verwijderen uit de public ftp?
			if(isset($_POST['deleteFiles'])){
				return unlink($this->path.$this->file);
			}
			return true;
		}
		return false;
	}

	public function view(){
		echo '<label for="publicftp">Selecteer een bestand:</label>
			<div id="ftpOpties" class="indent">';
		if(is_array($this->getFilelist()) AND count($this->getFilelist())>0){
			echo '<select name="ftpfile" class="ftpfile">';
			foreach($this->getFilelist() as $file){
				echo '<option value="'.htmlspecialchars($file).'"';
				if($this->file==$file){ echo 'selected="selected"'; }
				echo '>'.htmlspecialchars($file).'</option>';
			}
			echo '</select><br />';
			echo '<input type="checkbox" name="deleteFiles" /> <label for="deleteFiles">Bestand verwijderen uit FTP-map.</label>';
		}else{
			echo 'Geen bestanden gevonden in:<br /> <code class="small">ftp://csrdelft.nl/incoming/csrdelft/documenten/</code>';
		}
		echo '</div>';
	}

}
?>
