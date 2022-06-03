<?php


namespace CsrDelft\Component\Formulier;


interface FormulierTypeInterface
{
    public function createFormulier(FormulierBuilder $builder, $data, $options = []);
}
