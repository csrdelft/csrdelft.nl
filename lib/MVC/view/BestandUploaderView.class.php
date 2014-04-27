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

	protected $selected;

	public function __construct(BestandUploader $uploader, $selected, $titel = 'Bestand uploaden') {
		parent::__construct($uploader, $titel);
		$this->selected = $selected;
	}

}

class UploadHttpView extends BestandUploaderView {

	public function __construct(UploadHttp $uploader, $selected) {
		parent::__construct($uploader, $selected, 'Uploaden in browser');
	}

	public function view() {
		echo '<div class="UploadOptie"><input type="radio" class="BestandUploaderOptie" name="BestandUploader" id="UploadHttpInput" value="UploadHttp"';
		if ($this->selected) {
			echo ' checked="checked"';
		}
		echo ' /><label for="UploadHttpInput"> ' . $this->getTitel() . '</label>';
		echo '<div class="UploadKeuze" id="UploadHttp"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><input type="file" id="httpInput" name="bestand" /></div></div>';
	}

}

class UploadFtpView extends BestandUploaderView {

	public function __construct(UploadFtp $uploader, $selected) {
		parent::__construct($uploader, $selected, 'Uit publieke FTP-map');
	}

	public function view() {
		echo '<div class="UploadOptie"><input type="radio" class="BestandUploaderOptie" name="BestandUploader" id="UploadFtpInput" value="UploadFtp"';
		if ($this->selected) {
			echo ' checked="checked"';
		}
		echo ' /> <label for="UploadFtpInput">' . $this->getTitel() . '</label>';
		echo '<div class="UploadKeuze" id="UploadFtp"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '>';
		if (count($this->model->getFilelist()) > 0) {
			echo '<select id="ftpSelect" name="bestandsnaam">';
			foreach ($this->model->getFilelist() as $filename) {
				echo '<option value="' . htmlspecialchars($filename) . '"';
				if ($this->model->getBestand() AND $this->model->getBestand()->bestandsnaam === $filename) {
					echo ' selected="selected"';
				}
				echo '>' . htmlspecialchars($filename) . '</option>';
			}
			echo '</select><br /><input type="checkbox" name="verwijderVanFtp" id="verwijderVanFtp" checked="checked" style="width: auto; margin-top: 5px;" /><label for="verwijderVanFtp"> Bestand verwijderen uit FTP-map</label>';
		} else {
			echo 'Geen bestanden gevonden in:<br />ftp://csrdelft.nl/incoming/csrdelft' . $this->model->getSubDir();
		}
		echo '</div></div>';
	}

}

class UploadUrlView extends BestandUploaderView {

	public function __construct(UploadUrl $uploader, $selected) {
		parent::__construct($uploader, $selected, 'Ophalen vanaf url');
	}

	public function view() {
		echo '<div class="UploadOptie"><input type="radio" class="BestandUploaderOptie" name="BestandUploader" id="UploadUrlInput" value="UploadUrl"';
		if ($this->selected) {
			echo ' checked="checked"';
		}
		echo ' /><label for="UploadUrlInput"> ' . $this->getTitel() . '</label>';
		echo '<div class="UploadKeuze" id="UploadUrl"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><input type="text" id="urlInput" name="url" value="' . $this->model->getUrl() . '" /></div></div>';
	}

}

class BestandBehoudenView extends BestandUploaderView {

	public function __construct(BestandBehouden $uploader, $selected) {
		parent::__construct($uploader, $selected, 'Bestaand bestand behouden');
	}

	public function view() {
		echo '<div class="UploadOptie"><input type="radio" class="BestandUploaderOptie" name="BestandUploader" id="BestandBehoudenInput" value="BestandBehouden"';
		if ($this->selected) {
			echo ' checked="checked"';
		}
		echo ' /><label for="BestandBehoudenInput"> ' . $this->getTitel() . '</label>';
		echo '<div class="UploadKeuze" id="BestandBehouden"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '>' . $this->model->getBestand()->bestandsnaam . ' (' . format_filesize($this->model->getBestand()->size) . ')</div></div>';
	}

}
