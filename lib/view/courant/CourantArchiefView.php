<?php

namespace CsrDelft\view\courant;

use CsrDelft\model\CourantModel;
use CsrDelft\model\entity\courant\Courant;
use CsrDelft\view\View;
use PDOStatement;


/**
 * CourantArchiefView.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 */
class CourantArchiefView implements View {

	private $model;

	/**
	 * CourantArchiefView constructor.
	 * @param PDOStatement|Courant[] $model
	 */
	public function __construct($model) {
		$this->model = $model;
	}

	public function getModel() {
		return $this->model;
	}

	public function getBreadcrumbs() {
		return '<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>'
			. '<li class="breadcrumb-item"><a href="/courant">Courant</a></li>'
			. '<li class="breadcrumb-item">' . $this->getTitel() . '</li>';
	}

	public function getTitel() {
		return 'Archief C.S.R.-courant';
	}

	public function view() {
		?>
		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a href="/courant" class="nav-link">Courantinzendingen</a>
			</li>
			<li class="nav-item">
				<a href="/courant/archief" class="nav-link active">Archief</a>
			</li>
		</ul>

		<?php
		echo '<h1>' . $this->getTitel() . '</h1>';
		$jaar = 0;
		foreach ($this->model as $courant) {
			if ($jaar != $courant->getJaar()) {
				if ($jaar > 0) {
					echo '</div>';
				}
				$jaar = $courant->getJaar();
				echo '<div class="CourantArchiefJaar"><h3>' . $jaar . '</h3>';
			}
			echo '<a href="/courant/bekijken/' . $courant->id . '">' . strftime('%d %B', strtotime($courant->verzendMoment)) . '</a><br />';
		}
		echo '</div>';
	}

}
