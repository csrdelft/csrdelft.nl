<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\common\Security\Voter\Entity\CmsPaginaVoter;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\BedragUtil;
use CsrDelft\common\Util\CryptoUtil;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\common\Util\TextUtil;
use CsrDelft\common\Util\VueUtil;
use CsrDelft\Component\DataTable\DataTableView;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\CsrfService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\groepen\formulier\GroepBewerkenForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class CsrTwigExtension extends AbstractExtension
{
	public function __construct(
		private readonly CsrfService $csrfService,
		private readonly Security $security,
		private readonly CmsPaginaRepository $cmsPaginaRepository,
		private readonly ProfielRepository $profielRepository,
		private readonly MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		private readonly MaaltijdBeoordelingenRepository $maaltijdBeoordelingenRepository
	) {
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('dragobject_coords', $this->dragobject_coords(...)),
			new TwigFunction('commitHash', $this->commitHash(...)),
			new TwigFunction('commitLink', $this->commitLink(...)),
			new TwigFunction('csrfMetaTag', $this->csrfMetaTag(...), [
				'is_safe' => ['html'],
			]),
			new TwigFunction('csrfField', $this->csrfField(...), [
				'is_safe' => ['html'],
			]),
			new TwigFunction('vereniging_leeftijd', $this->vereniging_leeftijd(...)),
			new TwigFunction('get_profiel', $this->get_profiel(...)),
			new TwigFunction(
				'get_maaltijd_aanmelding',
				$this->get_maaltijd_aanmelding(...)
			),
			new TwigFunction(
				'get_maaltijd_beoordeling',
				$this->get_maaltijd_beoordeling(...)
			),
			new TwigFunction('huidige_jaargang', $this->huidige_jaargang(...)),
			new TwigFunction('gethostbyaddr', 'gethostbyaddr'),
			new TwigFunction('cms', $this->cms(...), ['is_safe' => ['html']]),
			new TwigFunction('table', $this->table(...), ['is_safe' => ['html']]),
			new TwigFunction('groep_bewerken_form', $this->groepBewerkenForm(...), [
				'is_safe' => ['html'],
			]),
			new TwigFunction('vue', VueUtil::vueComponent(...), [
				'is_safe' => ['html'],
			]),
		];
	}

	public function groepBewerkenForm($lid, $groep): GroepBewerkenForm
	{
		return new GroepBewerkenForm($lid, $groep);
	}

	public function huidige_jaargang(): string
	{
		return LichtingenRepository::getHuidigeJaargang();
	}

	public function get_profiel($uid)
	{
		return $this->profielRepository->find($uid);
	}

	public function get_maaltijd_aanmelding($maaltijd_id)
	{
		return $this->maaltijdAanmeldingenRepository->find([
			'maaltijd_id' => $maaltijd_id,
			'uid' => LoginService::getUid(),
		]);
	}

	public function get_maaltijd_beoordeling($maaltijd)
	{
		$beoordeling = $this->maaltijdBeoordelingenRepository->find([
			'maaltijd_id' => $maaltijd->maaltijd_id,
			'uid' => LoginService::getUid(),
		]);
		if (!$beoordeling) {
			$beoordeling = $this->maaltijdBeoordelingenRepository->nieuw($maaltijd);
		}
		$kwantiteit = (new MaaltijdKwantiteitBeoordelingForm(
			$maaltijd,
			$beoordeling
		))->getHtml();
		$kwaliteit = (new MaaltijdKwaliteitBeoordelingForm(
			$maaltijd,
			$beoordeling
		))->getHtml();

		return [
			'kwaliteit' => $kwaliteit,
			'kwantiteit' => $kwantiteit,
		];
	}

	public function csrfField($path = '', $method = 'post')
	{
		return (new CsrfField(
			$this->csrfService->generateToken($path, $method)
		))->__toString();
	}

	public function csrfMetaTag()
	{
		$token = $this->csrfService->generateToken('', 'POST');
		return '<meta property="X-CSRF-ID" content="' .
			htmlentities($token->getId()) .
			'" /><meta property="X-CSRF-VALUE" content="' .
			htmlentities($token->getValue()) .
			'" />';
	}

	public function cms($id)
	{
		$pagina = $this->cmsPaginaRepository->find($id);

		if (!$pagina) {
			return '<div class="alert alert-danger">Gedeelte van de pagina met naam "' .
				htmlspecialchars((string) $id) .
				'" niet gevonden.</div>';
		}

		if ($this->security->isGranted(CmsPaginaVoter::BEKIJKEN, $pagina)) {
			return CsrBB::parseHtml($pagina->inhoud, $pagina->inlineHtml);
		}

		return '';
	}

	public function getFilters(): array
	{
		return [
			new TwigFilter('escape_ical', TextUtil::escape_ical(...)),
			new TwigFilter('file_base64', $this->file_base64(...)),
			new TwigFilter('bbcode', $this->bbcode(...), ['is_safe' => ['html']]),
			new TwigFilter('uniqid', fn($prefix) => CryptoUtil::uniqid_safe($prefix)),
			new TwigFilter('format_bedrag', BedragUtil::format_bedrag(...)),
			new TwigFilter('format_euro', BedragUtil::format_euro(...)),
			new TwigFilter('truncate', TextUtil::truncate(...)),
			new TwigFilter('format_filesize', FileUtil::format_filesize(...)),
			new TwigFilter('shuffle', ArrayUtil::array_shuffle(...)),
			new TwigFilter('pluralize', $this->pluralize(...)),
		];
	}

	public function pluralize(
		int $count,
		string $singular,
		string $plural,
		string $zero = null
	): string {
		if ($count > 1) {
			return str_replace('{}', $count, $plural);
		} elseif ($count <= 0 && null !== $zero) {
			return $zero; // No string replacement required for zero
		}
		return str_replace('{}', $count, $singular);
	}

	public function getTests(): array
	{
		/**
		 * @param Agendeerbaar $value
		 * @return bool
		 */
		/**
		 * @param Profiel $value
		 * @return bool
		 */
		return [
			new TwigTest('numeric', fn($value) => is_numeric($value)),
			new TwigTest('profiel', fn($value) => $value instanceof Profiel),
			new TwigTest('corveetaak', fn($value) => $value instanceof CorveeTaak),
			new TwigTest('maaltijd', fn($value) => $value instanceof Maaltijd),
			new TwigTest(
				'agendeerbaar',
				fn($value) => $value instanceof Agendeerbaar
			),
			new TwigTest('abstractgroep', fn($value) => $value instanceof Groep),
			new TwigTest('agendaitem', fn($value) => $value instanceof AgendaItem),
			new TwigTest('verticale', fn($value) => $value instanceof Verticale),
			new TwigTest(
				'heeftaanmeldlimiet',
				fn($value) => $value instanceof HeeftAanmeldLimiet
			),
			new TwigTest(
				'heeftaanmeldmoment',
				fn($value) => $value instanceof HeeftAanmeldMoment
			),
		];
	}

	public function dragobject_coords(SessionInterface $session, $id, $top, $left)
	{
		if ($session->has("dragobject_$id")) {
			$dragObject = $session->get("dragobject_$id");
			$top = (int) $dragObject['top'];
			$left = (int) $dragObject['left'];
		}

		$top = max($top, 0);
		$left = max($left, 0);
		return ['top' => $top, 'left' => $left];
	}

	public function bbcode(
		?string $string,
		string $mode = 'normal',
		bool $inlineHtml = false
	) {
		if (!$string) {
			return '';
		}

		if ($mode === 'html') {
			return CsrBB::parseHtml($string, $inlineHtml);
		} elseif ($mode == 'mail') {
			return CsrBB::parseMail($string);
		} elseif ($mode == 'light') {
			return CsrBB::parseLight($string);
		} elseif ($mode == 'preview') {
			return CsrBB::parsePreview($string);
		} elseif ($mode == 'plain') {
			return CsrBB::parsePlain($string);
		} else {
			return CsrBB::parse($string);
		}
	}

	public function file_base64($filename)
	{
		if (file_exists($filename)) {
			return base64_encode(file_get_contents($filename));
		}
		return '';
	}

	/**
	 * Reken uit hoe oud de vereniging is.
	 *
	 * @return int
	 */
	public function vereniging_leeftijd()
	{
		$oprichting = date_create_immutable('1961-06-16');

		$leeftijd = date_create_immutable()->diff($oprichting);

		return $leeftijd->y;
	}

	public function table(DataTableView $table): string
	{
		return (string) $table;
	}

	public function commitLink()
	{
		return 'https://github.com/csrdelft/productie/commit/' .
			$this->commitHash(true);
	}

	public function commitHash($full = false)
	{
		if ($full) {
			return trim((string) `git rev-parse HEAD`);
		} else {
			return trim((string) `git rev-parse --short HEAD`);
		}
	}
}
