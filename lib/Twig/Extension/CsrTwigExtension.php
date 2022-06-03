<?php


namespace CsrDelft\Twig\Extension;


use CsrDelft\Component\DataTable\DataTableView;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\CsrfService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\CsrfField;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class CsrTwigExtension extends AbstractExtension
{
    /**
     * @var CsrfService
     */
    private $csrfService;
    /**
     * @var ProfielRepository
     */
    private $profielRepository;
    /**
     * @var CmsPaginaRepository
     */
    private $cmsPaginaRepository;

    public function __construct(
        CsrfService         $csrfService,
        CmsPaginaRepository $cmsPaginaRepository,
        ProfielRepository   $profielRepository
    )
    {
        $this->csrfService = $csrfService;
        $this->profielRepository = $profielRepository;
        $this->cmsPaginaRepository = $cmsPaginaRepository;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('dragobject_coords', [$this, 'dragobject_coords']),
            new TwigFunction('commitHash', 'commitHash'),
            new TwigFunction('commitLink', 'commitLink'),
            new TwigFunction('csrfMetaTag', [$this, 'csrfMetaTag'], ['is_safe' => ['html']]),
            new TwigFunction('csrfField', [$this, 'csrfField'], ['is_safe' => ['html']]),
            new TwigFunction('vereniging_leeftijd', [$this, 'vereniging_leeftijd']),
            new TwigFunction('get_profiel', [$this, 'get_profiel']),
            new TwigFunction('huidige_jaargang', [$this, 'huidige_jaargang']),
            new TwigFunction('gethostbyaddr', 'gethostbyaddr'),
            new TwigFunction('cms', [$this, 'cms'], ['is_safe' => ['html']]),
            new TwigFunction('table', [$this, 'table'], ['is_safe' => ['html']])
        ];
    }

    public function huidige_jaargang()
    {
        return LichtingenRepository::getHuidigeJaargang();
    }

    public function get_profiel($uid)
    {
        return $this->profielRepository->find($uid);
    }

    public function csrfField($path = '', $method = 'post')
    {
        return (new CsrfField($this->csrfService->generateToken($path, $method)))->__toString();
    }

    public function csrfMetaTag()
    {
        $token = $this->csrfService->generateToken('', 'POST');
        return '<meta property="X-CSRF-ID" content="' . htmlentities($token->getId()) . '" /><meta property="X-CSRF-VALUE" content="' . htmlentities($token->getValue()) . '" />';
    }

    public function cms($id)
    {
        $pagina = $this->cmsPaginaRepository->find($id);

        if (!$pagina) {
            return '<div class="alert alert-danger">Gedeelte van de pagina met naam "' . htmlspecialchars($id) . '" niet gevonden.</div>';
        }

        if ($pagina->magBekijken()) {
            return CsrBB::parseHtml($pagina->inhoud, $pagina->inlineHtml);
        }

        return '';
    }

    public function getFilters()
    {
        return [
            new TwigFilter('escape_ical', 'escape_ical'),
            new TwigFilter('file_base64', [$this, 'file_base64']),
            new TwigFilter('bbcode', [$this, 'bbcode'], ['is_safe' => ['html']]),
            new TwigFilter('bbcode_light', [$this, 'bbcode_light'], ['is_safe' => ['html']]),
            new TwigFilter('uniqid', function ($prefix) {
                return uniqid_safe($prefix);
            }),
            new TwigFilter('format_bedrag', 'format_bedrag'),
            new TwigFilter('format_euro', 'format_euro'),
            new TwigFilter('truncate', 'truncate'),
            new TwigFilter('format_filesize', 'format_filesize'),
            new TwigFilter('shuffle', 'array_shuffle'),
        ];
    }

    public function getTests()
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
            new TwigTest('numeric', function ($value) {
                return is_numeric($value);
            }),
            new TwigTest('profiel', function ($value) {
                return $value instanceof Profiel;
            }),
            new TwigTest('corveetaak', function ($value) {
                return $value instanceof CorveeTaak;
            }),
            new TwigTest('maaltijd', function ($value) {
                return $value instanceof Maaltijd;
            }),
            new TwigTest('agendeerbaar', function ($value) {
                return $value instanceof Agendeerbaar;
            }),
            new TwigTest('abstractgroep', function ($value) {
                return $value instanceof Groep;
            }),
            new TwigTest('agendaitem', function ($value) {
                return $value instanceof AgendaItem;
            }),
        ];
    }


    public function dragobject_coords(SessionInterface $session, $id, $top, $left)
    {
        if ($session->has("dragobject_$id")) {
            $dragObject = $session->get("dragobject_$id");
            $top = (int)$dragObject['top'];
            $left = (int)$dragObject['left'];
        }

        $top = max($top, 0);
        $left = max($left, 0);
        return ['top' => $top, 'left' => $left];
    }

    public function bbcode(string $string, string $mode = 'normal')
    {
        if ($mode === 'html') {
            return CsrBB::parseHtml($string);
        } else if ($mode == 'mail') {
            return CsrBB::parseMail($string);
        } else if ($mode == 'plain') {
            return CsrBB::parsePlain($string);
        } else {
            return CsrBB::parse($string);
        }
    }

    public function bbcode_light($string)
    {
        return CsrBB::parseLight($string);
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
        return (string)$table;
    }
}

