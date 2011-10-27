<?php
/*
 * bibliotheekcontent.class.php	|	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 *
 */
require_once 'catalogus.class.php';

/*
 * Catalogus
 */
class BibliotheekCatalogusContent extends SimpleHtml{


	public function getTitel(){
		return 'Bibliotheek | Catalogus';
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('action', $this->action);

		$smarty->display('bibliotheek/catalogus.tpl');
	}

}

/*
 * Boekstatus
 */
class BibliotheekBoekstatusContent extends SimpleHtml{

	public function getTitel(){
		return 'Bibliotheek | Boekstatus';
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('action', $this->action);

		$smarty->display('bibliotheek/boekstatus.tpl');
	}
}

class BibliotheekCatalogusDatatableContent extends SimpleHtml{
	private $catalogus;

	public function __construct(Catalogus $catalogus){
		$this->catalogus=$catalogus;
	}

	public function view(){
		/*
		 * Output
		 */
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $this->catalogus->getTotaal(),
			"iTotalDisplayRecords" => $this->catalogus->getGefilterdTotaal(),
			"aaData" => array()
		);

		//kolommen van de dataTable
		$aKolommen = $this->catalogus->getKolommen();
		//Vult de array aaData met htmlcontent. Entries van aaData corresponderen met tabelcellen.
		foreach($this->catalogus->getBoeken() as $aBoek){
			$boek = array();
			//loopt over de zichtbare kolommen
			for($i=0 ; $i<$this->catalogus->getKolommenZichtbaar() ; $i++ ){
				//van sommige kolommen wordt de inhoud verfraaid
				switch($aKolommen[$i]){
					case 'titel':
						$boek[] = $this->render_titel($aBoek);
						break;
					case 'eigenaar':
					case 'lener':
						$boek[] = $this->render_lidlink($aBoek, $aKolommen[$i]);
						break;
					case 'status':
						$boek[] = $this->render_status($aBoek);
						break;
					case 'leningen':
						$boek[] = str_replace(', ', '<br />', $aBoek['leningen']);
						break;
					case 'uitleendatum':
						$boek[] = $this->render_uitleendatum($aBoek);
						break;
					default:
						$boek[] = htmlspecialchars($aBoek[ $aKolommen[$i] ]);
				}
			}
			$output['aaData'][] = $boek;
		}

		echo json_encode( $output );
	}

	/*
	 * methodes om htmlinhoud van cellen te maken
	 */
	// Geeft html voor titel-celinhoud
	protected function render_titel($aBoek){
		//statusindicator op cataloguspagina en title van url
		if($this->catalogus->getExemplaarinfo()){
			//boekstatus
			$titel = '';
			$urltitle = 'title="Boek: '.$aBoek['titel'].'
Auteur: '.$aBoek['auteur'].' 
Rubriek: '.$aBoek['categorie'].'"';
		}else{
			//catalogus
			$titel = '<span title="'.$aBoek['status'].' boek" class="indicator '.$aBoek['status'].'">•</span> ';
			$urltitle = 'title="Boek bekijken"';
		}

		//url
		if(Loginlid::instance()->hasPermission('P_BIEB_READ')){
			$titel .= '<a href="/communicatie/bibliotheek/boek/'.$aBoek['id'].'" '.$urltitle.'>'
						.htmlspecialchars($aBoek['titel'])
						.'</a>';
		}else{
			$titel .= htmlspecialchars($aBoek['titel']);
		}
		return $titel;
	}
	//Geeft html voor lener- of eigenaar-celinhoud
	protected function render_lidlink($aBoek, $key){
		$aUid = explode(', ', $aBoek[$key]);
		$sNaamlijst = '';
		foreach( $aUid as $uid ){
			if($uid == 'x222'){
				$sNaamlijst .= 'C.S.R.-bibliotheek';
			}else{
				if($naam = Lid::getNaamLinkFromUid($uid, $vorm='civitas', $mode='link')){
					$sNaamlijst .= $naam;
				}else{
					$sNaamlijst .= '-';
				}
			}
			$sNaamlijst .= '<br />';
		}
		return $sNaamlijst;
	}
	//Geeft html voor status-celinhoud
	protected function render_status($aBoek){
		$aStatus = explode(', ', $aBoek['status']);
		$aUitleendatum = explode(', ', $aBoek['uitleendatum']);
		$sStatuslijst = '';
		$j=0;
		foreach( $aStatus as $status ){
			if($status == 'uitgeleend' OR  $status == 'teruggegeven'){
				$sStatuslijst .= '<span title="Uitgeleend sinds '.strip_tags(reldate($aUitleendatum[$j])).'">'
								.ucfirst($status)
								.'</span>';
			}elseif($status == 'vermist'){
				$sStatuslijst .= '<span title="Vermist sinds '.strip_tags(reldate($aUitleendatum[$j])).'">'
								.ucfirst($status)
								.'</span>';
			}else{
				$sStatuslijst .= ucfirst($status);
			}
			$sStatuslijst .= '<br />';
			$j++;
		}
		return $sStatuslijst;
	}
	//Geeft html voor status-celinhoud
	protected function render_uitleendatum($aBoek){
		$aStatus = explode(', ', $aBoek['status']);
		$aUitleendatum = explode(', ', $aBoek['uitleendatum']);
		$sUitleendatalijst = '';
		$j=0;
		foreach( $aUitleendatum as $uitleendatum ){
			if($aStatus[$j] == 'uitgeleend' OR  $aStatus[$j] == 'teruggegeven' OR $aStatus[$j] == 'vermist'){
				$sUitleendatalijst .= strftime("%d %b %Y", strtotime($uitleendatum));//date("j M Y", strtotime($uitleendatum)); //strip_tags(reldate($uitleendatum));
			}
			$sUitleendatalijst .= '<br />';
			$j++;
		}
		return $sUitleendatalijst;
	}
}

/*
 * Boek weergeven
 */
class BibliotheekBoekContent extends SimpleHtml{

	private $boek;
	private $action='view';

	public function __construct(Boek $boek){
		$this->boek=$boek;
	}
	public function getTitel(){
		return 'Bibliotheek | Boek: '.$this->boek->getTitel();
	}
	public function setAction($action){
		$this->action=$action;
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('boek', $this->boek);
		$smarty->assign('action', $this->action);

		$smarty->display('bibliotheek/boek.tpl');
	}

}
/*
 * Contentclasse voor de boek-ubb-tag
 */
class BoekUbbContent extends SimpleHTML{
	private $boek;
	private $style;
	public function __construct($boekid, $style='default'){
		try{
			require_once 'bibliotheek/boek.class.php';
			$this->boek=new Boek((int)$boekid);
		}catch(Exception $e){
			$this->boek='[boek] Boek [boekid:'.(int)$boekid.'] bestaat niet.';
		}
	}
	public function getHTML(){
		if($this->boek instanceof Boek){
			$content=new Smarty_csr();
			$content->assign('boek', $this->boek);
			return $content->fetch('bibliotheek/boek.ubb.tpl');
		}else{
			return $this->boek;
		}
	}
	public function view(){
		echo $this->getHTML();
	}
}
?>
