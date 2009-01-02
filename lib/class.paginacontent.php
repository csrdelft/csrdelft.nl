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

	function PaginaContent($pagina){
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
			case 'bekijken':
				$ubb=new csrUbb();
				$ubb->allow_html=true;
				$sInhoud=$ubb->getHTML($this->_pagina->getInhoud());

				if ($this->_pagina->magBewerken()){
					$sInhoud='<a href="/pagina/'.$this->_pagina->getNaam().'/bewerken" class="knop" style="float: right;"><img src="'.CSR_PICS.'forum/bewerken.png" title="Bewerk pagina" /></a>'.$sInhoud;
				}

				echo $sInhoud;
				break;

			case 'bewerken':
				$sInhoud='<h1>Pagina bewerken</h1>';
				//$sInhoud.='Deze pagina is zichtbaar voor: '.$this->_pagina->getRechtenBekijken().' en bewerkbaar voor '.$this->_pagina->getRechtenBewerken().'.';
				$sInhoud.='

				<form action="/pagina/'.$this->_pagina->getNaam().'/bewerken" method="post">
					<strong>Titel:</strong><br />
					<input type="text" name="titel" style="width: 100%" value="'.htmlspecialchars($this->_pagina->getTitel()).'" />
					<br /><br />
					<strong>Inhoud:</strong><br />
					<textarea name="inhoud" style="width: 100%; height: 500px;">'.htmlspecialchars($this->_pagina->getInhoud()).'</textarea>
					<input type="submit" value="Opslaan" />
				</form>';

				echo $sInhoud;
				break;
		}
	}
}

?>