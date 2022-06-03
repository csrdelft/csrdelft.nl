<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbTag;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\service\security\LoginService;
use Exception;
use Twig\Environment;

class BbForum extends BbTag
{
    public $num = 3;
    /**
     * @var ForumDeel
     */
    private $deel;
    /**
     * @var ForumDradenRepository
     */
    private $forumDradenRepository;
    /**
     * @var ForumDelenRepository
     */
    private $forumDelenRepository;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var string
     */
    private $id;

    public function __construct(ForumDradenRepository $forumDradenRepository, ForumDelenRepository $forumDelenRepository, Environment $twig)
    {
        $this->forumDradenRepository = $forumDradenRepository;
        $this->forumDelenRepository = $forumDelenRepository;
        $this->twig = $twig;
    }

    public static function getTagName()
    {
        return 'forum';
    }

    public function isAllowed()
    {
        if ($this->id == 'recent' || $this->id == 'belangrijk') {
            return LoginService::mag(P_LOGGED_IN);
        }

        return $this->deel->magLezen();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function render()
    {
        if (!LoginService::mag(P_LOGGED_IN)) {
            return 'Geen toegang';
        }

        return $this->twig->render('forum/bb.html.twig', [
            'deel' => $this->deel,
            'id' => $this->id,
        ]);
    }

    /**
     * @param array $arguments
     */
    public function parse($arguments = [])
    {
        $this->id = $this->readMainArgument($arguments);
        if (isset($arguments['num'])) {
            $this->num = (int)$arguments['num'];
        }

        $this->forumDradenRepository->setAantalPerPagina($this->num);
        switch ($this->id) {
            case 'recent':
                $this->deel = $this->forumDelenRepository->getRecent();
                break;
            case 'belangrijk':
                $this->deel = $this->forumDelenRepository->getRecent(true);
                break;
            default:
                $this->deel = $this->forumDelenRepository->get($this->id);
                break;
        }
    }
}
