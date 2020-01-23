<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\common\CsrException;
use CsrDelft\entity\LidToestemming;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\model\security\LoginModel;
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
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;

	/**
	 * @param LidToestemmingRepository $lidToestemmingRepository
	 * @param bool $nieuw
	 * @throws \Exception
	 */
	public function __construct(LidToestemmingRepository $lidToestemmingRepository, $nieuw = false) {
		parent::__construct(new LidToestemming(), '/toestemming', 'Toestemming geven');

		$this->modalBreedte = 'modal-lg';
		$this->lidToestemmingRepository = $lidToestemmingRepository;
		$this->nieuw = $nieuw;

		$fields = [];

		$akkoord = '';

		$instellingen = $lidToestemmingRepository->getRelevantToestemmingCategories(LoginModel::getProfiel()->isLid());

		foreach ($instellingen as $module => $instelling) {
			foreach ($instelling as $id) {
				if ($lidToestemmingRepository->getValue($module, $id) == 'ja' && $akkoord == null) {
					$akkoord = 'ja';
				} elseif ($lidToestemmingRepository->getValue($module, $id) == 'nee') {
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
	 * @throws CsrException
	 */
	private function maakToestemmingLine($module, $id) {

		$eerdereWaarde = filter_input(INPUT_POST, $module . '_' . $id, FILTER_SANITIZE_STRING) ?? 'ja';

		return new ToestemmingRegel(
			$module,
			$id,
			$this->lidToestemmingRepository->getType($module, $id),
			$this->lidToestemmingRepository->getTypeOptions($module, $id),
			$this->lidToestemmingRepository->getDescription($module, $id),
			$this->nieuw ? $eerdereWaarde : $this->lidToestemmingRepository->getValue($module, $id),
			$this->lidToestemmingRepository->getDefault($module, $id)
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
