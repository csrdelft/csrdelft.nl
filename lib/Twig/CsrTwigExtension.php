<?php


namespace CsrDelft\Twig {


	use CsrDelft\entity\MenuItem;
	use CsrDelft\entity\profiel\Profiel;
	use CsrDelft\repository\instellingen\InstellingenRepository;
	use CsrDelft\repository\instellingen\LidInstellingenRepository;
	use CsrDelft\repository\instellingen\LidToestemmingRepository;
	use CsrDelft\repository\MenuItemRepository;
	use CsrDelft\service\CsrfService;
	use CsrDelft\service\security\LoginService;
	use CsrDelft\view\formulier\CsrfField;
	use CsrDelft\view\formulier\InstantSearchForm;
	use CsrDelft\view\toestemming\ToestemmingModalForm;
	use Symfony\Component\HttpFoundation\Session\SessionInterface;
	use Twig\Extension\AbstractExtension;
	use Twig\TwigFilter;
	use Twig\TwigFunction;

	class CsrTwigExtension extends AbstractExtension {
		/**
		 * @var LidToestemmingRepository
		 */
		private $lidToestemmingRepository;
		/**
		 * @var LidInstellingenRepository
		 */
		private $lidInstellingenRepository;
		/**
		 * @var InstellingenRepository
		 */
		private $instellingenRepository;
		/**
		 * @var LoginService
		 */
		private $loginService;
		/**
		 * @var SessionInterface
		 */
		private $session;
		/**
		 * @var MenuItemRepository
		 */
		private $menuItemRepository;
		/**
		 * @var CsrfService
		 */
		private $csrfService;

		public function __construct(
			SessionInterface $session,
			LoginService $loginService,
			CsrfService $csrfService,
			MenuItemRepository $menuItemRepository,
			LidToestemmingRepository $lidToestemmingRepository,
			LidInstellingenRepository $lidInstellingenRepository,
			InstellingenRepository $instellingenRepository
		) {
			$this->lidToestemmingRepository = $lidToestemmingRepository;
			$this->lidInstellingenRepository = $lidInstellingenRepository;
			$this->instellingenRepository = $instellingenRepository;
			$this->loginService = $loginService;
			$this->session = $session;
			$this->menuItemRepository = $menuItemRepository;
			$this->csrfService = $csrfService;
		}

		public function getFunctions() {
			return [
				new TwigFunction('instelling', [$this, 'instelling']),
				new TwigFunction('lid_instelling', [$this, 'lid_instelling']),
				new TwigFunction('mag', [$this, 'mag']),
				new TwigFunction('css_asset', [$this, 'css_asset'], ['is_safe' => ['html']]),
				new TwigFunction('js_asset', [$this, 'js_asset'], ['is_safe' => ['html']]),
				new TwigFunction('dragobject_coords', [$this, 'dragobject_coords']),
				new TwigFunction('user_modules', [$this, 'getUserModules']),
				new TwigFunction('csr_breadcrumbs', [$this, 'csr_breadcrumbs']),
				new TwigFunction('get_breadcrumbs', [$this, 'get_breadcrumbs']),
				new TwigFunction('get_menu', [$this, 'get_menu']),
				new TwigFunction('commitHash', 'commitHash'),
				new TwigFunction('commitLink', 'commitLink'),
				new TwigFunction('toestemming_gegeven', [$this, 'toestemming_gegeven']),
				new TwigFunction('toestemming_form', [$this, 'toestemming_form']),
				new TwigFunction('csrfMetaTag', [$this, 'csrfMetaTag'], ['is_safe' => ['html']]),
				new TwigFunction('csrfField', [$this, 'csrfField'], ['is_safe' => ['html']]),
				new TwigFunction('getMelding', 'getMelding', ['is_safe' => ['html']]),
				new TwigFunction('get_zijbalk', 'get_zijbalk', ['is_safe' => ['html']]),
				new TwigFunction('vereniging_leeftijd', 'vereniging_leeftijd'),
				new TwigFunction('login_form', 'login_form', ['is_safe' => ['html']]),
				new TwigFunction('icon', 'icon', ['is_safe' => ['html']]),
				new TwigFunction('instant_search_form', [$this, 'instant_search_form'], ['is_safe' => ['html']]),
			];
		}

		public function toestemming_gegeven() {
			return $this->lidToestemmingRepository->toestemmingGegeven();
		}

		public function toestemming_form() {
			return new ToestemmingModalForm($this->lidToestemmingRepository);
		}

		public function csrfField($path = '', $method = 'post') {
			return (new CsrfField($this->csrfService->generateToken($path, $method)))->toString();
		}

		function csrfMetaTag() {
			$token = $this->csrfService->generateToken('', 'POST');
			return '<meta property="X-CSRF-ID" content="' . htmlentities($token->getId()) . '" /><meta property="X-CSRF-VALUE" content="' . htmlentities($token->getValue()) . '" />';
		}


		public function getFilters() {
			return [
				new TwigFilter('escape_ical', 'escape_ical'),
				new TwigFilter('is_zichtbaar', [$this, 'is_zichtbaar']),
				new TwigFilter('file_base64', 'file_base64'),
			];
		}

		/**
		 * @param Profiel $profiel
		 * @param string|string[] $key
		 * @param string $cat
		 * @param string $uitzondering Sommige commissie mogen wel dit veld zien.
		 * @return bool
		 */
		public function is_zichtbaar($profiel, $key, $cat = 'profiel', $uitzondering = P_LEDEN_MOD) {
			if (is_array($key)) {
				foreach ($key as $item) {
					if (!$this->lidToestemmingRepository->toestemming($profiel, $item, $cat, $uitzondering)) {
						return false;
					}
				}

				return true;
			}

			return $this->lidToestemmingRepository->toestemming($profiel, $key, $cat, $uitzondering);
		}

		public function lid_instelling($module, $key) {
			return $this->lidInstellingenRepository->getValue($module, $key);
		}

		public function instelling($module, $key) {
			return $this->instellingenRepository->getValue($module, $key);
		}

		/**
		 * Mag de op dit moment ingelogde gebruiker $permissie?
		 *
		 * Korte methode voor gebruik in Blade templates.
		 *
		 * @param string $permission
		 * @param array|null $allowedAuthenticationMethods
		 * @return bool
		 */
		public function mag($permission, array $allowedAuthenticationMethods = null) {
			return $this->loginService->_mag($permission, $allowedAuthenticationMethods);
		}

		/**
		 * Genereer een unieke url voor een asset.
		 *
		 * @param string $asset
		 * @return string
		 */
		public function css_asset(string $module, $media = null) {
			$assetString = '';

			foreach (module_asset($module, 'css') as $asset) {
				if ($media) {
					$assetString .= "<link rel=\"stylesheet\" href=\"{$asset}\" type=\"text/css\" media=\"{$media}\"/>\n";
				} else {
					$assetString .= "<link rel=\"stylesheet\" href=\"{$asset}\" type=\"text/css\"/>\n";
				}
			}

			return $assetString;
		}

		public function js_asset(string $module) {
			$assetString = '';

			foreach (module_asset($module, 'js') as $asset) {
				$assetString .= "<script type=\"text/javascript\" src=\"{$asset}\"></script>\n";
			}

			return $assetString;
		}

		public function dragobject_coords($id, $top, $left) {
			if ($this->session->has("dragobject_$id")) {
				$dragObject = $this->session->get("dragobject_$id");
				$top = (int)$dragObject['top'];
				$left = (int)$dragObject['left'];
			}

			$top = max($top, 0);
			$left = max($left, 0);
			return ['top' => $top, 'left' => $left];
		}

		/**
		 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
		 * De modules zijn terug te vinden in /resources/assets/sass
		 *
		 * @return array
		 */
		public function getUserModules() {
			$modules = [];

			if (!LoginService::mag(P_LOGGED_IN)) {
				return [];
			}

			//voeg modules toe afhankelijk van instelling
			$modules[] = 'thema-' . lid_instelling('layout', 'opmaak');

			// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht

			if (lid_instelling('layout', 'toegankelijk') == 'bredere letters') {
				$modules[] = 'bredeletters';
			}
			if (lid_instelling('layout', 'fx') == 'civisaldo') {
				$modules[] = 'effect-civisaldo';
			}

			return $modules;
		}

		function csr_breadcrumbs($breadcrumbs) {
			return $this->menuItemRepository->renderBreadcrumbs($breadcrumbs);
		}

		function get_breadcrumbs($name) {
			return $this->menuItemRepository->getBreadcrumbs($name);
		}

		/**
		 * @param $name
		 * @param bool $root
		 * @return MenuItem
		 */
		function get_menu($name, $root = false) {
			if ($root) {
				return $this->menuItemRepository->getMenuRoot($name);
			}

			return $this->menuItemRepository->getMenu($name);
		}

		public function instant_search_form() {
			return (new InstantSearchForm())->toString();
		}
	}
}

namespace {

	use CsrDelft\view\Icon;
	use CsrDelft\view\login\LoginForm;
	use CsrDelft\view\Zijbalk;

	function file_base64($filename) {
		if (file_exists($filename)) {
			return base64_encode(file_get_contents($filename));
		}
		return '';
	}

	function get_zijbalk() {
		return Zijbalk::addStandaardZijbalk([]);
	}

	function login_form() {
		return (new LoginForm())->toString();
	}

	function icon($name) {
		return Icon::getTag($name);
	}
}
