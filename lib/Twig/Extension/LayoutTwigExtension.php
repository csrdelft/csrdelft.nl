<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\Component\Formulier\FormulierFactory;
use CsrDelft\entity\MenuItem;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\view\formulier\InstantSearchForm;
use CsrDelft\view\Icon;
use CsrDelft\view\login\LoginForm;
use CsrDelft\view\Voorpagina;
use CsrDelft\view\Zijbalk;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LayoutTwigExtension extends AbstractExtension
{
	/**
	 * @var Voorpagina
	 */
	private $voorpagina;
	/**
	 * @var MenuItemRepository
	 */
	private $menuItemRepository;
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var FormulierFactory
	 */
	private $formulierFactory;

	public function __construct(
		RequestStack $requestStack,
		Voorpagina $voorpagina,
		MenuItemRepository $menuItemRepository,
		FormulierFactory $formulierFactory
	) {
		$this->voorpagina = $voorpagina;
		$this->menuItemRepository = $menuItemRepository;
		$this->requestStack = $requestStack;
		$this->formulierFactory = $formulierFactory;
	}

	public function getFunctions()
	{
		return [
			new TwigFunction(
				'csr_breadcrumbs',
				[$this, 'csr_breadcrumbs'],
				['is_safe' => ['html']]
			),
			new TwigFunction('get_breadcrumbs', [$this, 'get_breadcrumbs']),
			new TwigFunction('get_menu', [$this, 'get_menu']),
			new TwigFunction('getMelding', 'getMelding', ['is_safe' => ['html']]),
			new TwigFunction(
				'instant_search_form',
				[$this, 'instant_search_form'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'login_form',
				[$this, 'login_form'],
				['is_safe' => ['html']]
			),
			new TwigFunction('icon', [$this, 'icon'], ['is_safe' => ['html']]),
			new TwigFunction(
				'get_agenda',
				[$this, 'get_agenda'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_forum',
				[$this, 'get_forum'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_posters',
				[$this, 'get_posters'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_fotoalbum',
				[$this, 'get_fotoalbum'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_civisaldo',
				[$this, 'get_civisaldo'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_ishetal',
				[$this, 'get_ishetal'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_verjaardagen',
				[$this, 'get_verjaardagen'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_overig',
				[$this, 'get_overig'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'get_civisaldo',
				[$this, 'get_civisaldo'],
				['is_safe' => ['html']]
			),
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
		$defaultName = $name;
		$locale = $this->requestStack->getCurrentRequest()->getLocale();
		if (
			$locale != $this->requestStack->getCurrentRequest()->getDefaultLocale()
		) {
			$name = $name . '_' . $locale;
		}
		if ($root) {
			return $this->menuItemRepository->getMenuRoot($name) ??
				$this->menuItemRepository->getMenuRoot($defaultName);
		}

		return $this->menuItemRepository->getMenu($name) ??
			$this->menuItemRepository->getMenu($defaultName);
	}

	public function instant_search_form()
	{
		return (new InstantSearchForm())->__toString();
	}

	public function login_form()
	{
		return $this->formulierFactory
			->create(LoginForm::class, null, [])
			->createView()
			->__toString();
	}

	public function icon(
		$key,
		$hover = null,
		$title = null,
		$class = null,
		$content = null
	) {
		return Icon::getTag($key, $hover, $title, $class, $content);
	}

	public function get_agenda(): ?string
	{
		return $this->voorpagina->getAgenda();
	}

	public function get_forum(): ?string
	{
		return $this->voorpagina->getForum();
	}

	public function get_posters(): ?string
	{
		return $this->voorpagina->getPosters();
	}

	public function get_fotoalbum(): ?string
	{
		return $this->voorpagina->getFotoalbum();
	}

	public function get_civisaldo(): ?string
	{
		return $this->voorpagina->getCivisaldo();
	}

	public function get_ishetal(): ?string
	{
		return $this->voorpagina->getIsHetAl();
	}

	public function get_verjaardagen(): ?string
	{
		return $this->voorpagina->getVerjaardagen();
	}

	public function get_overig(): ?string
	{
		return $this->voorpagina->getOverig();
	}
}
