<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.pagina.php
# -------------------------------------------------------------------
# Weergeven en bewerken van pagina's met tekst uit de database
# -------------------------------------------------------------------

require_once 'class.simplehtml.php';
require_once 'class.pagina.php';

class PaginaContent extends SimpleHTML{

	private $_pagina;

	private $sActie;

	function __construct($pagina){
		$this->_pagina=$pagina;
	}

	function getTitel(){
		return $this->_pagina->getTitel();
	}

	function setActie($sActie){
		$this->sActie=$sActie;
	}

	function view(){
		if(!isset($this->sActie)){
			$this->sActie='bekijken';
		}
		switch ($this->sActie){
			# Lijst pagina's laten zien in de zijkolom
			case 'zijkolom':
				$aPaginas=Pagina::getPaginas();

				echo '<h1>Pagina\'s</h1>';
				foreach($aPaginas as $aPagina){
					echo '<div class="item">';
					echo '<a href="/pagina/'.$aPagina['naam'].'/bewerken"
						title="'.htmlspecialchars($aPagina['titel']).'">'.htmlspecialchars($aPagina['titel']).'</a><br />';
					echo '</div>';
				}
			break;

			# Gewoon de inhoud van een pagina laten zien
			case 'bekijken':
				$sInhoud=CsrHtmlUBB::instance()->getHTML($this->_pagina->getInhoud());
							
				if ($this->_pagina->magBewerken()){
					$sInhoud='<a href="/pagina/'.$this->_pagina->getNaam().'/bewerken" class="knop" style="float: right;"><img src="'.CSR_PICS.'forum/bewerken.png" title="Bewerk pagina" /></a>'.$sInhoud;
				}

				echo $sInhoud;
			break;

			# De inhoud van een pagina bewerken
			case 'bewerken':
				$sInhoud='<h1>Pagina bewerken</h1>';
				$sInhoud.='Deze pagina is zichtbaar voor: '.LoginLid::formatPermissionstring($this->_pagina->getRechtenBekijken());
				$sInhoud.=' en bewerkbaar voor: '.LoginLid::formatPermissionstring($this->_pagina->getRechtenBewerken()).'.';
				$sInhoud.='
				<form action="/pagina/'.$this->_pagina->getNaam().'/bewerken" method="post">
					<strong>Titel:</strong><br />
					<input type="text" name="titel" style="width: 70%" value="'.htmlspecialchars($this->_pagina->getTitel()).'" />';

				if($this->_pagina->magPermissiesBewerken()){
					$sInhoud.='<br />
						<strong>Rechten voor bekijken:</strong><br />
						<input type="text" name="rechten_bekijken" style="width: 50%;" value="'.htmlspecialchars($this->_pagina->getRechtenBekijken()).'" />
						<br />
						<strong>Rechten voor bewerken:</strong><br />
						<input type="text" name="rechten_bewerken" style="width: 50%" value="'.htmlspecialchars($this->_pagina->getRechtenBewerken()).'" />';
				}

				$sInhoud.='<br /><br />
					<strong>Inhoud:</strong><br />
					<div id="bewerkPreviewContainer" class="previewContainer"><h3>Voorbeeld van uw bericht:</h3><div id="bewerkPreview" class="preview pagina"></div></div>
					<textarea name="inhoud" id="paginaInhoud" style="width: 100%;" rows="40">'.htmlspecialchars($this->_pagina->getInhoud()).'</textarea>
					<a style="float: right;" class="handje knop" onclick="toggleDiv(\'ubbhulpverhaal\')" title="Opmaakhulp weergeven">UBB</a>
					<a style="float: right;" class="handje knop" onclick="vergrootTextarea(\'paginaInhoud\', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a> 
					<input type="submit" value="Opslaan" />
					<input type="button" value="voorbeeld" onclick="return previewPost(\'paginaInhoud\', \'bewerkPreview\')" />
					<a href="/pagina/'.$this->_pagina->getNaam().'/bewerken" class="handje knop">Reset</a>
					<a href="/pagina/'.$this->_pagina->getNaam().'/" class="knop">Terug</a>
				</form>';

				echo $sInhoud;
			break;
		}
	}
}

?>
