<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\model\entity\LidToestemming;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\LidToestemmingModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrSmarty;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 */
class ToestemmingModalForm extends ModalForm {
	/**
	 * @throws \SmartyException
	 */
	public function __construct() {
		parent::__construct(new LidToestemming(), '/toestemming', 'Toestemming geven');

		$this->modalBreedte = 'modal-lg';

		$smarty = CsrSmarty::instance();
		$model = LidToestemmingModel::instance();
		$fields = [];

		$akkoord = null;

		$instellingen = $model->getRelevantToestemmingCategories(LoginModel::getProfiel()->isLid());

		foreach ($instellingen as $module => $instelling) {
			foreach ($instelling as $id) {
				if ($model->getValue($module, $id) == 'ja' && $akkoord == null) {
					$akkoord = 'ja';
				} elseif ($model->getValue($module, $id) == 'nee') {
					$akkoord = 'nee';
				}

				$fields[] = $this->maakToestemmingLine($module, $id);
			}
		}

		$smarty->assign('beleid', InstellingenModel::get('privacy', 'beleid_kort'));
		$smarty->assign('beschrijvingBestuur', InstellingenModel::get('privacy', 'beschrijving_bestuur'));
		$smarty->assign('beschrijvingBijzonder', InstellingenModel::get('privacy', 'beschrijving_bijzonder'));
		$smarty->assign('beschrijvingVereniging', InstellingenModel::get('privacy', 'beschrijving_vereniging'));
		$smarty->assign('beschrijvingExternFoto', InstellingenModel::get('privacy', 'beschrijving_foto_extern'));
		$smarty->assign('beschrijvingInternFoto', InstellingenModel::get('privacy', 'beschrijving_foto_intern'));
		$smarty->assign('akkoordExternFoto', $this->maakToestemmingLine('algemeen', 'foto_extern'));
		$smarty->assign('akkoordInternFoto', $this->maakToestemmingLine('algemeen', 'foto_intern'));
		$smarty->assign('akkoordVereniging', $this->maakToestemmingLine('algemeen', 'vereniging'));
		$smarty->assign('akkoordBijzonder', $this->maakToestemmingLine('algemeen', 'bijzonder'));
		$smarty->assign('akkoord', $akkoord);
		$smarty->assign('fields', $fields);
		$this->addFields([
			new HtmlComment($smarty->fetch('toestemming/toestemming_head.tpl')),
		]);

		$this->formKnoppen = new FormDefaultKnoppen('/toestemming/annuleren', false);
	}

	/**
	 * @param string $module
	 * @param string $id
	 * @return string
	 * @throws \SmartyException
	 */
	private function maakToestemmingLine($module, $id) {
		$model = LidToestemmingModel::instance();
		$smarty = CsrSmarty::instance();

		$smarty->assign('module', $module);
		$smarty->assign('id', $id);
		$smarty->assign('type', $model->getType($module, $id));
		$smarty->assign('opties', $model->getTypeOptions($module, $id));
		$smarty->assign('label', $model->getDescription($module, $id));
		$smarty->assign('waarde', $model->getValue($module, $id));
		$smarty->assign('default', $model->getDefault($module, $id));

		return $smarty->fetch('toestemming/toestemming_input.tpl');
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}

		$toestemming = filter_input(INPUT_POST, 'toestemming-intern', FILTER_VALIDATE_BOOLEAN);

		if ($toestemming) {
			return true;
		}

		setMelding('Maak een keuze', -1);
		return false;
	}
}
