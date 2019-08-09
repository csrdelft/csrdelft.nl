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

		$akkoord = '';

		$instellingen = LidToestemmingModel::instance()->getRelevantToestemmingCategories(LoginModel::getProfiel()->isLid());

		foreach ($instellingen as $module => $instelling) {
			foreach ($instelling as $id) {
				if (LidToestemmingModel::instance()->getValue($module, $id) == 'ja' && $akkoord == null) {
					var_dump($module, $id);
					$akkoord = 'ja';
				} elseif (LidToestemmingModel::instance()->getValue($module, $id) == 'nee') {
					$akkoord = 'nee';
				}

				$fields[] = $this->maakToestemmingLine($module, $id);
			}
		}

		$this->addFields([
			new HtmlComment(view('toestemming.formulier', [
				'beleid' => instelling('privacy', 'beleid_kort'),
				'beschrijvingBestuur' => instelling('privacy', 'beschrijving_bestuur'),
				'beschrijvingBijzonder' => instelling('privacy', 'beschrijving_bijzonder'),
				'beschrijvingVereniging' => instelling('privacy', 'beschrijving_vereniging'),
				'beschrijvingExternFoto' => instelling('privacy', 'beschrijving_foto_extern'),
				'beschrijvingInternFoto' => instelling('privacy', 'beschrijving_foto_intern'),
				'akkoordExternFoto' => $this->maakToestemmingLine('algemeen', 'foto_extern'),
				'akkoordInternFoto' => $this->maakToestemmingLine('algemeen', 'foto_intern'),
				'akkoordVereniging' => $this->maakToestemmingLine('algemeen', 'vereniging'),
				'akkoordBijzonder' => $this->maakToestemmingLine('algemeen', 'bijzonder'),
				'akkoord' => $akkoord,
				'fields' => $fields,
			])->getHtml())
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

		$eerdereWaarde = filter_input(INPUT_POST, $module . '_' . $id, FILTER_SANITIZE_STRING) ?? 'ja';

		return new ToestemmingRegel(
			$module,
			$id,
			$model->getType($module, $id),
			$model->getTypeOptions($module, $id),
			$model->getDescription($module, $id),
			$this->nieuw ? $eerdereWaarde : $model->getValue($module, $id),
			$model->getDefault($module, $id)
		);
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
