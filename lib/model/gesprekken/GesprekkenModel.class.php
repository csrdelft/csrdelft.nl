<?php

namespace CsrDelft\model\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\entity\security\Account;
use CsrDelft\Orm\PersistenceModel;

/**
 * GesprekkenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class GesprekkenModel extends PersistenceModel {

	const ORM = Gesprek::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'laatste_update DESC';

	/**
	 * @var GesprekBerichtenModel
	 */
	private $gesprekBerichtenModel;

	/**
	 * @var GesprekDeelnemersModel
	 */
	private $gesprekDeelnemersModel;

	/**
	 * GesprekkenModel constructor.
	 * @param GesprekBerichtenModel $gesprekBerichtenModel
	 * @param GesprekDeelnemersModel $gesprekDeelnemersModel
	 */
	protected function __construct(
		GesprekBerichtenModel $gesprekBerichtenModel,
		GesprekDeelnemersModel $gesprekDeelnemersModel
	) {
		parent::__construct();

		$this->gesprekBerichtenModel = $gesprekBerichtenModel;
		$this->gesprekDeelnemersModel = $gesprekDeelnemersModel;
	}

	/**
	 * @param $gesprek_id
	 *
	 * @return Gesprek|false
	 */
	public static function get($gesprek_id) {
		return static::instance()->retrieveByPrimaryKey(array($gesprek_id));
	}

	/**
	 * @param Account $from
	 * @param Account $to
	 * @param string $inhoud
	 *
	 * @return Gesprek
	 */
	public function startGesprek(Account $from, Account $to, $inhoud) {
		// Maak gesprek
		$gesprek = new Gesprek();
		$gesprek->laatste_update = getDateTime();
		$gesprek->gesprek_id = (int)$this->create($gesprek);
		// Deelnemers toevoegen
		$deelnemer = $this->gesprekDeelnemersModel->voegToeAanGesprek($gesprek, $from);
		$this->gesprekDeelnemersModel->voegToeAanGesprek($gesprek, $to);
		// Maak bericht
		$this->gesprekBerichtenModel->maakBericht($gesprek, $deelnemer, $inhoud);
		return $gesprek;
	}

	/**
	 * @param Gesprek $gesprek
	 *
	 * @return int
	 */
	public function verwijderGesprek(Gesprek $gesprek) {
		$this->gesprekBerichtenModel->verwijderBerichtenVoorGesprek($gesprek);
		return $this->delete($gesprek);
	}

}
