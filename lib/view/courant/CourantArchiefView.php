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
		<ul class="horizontal nobullets">
			<li>
				<a href="/courant" title="Courantinzendingen">Courantinzendingen</a>
			</li>
			<li class="active">
				<a href="/courant/archief" title="Archief">Archief</a>
			</li>
		</ul>
		<hr/>
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
