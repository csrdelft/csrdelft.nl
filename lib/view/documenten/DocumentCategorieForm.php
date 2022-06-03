<?php


namespace CsrDelft\view\documenten;


use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\view\formulier\invoervelden\required\RequiredRechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredCheckboxField;

class DocumentCategorieForm implements FormulierTypeInterface
{
    /**
     * @param FormulierBuilder $builder
     * @param DocumentCategorie $data
     * @param array $options
     */
    public function createFormulier(FormulierBuilder $builder, $data, $options = [])
    {
        $builder->setTitel('Categorie bewerken');

        $fields = [];
        $fields[] = new RequiredTextField('naam', $data->naam, 'Naam');
        $fields[] = new RequiredCheckboxField('zichtbaar', $data->zichtbaar, 'Zichtbaar');
        $fields[] = new RequiredRechtenField('leesrechten', $data->leesrechten, 'Leesrechten');
        $fields[] = new RequiredRechtenField('schrijfrechten', $data->schrijfrechten, 'Schrijfrechten');

        $builder->addFields($fields);
    }
}
