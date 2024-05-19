<?php

namespace CsrDelft\controller\groepen;

use Symfony\Component\HttpFoundation\Response;
use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\view\groepen\GroepenView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * BesturenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor besturen.
 */
class BesturenController extends AbstractGroepenController
{
	public function getGroepType(): string
	{
		return Bestuur::class;
	}

	public function overzicht(Request $request, $soort = null): Response
	{
		$pagina = $request->get('pagina');
		$limit = 20;
		$offset = $pagina * $limit;
		$aantal = $this->repository->count([]);
		if ($offset >= $aantal) {
			throw new NotFoundHttpException();
		}
		// Zoek ook op ot,ft
		$groepen = $this->repository->findBy([], null, $limit, $offset);
		$paginaUrl = function ($paginaNummer) use ($soort): string {
			return $this->generateUrl(
				'csrdelft_groep_' . $this->repository::getNaam() . '_overzicht',
				['soort' => $soort, 'pagina' => $paginaNummer]
			);
		};
		// controleert rechten bekijken per groep
		$body = new GroepenView(
			$this->container->get('twig'),
			$this->repository,
			$groepen,
			null,
			$pagina,
			$limit,
			$aantal,
			$paginaUrl
		);
		return $this->render('default.html.twig', ['content' => $body]);
	}
}
