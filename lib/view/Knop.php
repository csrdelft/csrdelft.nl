<?php

namespace CsrDelft\view;

/**
 * class.btn.php  |  Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Algemene code om knopjes in html te regelen.
 * Door dit te centraliseren komen er niet op 100 plekken links naar plaatjes te staan enzo.
 *
 * Knopjes staan in {PHOTOS_PATH}knopjes/{$type}.png, zijn dus altijd van het type png.
 *
 * @deprecated
 */
class Knop {

	private $knoptypes = array('default', 'toevoegen', 'bewerken', 'verwijderen', 'citeren', 'slotje', 'plakkerig', 'belangrijk', 'offtopic');
	public $url;   //url van de knop.
	public $type = 'default'; //type van de knop, default= zonder plaatje, bij de andere opties hoort een plaatje.
	public $class = 'knop';  //css class
	public $text = null;  //eventuele tekst naast het plaatje
	public $title = null;  //hover text
	public $confirm = false; //javascript confirm toevoegen

	//Kan true zijn of een string, bij true wordt de vraag 'weet u het zeker'
	//anders de inhoud van de string.

	public function __construct($url) {
		$this->url = $url;
	}

	public function setText($text) {
		$this->text = htmlspecialchars($text);
	}

	public function setTitle($text) {
		$this->title = htmlspecialchars($text);
	}

	public function setType($type) {
		if (in_array($type, $this->knoptypes)) {
			$this->type = $type;
			$this->setClass('knopKaal');
		} else {
			$this->type = 'default';
			return false;
		}
	}

	public function setClass($class) {
		$this->class = htmlspecialchars($class);
	}

	/*
	 * false == geen javascript confirm
	 * true == een javascript confirm met 'weet u het zeker?' als tekst
	 * string == een javascript confirum met $string als tekst
	 */

	public function setConfirm($confirm) {
		$this->confirm = $confirm;
	}

	private function getImgTag() {
		$img = '<img src="/plaetjes/knopjes/' . $this->type . '.png"';
		if ($this->title === null) {
			$img .= ' title="' . ucfirst($this->type) . '"';
		}
		$img .= 'alt="' . ucfirst($this->type) . '" />';
		return $img;
	}

	public function getHtml() {
		$html = '<a href="' . $this->url . '" title="' . htmlspecialchars($this->title) . '" class="' . htmlspecialchars($this->class) . '" ';
		if ($this->confirm !== false) {
			if ($this->confirm === true) {
				$confirm = 'Weet u het zeker?';
			} else {
				$confirm = $this->confirm;
			}
			$html .= 'onclick="return confirm(\'' . $confirm . '\')" ';
		}
		$html .= '>';
		if ($this->type == 'default') {
			//knopje zonder plaatje, checken of er wel een tekst is, anders een foutmelding meegeven
			if ($this->text === null) {
				$this->text = 'Knop::getHtml(): Geen tekst opgegeven bij een knop zonder plaatje.';
			}
		} else {
			//we gaan een plaatje erbij doen.
			$html .= $this->getImgTag();
		}
		if ($this->text !== null) {
			$html .= ' ' . $this->text;
		}
		$html .= '</a>';
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

	public static function getKnop($url, $type, $text = null) {
		$knop = new Knop($url);
		$knop->setType($type);
		$knop->setText($text);
		return $knop->getHtml();
	}
}
