<?php


namespace CsrDelft\Twig\Extension;


use CsrDelft\entity\MenuItem;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\view\formulier\InstantSearchForm;
use CsrDelft\view\Icon;
use CsrDelft\view\login\LoginForm;
use CsrDelft\view\Zijbalk;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LayoutTwigExtension extends AbstractExtension
{
	/**
	 * @var Zijbalk
	 */
	private $zijbalk;
	/**
	 * @var MenuItemRepository
	 */
	private $menuItemRepository;

	public function __construct(Zijbalk $zijbalk, MenuItemRepository $menuItemRepository)
	{
		$this->zijbalk = $zijbalk;
		$this->menuItemRepository = $menuItemRepository;
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('csr_breadcrumbs', [$this, 'csr_breadcrumbs'], ['is_safe' => ['html']]),
			new TwigFunction('get_breadcrumbs', [$this, 'get_breadcrumbs']),
			new TwigFunction('get_menu', [$this, 'get_menu']),
			new TwigFunction('getMelding', 'getMelding', ['is_safe' => ['html']]),
			new TwigFunction('get_zijbalk', [$this, 'get_zijbalk'], ['is_safe' => ['html']]),
			new TwigFunction('instant_search_form', [$this, 'instant_search_form'], ['is_safe' => ['html']]),
			new TwigFunction('login_form', [$this, 'login_form'], ['is_safe' => ['html']]),
			new TwigFunction('icon', [$this, 'icon'], ['is_safe' => ['html']]),
		];
	}

	public function csr_breadcrumbs($breadcrumbs)
	{
		return $this->menuItemRepository->renderBreadcrumbs($breadcrumbs);
	}

	public function get_breadcrumbs($name)
	{
		return $this->menuItemRepository->getBreadcrumbs($name);
	}

	/**
	 * @param $name
	 * @param bool $root
	 * @return MenuItem
	 */
	public function get_menu($name, $root = false)
	{
		if ($root) {
			return $this->menuItemRepository->getMenuRoot($name);
		}

		return $this->menuItemRepository->getMenu($name);
	}

	public function instant_search_form()
	{
		return (new InstantSearchForm())->toString();
	}

	public function get_zijbalk()
	{
		return $this->zijbalk->getZijbalk();
	}

	public function login_form()
	{
		return (new LoginForm())->toString();
	}

	public function icon($key, $hover = null, $title = null, $class = null, $content = null)
	{
		return Icon::getTag($key, $hover, $title, $class, $content);
	}
}
