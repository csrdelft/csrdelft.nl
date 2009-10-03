<?php
/*
 * class.documentcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
class DocumentContent{
	private $document;
	public function __construct(Document $document){
		$this->document=$document;
	}
	public function getTitel(){
		return 'Document toevoegen';
	}
	
	public function view(){
		$smarty=new Smarty_csr();
		
		$smarty->assign('categorieen', DocumentenCategorie::getAll());
		$smarty->assign('document', $this->document);
		$smarty->display('documenten/document.tpl');
	}
}
class DocumentenContent{

	public function getTitel(){
		return 'Documentenketzer';
	}

	public function view(){
		$smarty=new Smarty_csr();

		$smarty->assign('categorieen', DocumentenCategorie::getAll());
		$smarty->display('documenten/documenten.tpl');
	}
}	
/*
 * documenten voor een bepaalde categorie
 */
class DocumentCategorieContent{

	private $categorie;
	
	
	public function __construct(DocumentCategorie $categorie){
		$this->categorie=$categorie;
	}
	public function getTitel(){
		return 'Documenten in categorie: '.$this->categorie->getNaam();
	}
	
}
/*
 * Document downloaden.
 */
class DocumentDownloadContent{
	private $document;
	public function __construct(Document $document){
		$this->document=$document;
	}
	
	public function view(){
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private',false);
		header('content-type: '.$this->document->getMimeType());
 		header('Content-Disposition: attachment; filename='.$this->document->getBestandsnaam().';');
		header('Content-Lenght: '.$this->document->getSize().';');

		readfile($this->document->getID().'_'.$this->document->getBestandsnaam());
	}
}
?>
