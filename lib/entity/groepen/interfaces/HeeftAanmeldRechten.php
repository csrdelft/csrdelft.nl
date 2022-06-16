<?php

namespace CsrDelft\entity\groepen\interfaces;

interface HeeftAanmeldRechten
{
	function magAanmeldRechten($action);

	function getAanmeldRechten();
	function setAanmeldRechten($rechten);
}
