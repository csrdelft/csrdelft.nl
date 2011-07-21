<?php
/*
 * bibliotheekcontroller.class.php	|	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 *
 */

require_once 'controller.class.php';
require_once 'bibliotheek/boek.class.php';
require_once 'bibliotheek/catalogus.class.php';

require_once 'bibliotheek/bibliotheekcontent.class.php';

class BibliotheekController extends Controller{

	public $boek;

	public $baseurl='/communicatie/bibliotheek/';

	/*
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($querystring){
		parent::__construct($querystring);

		//wat zullen we eens gaan doen? Hier bepalen we welke actie we gaan uitvoeren
		//en of de ingelogde persoon dat mag.
		if(Loginlid::instance()->hasPermission('P_BIEB_READ')){
			if($this->hasParam(0) AND $this->getParam(0)!=''){
				$this->action=$this->getParam(0);
			}else{
				$this->action='default';
			}
			//niet alle acties mag iedereen doen, hier whitelisten voor de gebruikers
			//zonder P_BIEB_MOD, en gebruikers met, zodat bij niet bestaande acties
			//netjes gewoon de catalogus getoond wordt.
			$allow=array('default', 'boek', 'nieuwboek', 'addbeschrijving', 'verwijderbeschrijving', 'bewerkbeschrijving');
			if(LoginLid::instance()->hasPermission('P_BIEB_EDIT')){ //TODO eigenaarboek
				$allow=array_merge($allow, array('bewerkboek'));
			}
			if(LoginLid::instance()->hasPermission('P_BIEB_MOD','groep:BASFCie')){
				$allow=array_merge($allow, array('verwijderboek'));
			}
			if(!in_array($this->action, $allow)){
				$this->action='default';
			}
		}else{
			$this->action='geentoegang';
		}

		$this->performAction();
	}

	/*
	 * Catalogus tonen
	 */
	protected function action_default(){
		$this->content=new BibliotheekCatalogusContent();
	}

	/*
	 * Laad een boek object
	 * 
	 * ga er van uit dat in getParam(1) een boekid staat en laad dat in.
	 */
	private function loadBoek($boekid=null){
		if($this->hasParam(1) OR $boekid!==null ){
			if($boekid===null){
				$boekid=$this->getParam(1);
			}
			try{
				$this->boek=new Boek($boekid);
			}catch(Exception $e){
				BibliotheekCatalogusContent::invokeRefresh($e->getMessage(), CSR_ROOT.'communicatie/bibliotheek/');
			}

		}
	}

	/*
	 * Boekpagina weergeven
	 */
	protected function action_boek(){
		$this->loadBoek();
		$this->content=new BibliotheekBoekContent($this->boek);
	}

	/*
	 * Verwerken van bewerking van een veld op de boekpagina
	 */
	protected function action_bewerkboek(){
		$this->loadBoek();
		if(!$this->boek->magBewerken()){
			echo '<span class="melding">Onvoldoende rechten voor deze actie</span>';
			exit;
		}

		if(isset($_POST['id'])){
			if($this->boek->isPostedField($_POST['id']) AND $this->boek->validField($_POST['id']) AND $this->boek->saveField($_POST['id'])){
				echo $_POST['waarde'];
			}else{
				echo $this->boek->getError();
			}
		}else{
			echo '$_POST["id"] is leeg!';
		}
		exit;
	}

	/*
	 * Nieuw boek aanmaken, met formulier
	 */
	protected function action_nieuwboek(){
		//leeg object Boek laden
		$this->loadBoek(0); 
		//Eerst ongewensten de deur wijzen
		if(!$this->boek->magBewerken()){
			BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_addboek', CSR_ROOT.'communicatie/bibliotheek/');
		}
		//formulier verwerken, als het onvoldoende is terug naar formulier
		if($this->boek->isPostedFields('nieuwboek') AND $this->boek->validFields('nieuwboek') AND $this->boek->saveFields('nieuwboek')){
			header('location: '.CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
		}else{
			$this->content=new BibliotheekBoekContent($this->boek);
		}
	}
	/*
	 * Verwijder boek
	 */
	protected function action_verwijderboek(){
		$this->loadBoek(); 
		if(!$this->boek->magVerwijderen()){
			BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_addbeschrijving', CSR_ROOT.'communicatie/bibliotheek/');
		}
		if($this->boek->delete()){
			$melding='Boek met succes verwijderd.';
		}else{
			$melding='Boek verwijderen mislukt. '.$this->boek->getError().'Biebcontrllr::action_verwijderboek()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/');
	}
	/*
	 * Boekbeschrijving toevoegen
	 */
	 protected function action_addbeschrijving(){
		//object Boek laden
		$this->loadBoek(); 
		//Eerst ongewensten de deur wijzen
		if(!$this->boek->magBewerken()){
			BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_addbeschrijving', CSR_ROOT.'communicatie/bibliotheek/');
		}
		if($this->boek->isPostedFields('beschrijving') AND $this->boek->validFields('beschrijving') AND $this->boek->saveFields('beschrijving')){
			header('location: '.CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId().'#beschrijving'.$this->boek->getBeschrijving());
		}else{
			$this->content=new BibliotheekBoekContent($this->boek);
		}
	}
	/*
	 * Boekbeschrijving verwijderen
	 */
	protected function action_verwijderbeschrijving(){
		$this->loadBoek();
		
		if($this->hasParam(2)){
			$beschrijvingsid=(int)$this->getParam(2);
			if(!$this->boek->magVerwijderen($beschrijvingsid)){
				BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_verwijderbeschrijving()', CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getID());
			}
			if($this->boek->verwijderBeschrijving($beschrijvingsid)){
				$melding='Beschrijving met succes verwijderd.';
			}else{
				$melding='Beschrijving verwijderen mislukt. '.$this->boek->getError().'Biebcontrllr::action_verwijderbeschrijving()';
			}
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getID());
	}
	/*
	 * Boekbeschrijving aanpassen
	 */
	protected function action_bewerkbeschrijving(){
		$this->loadBoek();
		if($this->hasParam(2)){
			$beschrijvingsid=(int)$this->getParam(2);
			if(!$this->boek->magBewerken($beschrijvingsid)){
				BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_verwijderbeschrijving()', CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getID());
			}
			//beschrijving ophalen en in bewerkveld plaatsen
			$this->boek->setValue('beschrijvingsid', $beschrijvingsid);
			$this->boek->setValue('beschrijving', $this->boek->getBeschrijving($beschrijvingsid));
			$this->boek->assignFieldsBeschrijvingForm();
			$this->boek->setCommentBeschrijvingForm('Bewerk uw beschrijving of recensie van het boek:'); //header bewerkveld goed zetten
			//controleer en sla op
			if($this->boek->isPostedFields('beschrijving') AND $this->boek->validFields('beschrijving') AND $this->boek->saveFields('beschrijving',$bewerken=true)){
				header('location: '.CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId().'#beschrijving'.$this->boek->getBeschrijvingsid());
			}else{
				$this->content=new BibliotheekBoekContent($this->boek);
				$this->content->setAction('bewerken');
			}
		}else{
			BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getID());
		}
	}
	
}
