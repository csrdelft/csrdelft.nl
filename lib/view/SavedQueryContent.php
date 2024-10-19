<?php

namespace CsrDelft\view;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\SavedQueryResult;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\SavedQueryRepository;

class SavedQueryContent implements View
{
	use ToHtmlResponse;

	public function __construct(
		/**
		 * Saved query
		 */
		private ?SavedQueryResult $sq = null
	) {
	}

	public function getModel()
	{
		return $this->sq;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getTitel()
	{
		return 'Opgeslagen query\'s';
	}

	public static function render_header($name)
	{
		switch ($name) {
			case 'uid_naam':
				return 'Naam';
			case 'uid_naam_civitas':
				return 'Civitas naam';
			case 'groep_naam':
				return 'Groep';
			case 'onderwerp_link':
				return 'Onderwerp';
			case 'med_link':
				return 'Mededeling';
			default:
				if (str_starts_with((string) $name, 'groep_naam')) {
					return substr((string) $name, 11);
				}
		}
		return $name;
	}

	public static function render_field($name, $contents)
	{
		if ($name == 'uid_naam') {
			return ProfielRepository::getLink($contents, 'volledig');
		} elseif ($name == 'uid_naam_civitas') {
			return ProfielRepository::getLink($contents, 'civitas');
		} elseif ($name == 'onderwerp_link') {
			//link naar het forum.
			return '<a href="/forum/onderwerp/' .
				$contents .
				'">' .
				$contents .
				'</a>';
		} elseif (
			str_starts_with((string) $name, 'groep_naam') &&
			$contents != ''
		) {
			return ''; //FIXME: OldGroep::ids2links($contents, '<br />');
		} elseif ($name == 'med_link') {
			//link naar een mededeling.
			return '<a href="/mededelingen/' . $contents . '">' . $contents . '</a>';
		}

		return htmlspecialchars((string) $contents);
	}

	public function render_queryResult()
	{
		if ($this->sq && !$this->sq->error) {
			$sq = $this->sq;
			$id = 'query-' . time();
			$return =
				$sq->query->beschrijving .
				' (' .
				count($sq->rows) .
				' regels)<br /><table class="table table-sm table-striped" id="' .
				$id .
				'">';

			$return .= '<thead><tr>';
			foreach ($sq->cols as $kopje) {
				$return .= '<th>' . self::render_header($kopje) . '</th>';
			}
			$return .= '</tr></thead><tbody>';

			foreach ($sq->rows as $rij) {
				$return .= '<tr>';
				foreach ($rij as $key => $veld) {
					$return .= '<td>' . self::render_field($key, $veld) . '</td>';
				}
				$return .= '</tr>';
			}
			$return .= '</tbody></table>';
		} elseif ($this->sq->error) {
			$return = $this->sq->error;
		} else {
			//foutmelding in geval van geen resultaat, dus of geen query die bestaat, of niet
			//voldoende rechten.
			$return =
				'Query (' .
				$this->sq->query->ID .
				') bestaat niet, geeft een fout, of u heeft niet voldoende rechten.';
		}
		return $return;
	}

	public function getQueryselector()
	{
		//als er een query ingeladen is, die highlighten
		$id = $this->sq instanceof SavedQueryResult ? $this->sq->query->ID : 0;

		$return =
			'<a class="btn btn-primary" href="#" onclick="$(\'#sqSelector\').toggle();">Laat queryselector zien.</a>';
		$return .= '<div id="sqSelector" class="row';
		if ($id != 0) {
			$return .= ' verborgen';
		}
		$return .= '">';
		$current = '';
		$savedQueryRepository = ContainerFacade::getContainer()->get(
			SavedQueryRepository::class
		);
		foreach ($savedQueryRepository->getQueries() as $query) {
			if (!$query->magBekijken()) {
				continue;
			}
			if ($current != $query->categorie) {
				if ($current != '') {
					$return .= '</ul></div>';
				}
				$return .=
					'<div class="col-md-6"><span class="dikgedrukt">' .
					$query->categorie .
					'</span><ul>';
				$current = $query->categorie;
			}
			$return .= '<li><a href="query?id=' . $query->ID . '">';
			if ($id == $query->ID) {
				$return .= '<span class="cursief">';
			}
			$return .= htmlspecialchars($query->beschrijving);
			if ($id == $query->ID) {
				$return .= '</span>';
			}
			$return .= '</a></li>';
		}
		$return .= '</ul></div></div><div class="clear"></div>';
		return $return;
	}

	public function __toString(): string
	{
		$html = '';
		$html .= '<h1>' . $this->getTitel() . '</h1>';
		$html .= $this->getQueryselector();

		//render query if selected and allowed
		if ($this->sq != null && $this->sq->query->magBekijken()) {
			$html .= $this->render_queryResult();
		}
		return $html;
	}
}
