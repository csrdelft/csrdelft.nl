<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/corveeinstellingen.class.php
# -------------------------------------------------------------------
# Instellingen bekijken en aanpassen
# -------------------------------------------------------------------


require_once ('maaltijden/maaltrack.class.php');

class CorveeinstellingenContent extends SimpleHTML {
	private $corveeinstellingen;

	public function __construct($corveeinstellingen) {
		$this->corveeinstellingen=$corveeinstellingen;
	}

	public function getTitel(){ return 'Maaltijdketzer - corveeinstellingen'; }

	private function getDatumveld(){
		$years=range(date('Y')-3, date('Y')+3);
		$mounths=range(1,12);
		$days=range(1,31);

		$html = '<select id="field_eindcorveeperiode" name="eindcorveeperiode_jaar" />';
		foreach($years as $value){
			$html .= '<option value="'.$value.'">'.$value.'</option>';
		}
		$html .= '</select>&nbsp;';

		$html .= '<select id="field_eindcorveeperiode" name="eindcorveeperiode_maand" />';
		foreach($mounths as $value){
			$value=sprintf('%02d', $value);
			$html .= '<option value="'.$value.'">'.$value.'</option>';
		}
		$html .= '</select>&nbsp;';

		$html .= '<select id="field_eindcorveeperiode" name="eindcorveeperiode_dag" />';
		foreach($days as $value){
			$value=sprintf('%02d', $value);
			$html .= '<option value="'.$value.'">'.$value.'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	public function view(){
		$loginlid=LoginLid::instance();

		//de html template in elkaar draaien en weergeven
		$pagina=new Smarty_csr();


		//arrays toewijzen en weergeven
		$pagina->assign('instellingen', $this->corveeinstellingen);
		$pagina->assign('datumveld', $this->getDatumveld());
		$pagina->assign('melding', $this->getMelding());
		$pagina->display('maaltijdketzer/corveeinstellingen.tpl');
	}
}

class CorveeresetterContent extends SimpleHTML {
	private $corveeresetter;
	private $action;

	public function __construct($corveeresetter) {
		$this->corveeresetter=$corveeresetter;
	}
	public function setAction($action){
		$this->action=$action;
	}
	public function view(){
		$pagina=new Smarty_csr();

		$pagina->assign('actie', $this->action);
		$pagina->assign('datum', $this->corveeresetter->getDatum());
		$pagina->assign('alletaken', Maaltrack::getAlleTaken($this->corveeresetter->getDatum(), $status='onbekend'));
		$pagina->assign('melding', $this->getMelding());
		$pagina->assign('meldingresetter', $this->corveeresetter->getMelding());

		$pagina->display('maaltijdketzer/corveeresetformulier.tpl');
	}
}

?>
