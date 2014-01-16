<?php
/**
 * Class syntax_plugin_bureaucracy_field_textboxcsr
 *
 * Creates a single line input field
 * With extra data attribute for aditional info for lib/plugins/csrlink/script/autocompletion.js
 */
class syntax_plugin_bureaucracy_field_textboxcsr extends syntax_plugin_bureaucracy_field_textbox {

    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - =default (optional)
     *  - &type:value (optional)
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    public function __construct($args) {
        parent::__construct($args);

        if(isset($this->opt['datatype'])) {
            $this->tpl['data-' . $this->opt['datatype'][0]] = $this->opt['datatype'][1];
        }
    }

    /**
     * Check for additional arguments and store their values
     *
     * @param string $arg array with remaining definition arguments
     * @return bool
     */
    protected function additionalStandardArg($arg) {
        if($arg[0] == '&') {
            $this->opt['datatype'] = explode(':', substr($arg, 1), 2);
            return true;
        }
        return false;
    }
}

