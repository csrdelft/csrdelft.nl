<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\LDAP;
use CsrDelft\common\Util\DebugUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\SavedQueryRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\service\Roodschopper;
use CsrDelft\service\security\SuService;
use CsrDelft\view\Icon;
use CsrDelft\view\PlainView;
use CsrDelft\view\roodschopper\RoodschopperForm;
use CsrDelft\view\SavedQueryContent;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deze controller bevat een aantal beheertools die niet direct onder een andere controller geschaard kunnen worden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2019
 */
class ToolsController extends AbstractController
{

}
