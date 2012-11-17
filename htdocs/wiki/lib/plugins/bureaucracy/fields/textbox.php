<?php
class syntax_plugin_bureaucracy_field_textbox extends syntax_plugin_bureaucracy_field {
    function syntax_plugin_bureaucracy_field_textbox($args) {
        parent::__construct($args);
        $this->tpl = form_makeTextField('@@NAME@@', '@@VALUE@@', '@@LABEL@@', '', '@@CLASS@@');
        if(isset($this->opt['class'])){
            $this->tpl['class'] .= ' '.$this->opt['class'];
        }
        if(isset($this->opt['datatype'])){
            $this->tpl['data-'.$this->opt['datatype'][0]] = $this->opt['datatype'][1];
        }
    }
}
