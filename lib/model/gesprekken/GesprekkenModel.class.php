<?php
namespace CsrDelft\model\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\entity\security\Account;
use CsrDelft\Orm\PersistenceModel;
use function CsrDelft\getDateTime;

/**
 * GesprekkenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class GesprekkenModel extends PersistenceModel {

	const ORM = Gesprek::class;

	/** @var static */
	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'laatste_update DESC';

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
		$gesprek->gesprek_id = (int) $this->create($gesprek);
		// Deelnemers toevoegen
		$deelnemer = GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $from);
		GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $to);
		// Maak bericht
		GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $inhoud);
		return $gesprek;
	}

	/**
	 * @param Gesprek $gesprek
	 *
	 * @return int
	 */
	public function verwijderGesprek(Gesprek $gesprek) {
		GesprekBerichtenModel::instance()->verwijderBerichtenVoorGesprek($gesprek);
		return $this->delete($gesprek);
	}

}
