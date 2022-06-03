<?php

namespace CsrDelft\view;

/**
 * View.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een View heeft een methode om een model aan de gebruiker te tonen.
 *
 */
interface View
{

//	public function view();

    public function getTitel();

    public function getBreadcrumbs();

    /**
     * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
     */
    public function getModel();

    /**
     * @return string
     */
    public function __toString();
}
