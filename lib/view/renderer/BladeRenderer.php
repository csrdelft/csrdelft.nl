<?php
namespace CsrDelft\view\renderer;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\DependencyManager;
use eftec\bladeone\BladeOne;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/08/2018
 */
class BladeRenderer implements Renderer {
	const VIEWS = TEMPLATE_PATH;
	const CACHE = DATA_PATH . 'bladeOne/';

	private $bladeOne;
	private $data;
	private $template;

	public function __construct($template, $variables = []) {
		$this->bladeOne = new BladeOne(self::VIEWS, self::CACHE, BladeOne::MODE_AUTO);
		$this->data = $variables;

		$this->bladeOne->setInjectResolver(function ($className) {
			if (is_a($className, DependencyManager::class, true)) {
				/** @var $className DependencyManager */
				return $className::instance();
			} else {
				return new $className();
			}
		});

		$this->bladeOne->authCallBack = [LoginModel::class, 'mag'];

		$this->bladeOne->directive('cycle', function ($expr) {
			$numOptions = count(explode(',', $expr));

			$options = trim($expr, "()");

			return "<?php echo [$options][(\$loop->index) % $numOptions]; ?>";
		});
		$this->template = $template;
	}

	public function assign($field, $value) {
		$this->data[$field] = $value;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function render() {
		return $this->bladeOne->run($this->template, $this->data);
	}

	/**
	 * @throws \Exception
	 */
	public function display() {
		echo $this->render();
	}
}
