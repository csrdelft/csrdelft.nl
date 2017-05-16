<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\view\formulier\getalvelden\RequiredIntField;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;

class FotoTagToevoegenForm extends InlineForm
{

    public function __construct(Foto $foto)
    {
        $field = new LidField('uid', null, null, LidInstellingenModel::get('fotoalbum', 'tag_suggestions'));
        $field->placeholder = 'Naam of lidnummer';
        parent::__construct(null, '/fotoalbum/addtag/' . $foto->subdir, $field, false, false);
        $fields[] = new RequiredTextField('foto', $foto->filename, null);
        $fields[] = new RequiredIntField('x', null, null, 1, 99);
        $fields[] = new RequiredIntField('y', null, null, 1, 99);
        $fields[] = new RequiredIntField('size', null, null, 1, 99);
        foreach ($fields as $field) {
            $field->hidden = true;
        }
        $this->addFields($fields);
    }

}
