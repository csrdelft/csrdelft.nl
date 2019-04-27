<?php

namespace CsrDelft\view\formulier;

interface PostedValue {
	public function isPosted();

	public function getValue();

	public function getName();
}
