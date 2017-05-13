<?php
namespace CsrDelft\model\gesprekken;
use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\entity\security\Account;
use CsrDelft\model\gesprekken;
use CsrDelft\Orm\PersistenceModel;
use function CsrDelft\getDateTime;

/**
 * GesprekkenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekkenModel extends PersistenceModel {

	const ORM = Gesprek::class;
	const DIR = 'gesprekken/';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'laatste_update DESC';

	public static function get($gesprek_id) {
		return static::instance()->retrieveByPrimaryKey(array($gesprek_id));
	}

	public function startGesprek(Account $from, Account $to, $inhoud) {
		// Maak gesprek
		$gesprek = new Gesprek();
		$gesprek->laatste_update = getDateTime();
		$gesprek->gesprek_id = (int) $this->create($gesprek);
		// Deelnemers toevoegen
		$deelnemer = gesprekken\GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $from);
		gesprekken\GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $to);
		// Maak bericht
		gesprekken\GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $inhoud);
		return $gesprek;
	}

	public function verwijderGesprek(Gesprek $gesprek) {
		gesprekken\GesprekBerichtenModel::instance()->verwijderBerichtenVoorGesprek($gesprek);
		return $this->delete($gesprek);
	}

}
