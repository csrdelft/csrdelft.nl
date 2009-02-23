<?php
/*
 * class.documentcontroller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */

require_once 'class.controller.php';
require_once 'documenten/class.document.php';
require_once 'documenten/class.categorie.php';

require_once 'documenten/class.documentcontent.php';

class DocumentController extends Controller{

	public $document;

	/*
	 * querystring:
	 *
	 */
	public function __construct($querystring){
		parent::__construct($querystring);
		if($this->hasParam(1)){
			$this->action=$this->getParam(0);
		}


		//Normale gebruikers mogen niet alle acties doen.
		$allow=array('default', '');
		if(!in_array($this->action, $allow) AND !$this->document->magBewerken()){
			$this->action='default';
		}
		$this->performAction();
	}
	public function action_default(){
		$this->content=new DocumentContent();
	}
	public function action_delete(){

	}
}

?>
