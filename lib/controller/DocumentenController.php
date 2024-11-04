<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\model\entity\Bestand;
use CsrDelft\repository\documenten\DocumentCategorieRepository;
use CsrDelft\repository\documenten\DocumentRepository;
use CsrDelft\view\documenten\DocumentBewerkenForm;
use CsrDelft\view\documenten\DocumentCategorieForm;
use CsrDelft\view\documenten\DocumentToevoegenForm;
use CsrDelft\view\Icon;
use CsrDelft\view\PlainView;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentenController extends AbstractController
{

}
