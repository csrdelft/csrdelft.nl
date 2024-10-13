<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\Component\Formulier\FormulierFactory;
use CsrDelft\entity\MenuItem;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\view\formulier\InstantSearchForm;
use CsrDelft\view\Icon;
use CsrDelft\view\login\LoginForm;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LayoutTwigExtension extends AbstractExtension
{
	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly MenuItemRepository $menuItemRepository,
		private readonly FormulierFactory $formulierFactory
	) {
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('csr_breadcrumbs', $this->csr_breadcrumbs(...), [
				'is_safe' => ['html'],
			]),
			new TwigFunction('get_breadcrumbs', $this->get_breadcrumbs(...)),
			new TwigFunction('get_menu', $this->get_menu(...)),
			new TwigFunction('instant_search_form', $this->instant_search_form(...), [
				'is_safe' => ['html'],
			]),
			new TwigFunction('login_form', $this->login_form(...), [
				'is_safe' => ['html'],
			]),
			new TwigFunction('icon', $this->icon(...), ['is_safe' => ['html']]),
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
		return Icon::getTag($key, $hover, $title, $class);
	}
}
