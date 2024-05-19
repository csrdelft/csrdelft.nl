<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\LidToestemming;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use Exception;
use Twig\Environment;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 */
class ToestemmingModalForm extends ModalForm
{
	/**
	 * @param LidToestemmingRepository $lidToestemmingRepository
	 * @param bool $nieuw
	 * @throws Exception
	 */
	public function __construct(
		private readonly LidToestemmingRepository $lidToestemmingRepository,
		private $nieuw = false
	) {
		parent::__construct(
			new LidToestemming(),
			'/toestemming',
			'Toestemming geven'
		);

		$this->modalBreedte = 'modal-lg';

		$fields = [];

		$akkoord = '';

		$instellingen = $this->lidToestemmingRepository->getRelevantToestemmingCategories(
			LoginService::getProfiel()->isLid()
		);

		foreach ($instellingen as $module => $instelling) {
			foreach ($instelling as $id) {
				if (
					$this->lidToestemmingRepository->getValue($module, $id) == 'ja' &&
					$akkoord == null
				) {
					$akkoord = 'ja';
				} elseif (
					$this->lidToestemmingRepository->getValue($module, $id) == 'nee'
				) {
					$akkoord = 'nee';
				}

				$fields[] = $this->maakToestemmingLine($module, $id);
			}
		}

<<<<<<< HEAD
		$twig = ContainerFacade::getContainer()->get('csr.hack.twig');
=======
		$twig = ContainerFacade::getContainer()->get(Environment::class);
>>>>>>> 293c8a774 (Fix meer problemen)

		$this->addFields([
			new HtmlComment(
				$twig->render('toestemming/formulier.html.twig', [
					'akkoordExternFoto' => $this->maakToestemmingLine(
						'algemeen',
						'foto_extern'
					),
					'akkoordInternFoto' => $this->maakToestemmingLine(
						'algemeen',
						'foto_intern'
					),
					'akkoordVereniging' => $this->maakToestemmingLine(
						'algemeen',
						'vereniging'
					),
					'akkoordBijzonder' => $this->maakToestemmingLine(
						'algemeen',
						'bijzonder'
					),
					'akkoord' => $akkoord,
					'fields' => $fields,
				])
			),
		]);

		$this->formKnoppen = new FormDefaultKnoppen(
			'/toestemming/annuleren',
			false
		);
	}

	/**
	 * @param string $module
	 * @param string $id
	 * @return ToestemmingRegel
	 */
	private function maakToestemmingLine(
		string $module,
		string $id
	): ToestemmingRegel {
		$eerdereWaarde =
			filter_input(INPUT_POST, $module . '_' . $id, FILTER_SANITIZE_STRING) ??
			'ja';

		return new ToestemmingRegel(
			$module,
			$id,
			$this->lidToestemmingRepository->getType($module, $id),
			$this->lidToestemmingRepository->getTypeOptions($module, $id),
			$this->lidToestemmingRepository->getDescription($module, $id),
			$this->nieuw
				? $eerdereWaarde
				: $this->lidToestemmingRepository->getValue($module, $id),
			$this->lidToestemmingRepository->getDefault($module, $id)
		);
	}

	public function validate()
	{
		if (!parent::validate()) {
			return false;
		}

		$toestemming = filter_input(
			INPUT_POST,
			'toestemming-intern',
			FILTER_VALIDATE_BOOLEAN
		);

		if ($toestemming) {
			return true;
		}

		FlashUtil::setFlashWithContainerFacade('Maak een keuze', -1);
		return false;
	}
}
