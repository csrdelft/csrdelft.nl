<?php
namespace CsrDelft\view\mededelingen;
use CsrDelft\model\entity\mededelingen\Mededeling;
use CsrDelft\model\mededelingen\MededelingenModel;
use CsrDelft\view\SmartyTemplateView;
use DateTime;
use function CsrDelft\getDateTime;

/**
 * Class MededelingView
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class MededelingView extends SmartyTemplateView {

	private $prullenbak;
	/**
	 * @var MededelingenModel
	 */
	protected $model;

	/**
	 * @var Mededeling
	 */
	private $mededeling;


	public function __construct(Mededeling $mededeling, $prullenbak = false) {
		parent::__construct(MededelingenModel::instance(), 'Mededelingen');
		$this->prullenbak = $prullenbak;
		$this->mededeling = $mededeling;

		$this->smarty->assign('prullenbak', $this->prullenbak);
	}

	public function getBreadcrumbs() {
		$breadcrumb = parent::getBreadcrumbs() . '<a href="/mededelingen">Mededelingen</a> » ';
		if ($this->mededeling->id) {
			$breadcrumb .= '<a href="/mededelingen/'.$this->mededeling->id.'">' . $this->mededeling->titel . '</a> » <span class="active">Bewerken</span>';
		} else {
			$breadcrumb .= '<span class="active">Toevoegen</span>';
		}
		return $breadcrumb;
	}

	public function view() {
		$this->smarty->assign('mededeling', $this->mededeling);
		$this->smarty->assign('prioriteiten', MededelingenModel::getPrioriteiten());
		$this->smarty->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		// Een standaard vervaltijd verzinnen indien nodig.
		if ($this->mededeling->vervaltijd === null) {
			$standaardVervaltijd = new DateTime(getDateTime());
			$standaardVervaltijd = $standaardVervaltijd->format('Y-m-d 23:59');
			$this->smarty->assign('standaardVervaltijd', $standaardVervaltijd);
		}
		$this->smarty->display('mededelingen/mededeling.tpl');
	}

}
