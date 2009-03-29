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
	
	public function view(){
		$smarty=new Smarty_csr();

		$smarty->assign('document', $this->document);
		$smarty->display('documenten/document.tpl');
	}
}
class DocumentDownloadContent{
	private $document;
	public function __construct(Document $document){
		$this->document=$document;
	}
	
	public function view(){
		header('content-type: '.$this->document->getMimeType());

		echo file_get_contents($this->document->getBestandsnaam());
	}
}
?>
