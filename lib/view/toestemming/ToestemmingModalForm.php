<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\LidToestemming;
use CsrDelft\model\instellingen\LidToestemmingModel;
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
	 * @var bool
	 */
	private $nieuw;

	/**
	 * @param bool $nieuw
	 * @throws CsrException
	 * @throws \SmartyException
	 */
	public function __construct($nieuw = false) {
		parent::__construct(new LidToestemming(), '/toestemming', 'Toestemming geven');

		$this->modalBreedte = 'modal-lg';
		$this->nieuw = $nieuw;

		$smarty = CsrSmarty::instance();
		$fields = [];

		$akkoord = null;

		$instellingen = LidToestemmingModel::instance()->getRelevantToestemmingCategories(LoginModel::getProfiel()->isLid());

		foreach ($instellingen as $module => $instelling) {
			foreach ($instelling as $id) {
				if (LidToestemmingModel::instance()->getValue($module, $id) == 'ja' && $akkoord == null) {
					$akkoord = 'ja';
				} elseif (LidToestemmingModel::instance()->getValue($module, $id) == 'nee') {
					$akkoord = 'nee';
				}

				$fields[] = $this->maakToestemmingLine($module, $id);
			}
		}

		$smarty->assign('beleid', instelling('privacy', 'beleid_kort'));
		$smarty->assign('beschrijvingBestuur', instelling('privacy', 'beschrijving_bestuur'));
		$smarty->assign('beschrijvingBijzonder', instelling('privacy', 'beschrijving_bijzonder'));
		$smarty->assign('beschrijvingVereniging', instelling('privacy', 'beschrijving_vereniging'));
		$smarty->assign('beschrijvingExternFoto', instelling('privacy', 'beschrijving_foto_extern'));
		$smarty->assign('beschrijvingInternFoto', instelling('privacy', 'beschrijving_foto_intern'));
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
	 * @throws \SmartyException|CsrException
	 */
	private function maakToestemmingLine($module, $id) {
		$model = LidToestemmingModel::instance();
		$smarty = CsrSmarty::instance();

		$eerdereWaarde = filter_input(INPUT_POST, $module . '_' . $id, FILTER_SANITIZE_STRING) ?? 'ja';

		$smarty->assign('module', $module);
		$smarty->assign('id', $id);
		$smarty->assign('type', $model->getType($module, $id));
		$smarty->assign('opties', $model->getTypeOptions($module, $id));
		$smarty->assign('label', $model->getDescription($module, $id));
		$smarty->assign('waarde', $this->nieuw ? $eerdereWaarde : $model->getValue($module, $id));
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
