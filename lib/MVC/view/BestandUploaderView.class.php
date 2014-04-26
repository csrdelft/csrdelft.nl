<?php

/**
 * BestandUploaderView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bekijken van een CmsPagina.
 */
abstract class BestandUploaderView extends TemplateView {

	public function __construct(BestandUploader $uploader, $titel = 'Bestand uploaden') {
		parent::__construct($uploader, $titel);
	}

	public function viewRadiobutton() {
		echo '<input type="radio" name="methode" id="r' . $this->model->getNaam() . '" value="' . $this->model->getNaam() . '" ';
		if ($this->model->isActive()) {
			echo 'checked="checked"';
		}
		echo ' /> <label for="r' . $this->model->getNaam() . '">' . $this->getTitel() . '</label>';
	}

}

class BestandBehoudenView extends TemplateView {

	public function __construct(Bestand $bestand) {
		parent::__construct($bestand, 'Bestaand bestand behouden');
	}

	public function view() {
		echo $this->model->getBestandsnaam() . ' (' . format_filesize($this->model->getSize()) . ')';
	}

}

class UploadBrowserView extends BestandUploaderView {

	public function __construct(UploadBrowser $uploader) {
		parent::__construct($uploader, 'Uploaden in browser');
	}

	public function view() {
		echo '<label for="fileInput">Selecteer bestand: </label><input type="file" id="fileInput" name="file_upload" />';
	}

}

class UploadURLView extends BestandUploaderView {

	public function __construct(UploadURL $uploader) {
		parent::__construct($uploader, 'Ophalen vanaf url');
	}

	public function view() {
		echo <<<HTML
<label for="urlInput">Geef url op:</label>
<div class="indent">
	<input type="text" id="urlInput" name="url" class="fromurl" value="{$this->model->getUrl()}" /><br />
	<span class="small">Bestanden zullen met het mime-type <code>application/octet-stream</code> worden opgeslagen.</span>
</div>
HTML;
	}

}

class UploadFTPView extends BestandUploaderView {

	public function __construct(UploadFTP $uploader) {
		parent::__construct($uploader, 'Uit publieke FTP-map');
	}

	public function view() {
		echo '<label for="publicftp">Selecteer een bestand:</label><div id="ftpOpties" class="indent">';
		if (count($this->model->getFilelist()) > 0) {
			echo '<select name="ftpfile" class="ftpfile">';
			foreach ($this->model->getFilelist() as $file) {
				echo '<option value="' . htmlspecialchars($file) . '"';
				if ($this->file == $file) {
					echo 'selected="selected"';
				}
				echo '>' . htmlspecialchars($file) . '</option>';
			}
			echo '</select><br /><input type="checkbox" name="deleteFiles" /> <label for="deleteFiles">Bestand verwijderen uit FTP-map.</label>';
		} else {
			echo 'Geen bestanden gevonden in:<br /> <code class="small">ftp://csrdelft.nl/incoming/csrdelft' . $this->model->getSubDir() . '</code>';
		}
		echo '</div>';
	}

}
