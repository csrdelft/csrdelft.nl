<?php
/*
 * class.documentcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once('class.document.php');

/*
 * Weergeven van één document, bijvoorbeeld toevoegen/bewerken.
 */
class DocumentContent extends SimpleHtml{
	private $document;
	private $uploaders;
	
	public function __construct(Document $document, $uploaders){
		$this->document=$document;
		$this->uploaders=$uploaders;
	}
	public function getTitel(){
		if($this->document->getID()==0){
			return 'Document toevoegen';
		}else{
			return 'Document bewerken';
		}
	}
	
	public function view(){
		$smarty=new Smarty_csr();

		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('categorieen', DocumentenCategorie::getAll());
		$smarty->assign('document', $this->document);
		$smarty->assign('uploaders', $this->uploaders);
		$smarty->display('documenten/document.tpl');
	}
}

/*
 * Overzicht van alle categorieën met een bepaald aantal documenten per
 * categorie, zeg maar de standaarpagina voor de documentenketzer.
 */
class DocumentenContent extends SimpleHtml{

	public function getTitel(){
		return 'Documentenketzer';
	}

	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('categorieen', DocumentenCategorie::getAll());
		$smarty->display('documenten/documenten.tpl');
	}
}	
/*
 * Documenten voor een bepaalde categorie tonen.
 */
class DocumentCategorieContent extends SimpleHtml{

	private $categorie;
	
	public function __construct(DocumentenCategorie $categorie){
		$this->categorie=$categorie;
	}
	public function getTitel(){
		return 'Documenten in categorie: '.$this->categorie->getNaam();
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('categorie', $this->categorie);
		$smarty->display('documenten/documentencategorie.tpl');
	}
	
}
/*
 * Document downloaden, allemaal headers goedzetten.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentDownloadContent extends SimpleHtml{
	private $document;
	public function __construct(Document $document){
		$this->document=$document;
	}
	
	public function view(){
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('content-type: '.$this->document->getMimeType());

		$mime=$this->document->getMimetype();
		if(!strstr($mime, 'image') AND !strstr($mime, 'text')){
			header('Content-Disposition: attachment; filename='.$this->document->getBestandsnaam().';');
			header('Content-Lenght: '.$this->document->getSize().';');
		}
		readfile($this->document->getFullPath());
	}
}
class DocumentUbbContent extends SimpleHtml{
	private $document;
	public function __construct(Document $document){
		$this->document=$document;
	}
	public function getHTML(){
		$smarty=new Smarty_csr();	//hmm, lekker overkill
		$smarty->assign('document', $this->document);
		return $smarty->fetch('documenten/document.ubb.tpl'); 
	}
	public function view(){
		echo $this->getHTML();
	}
}
?>
