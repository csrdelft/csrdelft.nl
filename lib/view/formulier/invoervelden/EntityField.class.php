<?php
/**
 * EntityField.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Select an entity based on primary key values in hidden input fields, supplied by remote data source.
 *
 * NOTE: support alleen entities met een enkele primary key.
 */
class EntityField extends InputField {

	private $show_value, $entity, $orm;

	/**
	 * @var \CsrDelft\Orm\PersistenceModel
	 */
	protected $model;

	/**
	 * EntityField constructor.
	 * @param $name string Prefix van de input
	 * @param $show string Attribuut van $model om in de input weer te geven
	 * @param $description string Beschrijvijng van de input
	 * @param \CsrDelft\Orm\Entity\PersistentEntity $entity
	 * @param \CsrDelft\Orm\PersistenceModel $model Model
	 * @param $url string Url waar aanvullingen te vinden zijn
	 */
	public function __construct($name, $show, $description, \CsrDelft\Orm\PersistenceModel $model, $url) {
		$this->orm = $model::ORM;
		$this->entity = new $this->orm();
		foreach ($this->entity->getPrimaryKey() as $key) {
			$this->entity->$key = filter_input(INPUT_POST, $name . '_' . $key, FILTER_DEFAULT);
		}

		parent::__construct($name, $this->entity->$show, $description, $model);
		$this->suggestions[] = $url;
		$this->show_value = $show;
	}

	public function getName() {
		// TODO: Dit moet de volledige pk kunnen zijn.
		return $this->name . '_' . $this->entity->getPrimaryKey()[0];
	}

	public function getValue() {
		// TODO: Dit moet de volledige pk kunnen zijn.
		$key = $this->entity->getPrimaryKey()[0];
		return $this->entity->$key;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}

		if (!$this->model->exists($this->entity)) {
			$this->error = 'Niet gevonden';
		}

		return $this->error === '';
	}

	/**
	 * Dit veld is gepost als show en de hele pk is gepost.
	 *
	 * @return bool Of alles gepost is
	 */
	public function isPosted() {
		if (false === filter_input(INPUT_POST, $this->name . '_show', FILTER_DEFAULT)) {
			return false;
		}

		foreach ($this->entity->getPrimaryKey() as $key) {
			if (false === filter_input(INPUT_POST, $this->name . '_' . $key, FILTER_DEFAULT)) {
				return false;
			}
		}

		return true;
	}

	public function getHtml() {
		if ($this->isPosted()) {
			$this->value = filter_input(INPUT_POST, $this->name . '_show', FILTER_DEFAULT);
		}

		$html = '<input name="' . $this->name . '_show" value="' . $this->value . '" origvalue="' . $this->value . '"' . $this->getInputAttribute(array('type', 'id', 'class', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete')) . ' />';

		foreach ($this->entity->getPrimaryKey() as $i => $key) {
			$id = $this->getId() . '_' . $key;
			$name = $this->name . '_' . $key;
			$this->typeahead_selected .= '$("#' . $id . '").val(suggestion["' . $key . '"]);';
			$html .= '<input type="hidden" name="' . $name . '" id="' . $id . '" value="' . $this->entity->$key . '" />';
		}
		return $html;
	}

}
