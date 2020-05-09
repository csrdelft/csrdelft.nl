<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\groepen\ActiviteitenRepository;


/**
 * ApiActiviteitenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor activiteiten.
 */
class ActiviteitenController extends KetzersController {
	public function __construct(ActiviteitenRepository $activiteitenRepository) {
		parent::__construct($activiteitenRepository);
	}
}
