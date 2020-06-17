<?php
namespace CsrDelft\view\renderer;
use CsrDelft\common\ContainerFacade;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\Icon;
use eftec\bladeone\BladeOne;
use Exception;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/08/2018
 */
class BladeRenderer implements Renderer {
	private $bladeOne;
	private $data;
	private $template;

	public function __construct($template, $variables = []) {
		$this->bladeOne = new CustomBladeOne(TEMPLATE_PATH, BLADE_CACHE_PATH, BladeOne::MODE_AUTO);
		$this->data = $variables;

		// Specifieke code voor development / Travis
		if (MODE !== 'TRAVIS') {
			$this->bladeOne->setInjectResolver(function ($className) {
				try {
					return ContainerFacade::getContainer()->get($className);
				} catch (Exception $e) {
					return new $className();
				}
			});

			// @auth en @guest maken puur onderscheid tussen ingelogd of niet.
			if (LoginService::mag(P_LOGGED_IN)) {
				$this->bladeOne->setAuth(LoginService::getUid());
			}
			$this->bladeOne->authCallBack = [LoginService::class, 'mag'];

			$this->bladeOne->directive('stylesheet', function ($expr) {
				return '<link rel="stylesheet" href="<?php echo asset' . $expr . '; ?>" type="text/css"/>';
			});
			$this->bladeOne->directive('script', function ($expr) {
				return '<?php echo js_asset' . $expr . '; ?>';
				return '<script type="text/javascript" src="<?php echo asset' . $expr . '?>"></script>';
			});
		} else {
			// In productie wordt de stylesheet in de html gehangen,
			// in andere modi wordt een aanroep naar asset gedaan.
			$this->bladeOne->directive('stylesheet', function ($expr) {
				$asset = trim(trim($expr, "()"), "\"'");
				return '<link rel="stylesheet" href="' . asset($asset) . '" type="text/css"/>';
			});
			$this->bladeOne->directive('script', function ($expr) {
				$asset = trim(trim($expr, "()"), "\"'");
				return '<script type="text/javascript" src="' . asset($asset) . '"></script>';
			});
		}

		$this->bladeOne->directive('icon', function ($expr) {
			$options = trim($expr, "()");

			$iconClassName = Icon::class;
			return "<?php echo call_user_func_array(['{$iconClassName}', 'getTag'], [$options]); ?>";
		});

		$this->bladeOne->directive('cycle', function ($expr) use ($template) {
			$this->cycleCount = isset($this->cycleCount) ? $this->cycleCount : 0;

			$numOptions = count(explode(',', $expr));
			$options = trim($expr, "()");

			$cycleCount = str_replace(".", "_", $template) . $this->cycleCount++;

			// Create the variable if it does not exist.
			return "<?php \$this->cycle_$cycleCount = @\$this->cycle_$cycleCount; echo [$options][(\$this->cycle_$cycleCount++) % $numOptions]; ?>";
		});

		$this->bladeOne->directive('link', function ($expr) {
			return "<?php echo link_for$expr; ?>";
		});

		$this->template = $template;
	}

	public function assign($field, $value) {
		$this->data[$field] = $value;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function render() {
		return $this->bladeOne->run($this->template, $this->data);
	}

	/**
	 * @throws Exception
	 */
	public function display() {
		echo $this->render();
	}

	/**
	 * @throws Exception
	 */
	public function compile() {
		$this->bladeOne->compile($this->template, true);
	}
}
