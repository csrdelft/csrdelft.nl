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
			$allow=array('default', 'boek', 'nieuwboek', 
						'addbeschrijving', 'verwijderbeschrijving', 'bewerkbeschrijving',
						'addexemplaar', 'verwijderexemplaar',
						'exemplaarlenen','exemplaarteruggegeven','exemplaarterugontvangen','exemplaarvermist','exemplaargevonden');
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
				BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_verwijderbeschrijving()', CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
			}
			if($this->boek->verwijderBeschrijving($beschrijvingsid)){
				$melding='Beschrijving met succes verwijderd.';
			}else{
				$melding='Beschrijving verwijderen mislukt. '.$this->boek->getError().'Biebcontrllr::action_verwijderbeschrijving()';
			}
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Boekbeschrijving aanpassen
	 */
	protected function action_bewerkbeschrijving(){
		$this->loadBoek();
		if($this->hasParam(2)){
			$beschrijvingsid=(int)$this->getParam(2);
			if(!$this->boek->magBewerken($beschrijvingsid)){
				BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_verwijderbeschrijving()', CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
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
			BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
		}
	}
	/*
	 * Bezitter/exemplaar toevoegen
	 * /bezitboek/$boekid[/$eigenaarid]
	 */
	public function action_bezitboek(){
		$this->loadBoek();
		if(!$this->boek->magBekijken()){
			BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_bezitboek()', CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
		}

		if($this->hasParam(2)){
			$eigenaar = $this->getParam(2);
		}else{
			$eigenaar = LoginLid::instance()->getUid();
		}
		if(Lid::isValidUid($eigenaar)){
			if($this->boek->addEigenaar($eigenaar)){
				$melding='Exemplaar met succes toegevoegd.';
			}else{
				$melding='Exemplaar toevoegen mislukt. '.$this->boek->getError().'Biebcontrllr::action_bezitboek()';
			}
		}else{
			$melding='Ongeldig uid "'.$eigenaar.'" Biebcontrllr::action_bezitboek()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Exemplaar toevoegen
	 * /addexemplaar/$boekid[/$eigenaarid]
	 */
	public function action_addexemplaar(){
		$this->loadBoek();
		if(!$this->boek->magBekijken()){
			BibliotheekCatalogusContent::invokeRefresh('Onvoldoende rechten voor deze actie. Biebcontrllr::action_addexemplaar()', CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
		}

		if($this->hasParam(2)){
			$eigenaar = $this->getParam(2);
		}else{
			$eigenaar = LoginLid::instance()->getUid();
		}
		if(Lid::isValidUid($eigenaar)){
			if($this->boek->addExemplaar($eigenaar)){
				$melding='Exemplaar met succes toegevoegd.';
			}else{
				$melding='Exemplaar toevoegen mislukt. '.$this->boek->getError().'Biebcontrllr::action_addexemplaar()';
			}
		}else{
			$melding='Ongeldig uid "'.$eigenaar.'" Biebcontrllr::action_addexemplaar()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Exemplaar verwijderen
	 * /deleteexemplaar/$exemplaarid
	 */
	public function action_verwijderexemplaar(){
		$this->loadBoek();
		if($this->hasParam(2) AND ($this->boek->isEigenaar() OR $this->magVerwijderen())){
			if($this->boek->verwijderExemplaar($this->getParam(2))){
				$melding='Exemplaar met succes verwijderd.';
			}else{
				$melding='Exemplaar verwijderen mislukt. '.$this->boek->getError().'Biebcontrllr::action_verwijderexemplaar()';
			}
		}else{
			$melding='Onvoldoende rechten voor deze actie. Biebcontrllr::action_verwijderexemplaar()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Exemplaar is geleend
	 * kan door iedereen, inclusief eigenaar
	 */
	public function action_exemplaarlenen(){
		$this->loadBoek();
		if($this->hasParam(2) AND $this->boek->magBekijken()){
			if($this->boek->leenExemplaar($this->getParam(2))){
				$melding='Exemplaar geleend.';
			}else{
				$melding='Exemplaar lenen is mislukt. '.$this->boek->getError().'Biebcontrllr::action_exemplaarlenen()';
			}
		}else{
			$melding='Onvoldoende rechten voor deze actie. Biebcontrllr::action_exemplaarlenen()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Lener zegt dat hij/zij exemplaar heeft teruggegeven
	 * Alleen door lener
	 */
	public function action_exemplaarteruggegeven(){
		$this->loadBoek();
		if($this->hasParam(2) AND $this->boek->isLener($this->getParam(2))){
			if($this->boek->teruggevenExemplaar($this->getParam(2))){
				$melding='Exemplaar is teruggegeven.';
			}else{
				$melding='Teruggave van exemplaar melden is mislukt. '.$this->boek->getError().'Biebcontrllr::action_exemplaarteruggegeven()';
			}
		}else{
			$melding='Onvoldoende rechten voor deze actie. '.$this->boek->getError().' Biebcontrllr::action_exemplaarteruggegeven()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Exemplaar is terugontvangen van lener
	 * Alleen door eigenaar
	 */
	public function action_exemplaarterugontvangen(){
		$this->loadBoek();
		if($this->hasParam(2) AND $this->boek->isEigenaar($this->getParam(2))){
			if($this->boek->terugontvangenExemplaar($this->getParam(2))){
				$melding='Exemplaar terugontvangen.';
			}else{
				$melding='Exemplaar terugontvangen melden is mislukt. '.$this->boek->getError().'Biebcontrllr::action_exemplaarterugontvangen()';
			}
		}else{
			$melding='Onvoldoende rechten voor deze actie. Biebcontrllr::action_exemplaarterugontvangen()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Exemplaar is vermist
	 * Alleen door eigenaar
	 */
	public function action_exemplaarvermist(){
		$this->loadBoek();
		if($this->hasParam(2) AND $this->boek->isEigenaar($this->getParam(2))){
			if($this->boek->vermistExemplaar($this->getParam(2))){
				$melding='Exemplaar vermist.';
			}else{
				$melding='Exemplaar vermist melden is mislukt. '.$this->boek->getError().'Biebcontrllr::action_exemplaarvermist()';
			}
		}else{
			$melding='Onvoldoende rechten voor deze actie. Biebcontrllr::action_exemplaarvermist()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
	/*
	 * Exemplaar is gevonden
	 * Alleen door eigenaar
	 */
	public function action_exemplaargevonden(){
		$this->loadBoek();
		if($this->hasParam(2) AND $this->boek->isEigenaar($this->getParam(2))){
			if($this->boek->gevondenExemplaar($this->getParam(2))){
				$melding='Exemplaar gevonden.';
			}else{
				$melding='Exemplaar gevonden melden is mislukt. '.$this->boek->getError().'Biebcontrllr::action_exemplaargevonden()';
			}
		}else{
			$melding='Onvoldoende rechten voor deze actie. Biebcontrllr::action_exemplaargevonden()';
		}
		BibliotheekBoekContent::invokeRefresh($melding, CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->boek->getId());
	}
}
