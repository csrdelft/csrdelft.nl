<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviCategorieRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use DateInterval;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BestellingZoeker extends AbstractController
{
	/**
	 * @Route("/fiscaat/bestelling-zoeker")
	 * @param Request $request
	 * @param CiviCategorieRepository $civiCategorieRepository
	 * @param CiviProductRepository $civiProductRepository
	 * @param CiviSaldoRepository $civiSaldoRepository
	 * @return Response
	 * @Auth(P_FISCAAT_READ)
	 */
	public function bestellingZoeker(
		Request $request,
		CiviCategorieRepository $civiCategorieRepository,
		CiviProductRepository $civiProductRepository,
		CiviSaldoRepository $civiSaldoRepository
	): Response {
		$from = new DateTimeImmutable();
		$from = $from->sub(new DateInterval('P1W'));

		$until = new DateTimeImmutable();
		$until = $until->add(new DateInterval('P1D'));

		if ($request->query->has('van')) {
			try {
				$from = new DateTimeImmutable($request->query->get('van'));
			} catch (Exception $e) {
			}
		}

		if ($request->query->has('tot')) {
			try {
				$until = new DateTimeImmutable($request->query->get('tot'));
			} catch (Exception $e) {
			}
		}

		if ($from > $until) {
			$until = $from->add(new DateInterval('P1D'));
		}

		$selectedCommissie = -1;
		$selectedCategorie = -1;
		$selectedProduct = -1;
		$commissies = [
			'soccie' => 'SocCie',
			'maalcie' => 'MaalCie',
			'oweecie' => 'OWeeCie',
			'anders' => 'Anders',
		];
		$categorieen = [];
		$producten = [];

		foreach ($commissies as $key => $commissie) {
			if ($request->query->get('commissie') == $key) {
				$selectedCommissie = $key;
			}
		}

		foreach (
			$civiCategorieRepository->findBy([], ['type' => 'ASC'])
			as $categorie
		) {
			$categorieen[$categorie->id] = $categorie->type;
			if (
				$request->query->get('categorie') == $categorie->id ||
				$request->query->get('categorie') == $categorie->type
			) {
				$selectedCategorie = $categorie->id;
			}
		}

		foreach (
			$civiProductRepository->findBy([], ['beschrijving' => 'ASC'])
			as $product
		) {
			if (!isset($producten[$product->categorie->type])) {
				$producten[$product->categorie->type] = [];
			}
			$producten[$product->categorie->type][$product->id] =
				$product->beschrijving;
			if ($request->query->get('product') == $product->id) {
				$selectedProduct = $product->id;
			}
		}

		$groeperen = $request->query->get('groeperen') == 1;

		$bestellingen = null;
		if (
			$selectedCommissie != -1 ||
			$selectedCategorie != -1 ||
			$selectedProduct != -1
		) {
			$bestellingen = $civiSaldoRepository->zoekBestellingen(
				$from,
				$until,
				$selectedCommissie,
				$selectedCategorie,
				$selectedProduct,
				$groeperen
			);
		}

		return $this->render('fiscaat/bestelling-zoeker.html.twig', [
			'van' => $from->format('Y-m-d'),
			'tot' => $until->format('Y-m-d'),
			'commissies' => $commissies,
			'categorieen' => $categorieen,
			'producten' => $producten,
			'selectedCommissie' => $selectedCommissie,
			'selectedCategorie' => $selectedCategorie,
			'selectedProduct' => $selectedProduct,
			'groeperen' => $groeperen,
			'bestellingen' => $bestellingen,
		]);
	}
}
