<?php
/*
 * class.documentcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
class DocumentContent{

	public function view(){
		$smarty=new Smarty_csr();

		$smarty->assign('document', new Document());
		$smarty->display('documenten/document.tpl');
	}
}
class DocumentDownloadContent{

	public function view(){
		header('content-type: '.$this->document->getMimeType());
	}
}
?>
